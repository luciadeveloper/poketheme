<?php
/**
 * Postype Pokemon
 *
 * @package Understrap
 */

namespace Pokemon;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class Pokemon
 *
 */
class Pokemon {
    /**
     * @var string
     *
     * Set post type params
     */
    private $type               = 'pokemon';
    private $slug               = 'pokemon';
    private $name               = 'Pokémon';
    private $singular_name      = 'pokemon';
    
    private $new_pokemons       = 3;

    private $api_endpoint       = 'https://pokeapi.co/api/v2/pokemon/';
       

    public function __construct() {
        // Register the post type
        add_action( 'init', array( $this, 'register_cpt' ));
       
        // create initial pokemons when the theme is enabled
        add_action( 'after_switch_theme', array( $this, 'create_pokemons' ) );
        //$this->create_pokemons(10);  
        
        //ajax endpoints
        add_action( 'wp_ajax_pokemon_data', array( $this,'pokemon_data' ) );
        add_action( 'wp_ajax_nopriv_pokemon_data', array( $this,'pokemon_data' ) );
       
        add_action('wp_ajax_get_pokedex_number', array( $this,'get_pokedex_number' ) );
        add_action('wp_ajax_nopriv_get_pokedex_number', array( $this,'get_pokedex_number' ) );


        //REST API endpoints
        add_action( 'rest_api_init',  array( $this,'register_url_get_pokemon' ));
        add_action( 'rest_api_init',  array( $this,'register_fields_show_pokemon' ));
        add_action( 'rest_api_init',  array( $this,'register_url_add_pokemon' ));
        add_action( 'rest_api_init',  array( $this,'register_url_list_pokemons_by_pokedex' ));
       
    }

    /**
     * Register post type
     */
    public function register_cpt() {
        $labels = array(
            'name'                  => $this->name,
            'singular_name'         => $this->singular_name,
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New '   . $this->singular_name,
            'edit_item'             => 'Edit '      . $this->singular_name,
            'new_item'              => 'New '       . $this->singular_name,
            'all_items'             => 'All '       . $this->name,
            'view_item'             => 'View '      . $this->name,
            'search_items'          => 'Search '    . $this->name,
            'not_found'             => 'No '        . strtolower( $this->name ) . ' found',
            'not_found_in_trash'    => 'No '        . strtolower( $this->name ) . ' found in Trash',
            'parent_item_colon'     => '',
            'menu_name'             => $this->name
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true, // the CPT will have a page on the front-end
            'publicly_queryable'    => true, // the content of the pokemon will be found on search
            'show_ui'               => true, // pokemons are editable.
            'show_in_menu'          => true, // the CPT will have its own page on the admin
            'query_var'             => true,
            'rewrite'               => array( 'slug' => $this->slug ),
            'capability_type'       => 'post',
            'has_archive'           => true, // the CPT has an archive page
            'show_in_rest'           => true,  // the CPT will be shown in the REST API
            'hierarchical'          => false,
            'menu_position'         => 0,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields'),
        );
        
        register_post_type( $this->type, $args );
    }
    

    /**
     * Create new pokemons 
     */
    public function create_pokemons( $n = 3) {
    
        $data  =  $this->pokemon_data( $this->api_endpoint );
        $count =  $data->count;
        
        for ($i = 0; $i < $n; $i++) {
            
            $rand         = rand( 0, $count );
            $pokemon_data =  $this->pokemon_data( $this->api_endpoint.$rand );            

            if( ! is_null( $pokemon_data )  ) {

                //checks if a post with same name already exists
                $post_exist = get_page_by_title(  $pokemon_data->name );
               
                if( ! $post_exist ) {
                    $this->create_one_pokemon( $pokemon_data );
                }

                else { //if that Pokemon was already in the DB
                    --$i;
                }
              
            } 
            else {   //if there was no pokemon retreived from the API, give it another try
                --$i;
            }
            
        }
       
    }

    /**
     * Create one pokemon with the data from the API
     */
    public function create_one_pokemon( $pokemon_data ) {
    
        $name           = $pokemon_data->name;
        $pokemon_id     = $pokemon_data->id; //same as $rand
        $weight         = $pokemon_data->weight;
        $primary_type   = $pokemon_data->types[0]->type->name;
 
        $secondary_type = $pokemon_data->types[1]->type->name ?? 'no data';
        $game_indices   = $pokemon_data->game_indices; 
       
        $game_first_indice = [
            'number' => reset( $game_indices )->game_index ?? 'no data',
            'name'   => reset( $game_indices )->version->name ?? 'no data',
        ];

        $game_last_indice = end( $game_indices )->game_index ?? 'no data';

        //attacks
        $moves = $this->get_pokemon_moves( $pokemon_data->moves );

        //basic post atributes
        $my_post = array(
            'post_title'    => $name,
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => $this->type,
        );

        // Insert the post into the database
        $postId = wp_insert_post( $my_post );
        
        //(this could be optimized with a foreach or take it to another function. Fields should be in new lines but I found it too big. )
        add_post_meta( $postId, 'pokemon_id', $pokemon_id, true );
        add_post_meta( $postId, 'description', '', true );
        add_post_meta( $postId, 'primary_type', $primary_type, true );
        add_post_meta( $postId, 'secondary_type', $secondary_type, true );
        add_post_meta( $postId, 'weight', $weight, true );
        add_post_meta( $postId, 'pokedex_old_version', $game_first_indice['number'], true );
        add_post_meta( $postId, 'pokedex_last_version', $game_last_indice, true );
        add_post_meta( $postId, 'pokedex_last_version_and_name', $game_first_indice, true );
        add_post_meta( $postId, 'attacks', $moves );
    
    }

    /**
     * Returns a list with info of all the movements
     */
    public function get_pokemon_moves( $pokemon_moves ) {
       
        $moves      = $pokemon_moves;
        $moves_list = array();
       
        foreach ( $moves as $move ) {

            $movement                = array();
            $movement['name']        = $move->move->name;
            $url                     = $move->move->url;
            $description             = $this->pokemon_data($url);
            $movement['description'] = $description->type->name;
            $moves_list[]            = $movement;
            
        }
       
        return($moves_list); 
       
    }



    /**
     * Register the URL to call get_random pokemon function
     * http://poketest.local/wp-json/add-pokemon/random/ 
     */
    public function register_url_add_pokemon(){
        
        register_rest_route( 'add-pokemon', 'random', 
            array(
                'methods'             => 'GET',
                'callback'            => array( $this,'add_random_pokemon' ),
                'permission_callback' => function () {
                    return true;
                    //return current_user_can( 'edit_posts' ); //this is not working not sure yet why
                }
            ) );
    }

     
    /**
     * Register the URL to call get_random_pokemon function
     * http://poketest.local/wp-json/get-pokemon/random/ 
     */
    public function register_url_get_pokemon(){
        register_rest_route( 'get-pokemon', 'random', 
            array(
                'methods'  => 'GET',
                'callback' => array( $this,'get_random_pokemon' ),
                'permission_callback' => function () {
                    return true;
                    //return current_user_can( 'edit_others_posts' );
                }
            ) );
    }


    

    /**
     * Register the URL to call list pokemons by pokedex function
     * http://poketest.local/wp-json/list-pokemons/bypokedex
     */
    public function register_url_list_pokemons_by_pokedex(){
        register_rest_route( 'list-pokemons', 'bypokedex', 
            array(
                'methods'  => 'GET',
                'callback' => array( $this,'list_pokemons' ),
                'permission_callback' => function () {
                    return true;
                    //return current_user_can( 'edit_others_posts' );
                }
            ) );
    }


     /**
     * add one pokemon to DB
     */
    function add_random_pokemon() {

        $this->create_pokemons(1);

    }


    /**
     * get Random pokemon form DB and redirects to its URL
     */
    function get_random_pokemon() {
      
        $args = array(
            'post_type'      => $this->type,
            'orderby'        => 'rand',
            'posts_per_page' => 1, 
        );

        $the_query = new \WP_Query( $args );
 
        if ( $the_query->have_posts() ) {
         
            $the_query->the_post();
           
            wp_redirect( get_permalink() );
           
            wp_reset_postdata();

        } else {
           
            echo "no posts";
        
        }
    }

    /**
     *  list pokemosn by pokedex function
     */
    function list_pokemons() {
       
        $args = array(
            'post_type'      => $this->type,
            'orderby'        => 'rand',
            'posts_per_page' => 500, //to show them all is typically used -1 but this could create performance issues. So I decided to use a high number.
            'meta_key'       => 'pokedex_last_version',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC'
        );

        $the_query = new \WP_Query( $args );
 
        if ( $the_query->have_posts() ) {
           
            while ( $the_query->have_posts() ) {
              
                $the_query->the_post();

                the_title();

                echo "->";
                echo get_post_meta( get_the_ID() , 'pokedex_last_version', true );
                echo "  ";
            }

        } else {
            echo "no posts";
        }

    }


     /**
     * Register the URL to call show_pokemon function
     * * http://poketest.local/wp-json/wp/v2/pokemon/{post id}_fields=metadata
     * http://poketest.local/wp-json/wp/v2/pokemon/2226?_fields=metadata
     */
    public function register_fields_show_pokemon(){
        
        register_rest_field( 'pokemon', 'metadata', 
            array(
                'get_callback' => function ( $data ) {
                    $post_meta = get_post_meta( $data['id'], '', '' );
                    return $post_meta;
                }, 
            ));      
    }

    /**
     * Gets the pokedex number to be displayed in the frontend by an AJAX call
     */
    public function get_pokedex_number() {
       
        $post_id = $_REQUEST['message_id'];
        
        $pokedex = get_post_meta( $post_id, 'pokedex_last_version_and_name', true );
      
        wp_send_json_success(
            array( 
                'pokedex_number' => $pokedex['number'],
                'pokedex_name'   => $pokedex['name'], 
            ), 200 );
        
        return $pokedex['number'];

    }


    /**
     * Calls the Ajax function and retreives the data
     */
    public function pokemon_data( $endpoint ) {
        
        $data = $this->ajax_call_pokemon_data( $endpoint );
      
        return( $data );
    }

    /**
     * Makes an Ajax call with a given API endpoint
     */
    public function ajax_call_pokemon_data( $api ) {  

        $request = wp_remote_get( $api);
    
        if ( is_wp_error( $request )) {

            $error_message = $response->get_error_message();
            echo $error_message;

        }
    
        if( !empty( $request ) ) {

            $body = wp_remote_retrieve_body( $request ); 
            $data = json_decode( $body ); 
            return( $data );

        }         
    }

}
<?php
/**
 * Postype Pokemon
 *
 * @package Understrap
 */

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
        add_action( 'init', array( $this, 'registerCPT' ));
       
        // create initial pokemons when the theme is enabled
        add_action( 'after_switch_theme', array( $this, 'createPokemons' ) );
       // $this->createPokemons(1);  
        
        
        //ajax endpoints
        add_action( 'wp_ajax_pokemon_data', array( $this,'pokemon_data' ) );
        add_action( 'wp_ajax_nopriv_pokemon_data', array( $this,'pokemon_data' ) );
       
        add_action('wp_ajax_get_pokedex_number', array( $this,'get_pokedex_number' ) );
        add_action('wp_ajax_nopriv_get_pokedex_number', array( $this,'get_pokedex_number' ) );


        //REST API endpoints
        add_action( 'rest_api_init',  array( $this,'register_url_get_pokemon' ));
        add_action( 'rest_api_init',  array( $this,'register_url_show_pokemon' ));
        add_action( 'rest_api_init',  array( $this,'register_url_add_pokemon' ));
        add_action( 'rest_api_init',  array( $this,'register_url_list_pokemons_by_pokedex' ));
       
    }

  

     /**
     * Register post type
     */
    public function registerCPT() {
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
            'public'                => true, // the CPT will not have a page on the front-end
            'publicly_queryable'    => true, // the content of the pokemon will not be found on search
            'show_ui'               => true, // pokemons are not editable.
            'show_in_menu'          => true, //the CPT will not have its own page on the admin
            'query_var'             => true,
            'rewrite'               => array( 'slug' => $this->slug ),
            'capability_type'       => 'post',
            'has_archive'           => true,
            'show_in_rest'           => true,
            'hierarchical'          => false,
            'menu_position'         => 0,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields'),
        );
        
        register_post_type( $this->type, $args );
    }
    

     /**
     * Create new pokemons 
     */
    public function createPokemons( $n = 3) {
    
        $data  =  $this->pokemon_data( $this->api_endpoint );
        $count =  $data->count;
        
        for ($i = 0; $i < $n; $i++) {
            
            $rand         = rand( 0, $count );
            $pokemon_data =  $this->pokemon_data( $this->api_endpoint.$rand );
     
            if( !is_null( $pokemon_data ) ) {

                $this->createOnePokemon( $pokemon_data );
              
            } 
            //if there was no pokemon retreived from the API, give it another try
            else { 
                $i--;
            }
            
        }
       
    }

     /**
     * Create one pokemon with the data from the API
     */
    public function createOnePokemon( $pokemon_data ) {
    
        $name         = $pokemon_data->name;
        $pokemon_id   = $pokemon_data->id; //same as $rand
        $weight       = $pokemon_data->weight;
        $primary_type = $pokemon_data->types[0]->type->name;
 
        if ( count( $pokemon_data->types ) > 1 ) {
            $secondary_type = $pokemon_data->types[1]->type->name;
        }
        
        $game_indices = $pokemon_data->game_indices; //can come empty
       
        if(!empty( $game_indices )) {
            $game_first_indice['number'] = reset( $game_indices )->game_index;
            $game_first_indice['name']   = reset( $game_indices )->version->name;  
            $game_last_indice            = end( $game_indices )->game_index;
        }
        //attacks
        $moves = $this->getPokemonMoves( $pokemon_data->moves );

        //basic post atributes
        $my_post = array(
            'post_title'    => $name,
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => $this->type,
        );

        // Insert the post into the database
        $postId = wp_insert_post( $my_post );
        
        add_post_meta( $postId, 'pokemon_id', $pokemon_id, true );
        add_post_meta( $postId, 'description', '', true );
        add_post_meta( $postId, 'primary_type', $primary_type, true );
        add_post_meta( $postId, 'secondary_type', '' , true );
        add_post_meta( $postId, 'weight', $weight, true );
       
        if(!empty( $game_indices )) {
            add_post_meta( $postId, 'pokedex_old_version', $game_first_indice['number'] , true );
            add_post_meta( $postId, 'pokedex_last_version', $game_last_indice, true );
            add_post_meta( $postId, 'pokedex_last_version_and_name', $game_first_indice, true );
        }

        add_post_meta( $postId, 'attacs', $moves );
        
        if (( count( $pokemon_data->types )>1 ) ) {
            add_post_meta( $postId, 'secondary_type', $secondary_type, true );
        } 
    }

     /**
     * Returns a list with info of all the movements
     */
    public function getPokemonMoves( $pokemon_moves ) {
       
        $moves = $pokemon_moves;
        $moves_list= array();
       
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
     * Register the URL to call getRandomPokemon function
     * http://poketest.local/wp-json/add-pokemon/random/ 
     */
    public function register_url_add_pokemon(){
        
        register_rest_route( 'add-pokemon/', 'random/', array(
            'methods'             => 'GET',
            'callback'            => array( $this,'addRandomPokemon' ),
            'permission_callback' => function () {
                return true;
                //return current_user_can( 'edit_posts' ); //this is not working not sure yet why
            }
        ) );
    }

     
    /**
     * Register the URL to call getRandomPokemon function
     * http://poketest.local/wp-json/get-pokemon/random/ 
     */
    public function register_url_get_pokemon(){
        register_rest_route( 'get-pokemon/', 'random/', array(
            'methods'  => 'GET',
            'callback' => array( $this,'getRandomPokemon' ),
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
        register_rest_route( 'list-pokemons/', 'bypokedex/', array(
            'methods'  => 'GET',
            'callback' => array( $this,'listPokemons' ),
            'permission_callback' => function () {
                return true;
                //return current_user_can( 'edit_others_posts' );
            }
        ) );
    }

    /**
     *  list pokemosn by pokedex function
     */
    function listPokemons() {
       
        $args = array(
            'post_type'      => $this->type,
            'orderby'        => 'rand',
            'posts_per_page' => 500, 
            'meta_key'       => 'pokedex_last_version',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC'
        );

        $the_query = new WP_Query( $args );
 
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
     * add Random pokemon to DB
     */
    function addRandomPokemon() {
        $this->createPokemons(1);
    }

    /**
     * get Random pokemon form DB and redirects to its URL
     */
    function getRandomPokemon() {
      
        $args = array(
            'post_type'      => $this->type,
            'orderby'        => 'rand',
            'posts_per_page' => 1, 
        );

        $the_query = new WP_Query( $args );
 
        if ( $the_query->have_posts() ) {
         
            $the_query->the_post();
            wp_redirect(get_permalink());
            wp_reset_postdata();

        } else {
           
            echo "no posts";
        
        }

    }


     /**
     * Register the URL to call showPokemon function
     * * http://poketest.local/wp-json/wp/v2/pokemon/{post id}_fields=metadata
     * http://poketest.local/wp-json/wp/v2/pokemon/2226?_fields=metadata
     */
    public function register_url_show_pokemon(){
        
        register_rest_field( 'pokemon', 'metadata', array(
            'get_callback' => function ( $data ) {
                $post_meta = get_post_meta( $data['id'], '', '' );
                return $post_meta;
            }, 
        ));

            
    }


    public function pokemon_data( $endpoint ) {
        
        $data = $this->ajax_call_pokemon_data( $endpoint );
      
        return( $data );
    }

    
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

    /**
     * Gets the pokedex number to be display in the frontend by an AJAX call
     */
    public function get_pokedex_number() {
        $post_id = $_REQUEST['message_id'];
        
        $pokedex = get_post_meta( $post_id, 'pokedex_last_version_and_name', true );
      
        wp_send_json_success(array( 
            'pokedex_number' => $pokedex['number'],
            'pokedex_name'   => $pokedex['name'], 
        ), 200 );
        
        return $pokedex['number'];

    }

}




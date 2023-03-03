<?php 
/**
 * Postype Pokemon Test
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use PHPUnit\Framework\TestCase;
/**
 * Class Pokemon Test
 *
 */

class PokemonTest extends TestCase {

    public function testcreateOnePokemon() {

        $pokemon_data = (object) array(
            'name' => 'Charizard',
            'id' => 6,
            'weight' => 905,
            'types' => array(
                (object) array(
                    'type' => (object) array(
                        'name' => 'Fire'
                    )
                ),
                (object) array(
                    'type' => (object) array(
                        'name' => 'Flying'
                    )
                )
            ),
            'game_indices' => array(
                (object) array(
                    'game_index' => 6,
                    'version' => (object) array(
                        'name' => 'Red'
                    )
                ),
                (object) array(
                    'game_index' => 6,
                    'version' => (object) array(
                        'name' => 'Blue'
                    )
                )
            ),
            'moves' => array(
                (object) array(
                    'move' => (object) array(
                        'name' => 'Ember'
                    )
                ),
                (object) array(
                    'move' => (object) array(
                        'name' => 'Flamethrower'
                    )
                )
            )
        );

        $pokemon = new Pokemon();
        $pokemon->createOnePokemon( $pokemon_data );

        $args = array(
            'post_type' => 'pokemon',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'pokemon_id',
                    'value' => 6
                )
            )
        );

        $query = new WP_Query( $args );
        $this->assertEquals( 1, $query->found_posts );
    }
}
Based on Understrap WordPress Theme, 2021 Howard Development & Consulting, LLC
Understrap is distributed under the terms of the GNU GPL.

Requires at least: WordPress 6.1.1

Tested up to: WordPress 6.1.1

Requires PHP: 8

License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Contributors: luciadeveloper (Lucia Sanchez Fraile)

Tags: API, REST API, Custom post type, Meta fields, Tests


== Description ==

The code added to the starter theme Understrap defines a custom post type for Pokémon called Pokemon. 
It includes methods for creating, displaying, and managing Pokémon posts, and uses the PokeAPI to fetch data for Pokémon and add it as metadata to the posts. 

This code also enables some API endpoints to add or get Pokemons and to retreive the post information stored in the metadata fields in the DB. 



 == The functionality added to Understrap theme: ==

Everything has been added in poketheme/inc/pokemon except for the CTP template

- (1 and 2) New custom post type called Pokemon 
- (3) Three Pokemon posts added when activating the theme
- (2) Those Pokemon posts are filled with data retrieved form an API endpoint
- (4) A custom template for the CTP is been created. Can be found in poketheme/loop-templates/content-single-pokemon.php
- Tree Endpoints created:
    - (7) /wp-json/add-pokemon/random/ : adds a new pokemon post to the DB, and fills it with the information provided by the API, the same as when activating the theme. 
    - (6) /wp-json/get-pokemon/random/ : shows the post of a random Pokemon from the DB. (with a redirection to the post page)
    - (8) /wp-json/list-pokemons/bypokedex: shows a list of pokemons from the DB, (name and pokedex number) and sorts them by pokedex number. 

- (8) To consult the data of a pokemon post, is not necessary to create a new endpoint. The metadata created for the post is enabled to be displayed in the REST API, so we can make a simple call indicating the data field we need.

        - http://domain.com/wp-json/wp/v2/pokemon/{post id}}?_fields=metadata.weight,metadata.name,metadata.description //specific fields

        - http://domain.com/wp-json/wp/v2/pokemon/{post id}]?_fields=metadata //all fields

        example: http://poketest.local/wp-json/wp/v2/pokemon/2226?_fields=metadata


- (9) There is some level of abstraction so changing the API to retrieve the Pokemon data could be done with some work. 
    -  The creation of the Custom post type is abstract. 
    -  I would have to change class and function names, and use something more general than "pokemon". Animal?
    -  Some simple changes like the API endpoint and some other vars. 

- (10)  In case of heavy use of the DB:
    - There is a fixed number of Pokemons in the API, so I would limit the number of pokemon posts created in WordPress, making sure they are no repeated Pokemons. 
    - Limit on time the attempts to call the new endpoints, especially the one creating new Pokemon Posts. 
    - Retrieving the Attacks of a Pokemon in the PokeApi is a heavy task. I would consider if is necessary. I could develop somehting so it is only called when really needed.
    - I am not storing anything yet in the WP cache, that could make an improvement on the performance.


- A test has been added, which can be found in /tests



 == Notes:  ==

- I spent some time understanding the PokéAPI and especially the Pokemons world. I had no previous knowledge of it. (Probably too much)

- I assumed "attacks" (point 2 h) to be "moves", but could not find a "description" field, so I used "type" -> "name". There is no field called "description".

- Initially, I used ACF, including it in the theme plugins. I discarded this option as the native WordPress custom fields were enough. And the project is more simple like this. 

- I like to add functionality as components. This would mean to have all the files related to certain functionality in one folder. PHP files, JS,  PHP templates, CSS (sass or less), etc. This helps with maintenace, makes it easier to find for other developers and can be reused in another project with some simple changes. This works really well specially with complex projects. In this case, it could seem a bit redundant not to have the custom post template with the rest of the templates, so I decided to leave it there. 

- I would have love to take time to work on the frontend, markup and styling. Creating a sass files estructure, add Gulp/Grunt to compile it.. etc. 

- I assumed the starter theme is accessible but I did not check. This is also something I enjoy working on.

- I havent worked on Typescript for some time, so having to prioritise my time, I left that task undone. I prefere to focus on what I can deliver with certain quality level in the code.

- I added a simple test, so I could show something, but there is quite some room for improvements there. 

- In a bigger project it will make sense to consider using a PHP Code Sniffer in the Github actions, checking WordPress Coding Standards so it runs on the pull requests. Like https://github.com/10up/wpcs-action




== Installation ==

    - Download the theme and add it to your themes folder /wp-content/themes
    - Activate it in the dashboard or use WP-CLI. 
    - Check the custom fields are enabled on your site. Go to Pokemon post, ..., preferences, panels, Custom Fields.
    
![Screenshot](https://luciadeveloper.com/wp-content/uploads/sites/8/2023/03/custom-fields.png)



== Screenshots ==

- Pokemon post
 
 ![Screenshot](https://luciadeveloper.com/wp-content/uploads/sites/8/2023/03/pokemon-post.png)


- Pokemon post editor
 
 ![Screenshot](https://luciadeveloper.com/wp-content/uploads/sites/8/2023/03/pokemon-post-editor.png)



== Changelog ==

= 1.0 = first launch


== Next ==

- Pokemon post properties Primary and Secondary type of Pokemon could be a category.
- When creating 3 new pokemons, I assumed they are different. There is a small chance of getting the same pokemon twice, as I am using the PHP function rand(). SOLVED
- CPT template styles. The Attacs table needs a way to make it shorter. A fixed height with a scroll inside for example. (max-height: 200px; overflow: scroll; display: block;)
- Pokemons archive page filter
- When creating a new Pokemon post, check there is no other Pokemon post with the same info (could be only a check on the name) SOLVED
- Fix: The permision callback in register_rest_route does not work properly in this code. 
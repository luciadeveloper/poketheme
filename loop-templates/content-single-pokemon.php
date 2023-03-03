<?php
/**
 * Single post partial template
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

//get all meta
$name                 = get_post_meta( get_the_ID(), 'color', true );
$descripion           = get_post_meta( get_the_ID(), 'descripion', true ); 
$primary_type         = get_post_meta( get_the_ID(), 'primary_type', true );
$secondary_type       = get_post_meta( get_the_ID(), 'secondary_type', true );
$pokedex_old_version  = get_post_meta( get_the_ID(), 'pokedex_old_version', true );
$attacks               = get_post_meta( get_the_ID(), 'attacks', true );
$pokemon_id           = get_post_meta( get_the_ID(), 'pokemon_id', true );

?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>" data-pokemon-id="<?php echo esc_html( $pokemon_id )?>">

	<header class="entry-header">

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		
	</header><!-- .entry-header -->
	
	<?php echo get_the_post_thumbnail( $post->ID, 'medium' ); ?>

	<div class="entry-content">

		<ul>
			<li><p><?php esc_html_e( 'Name:', 'understrap' ); ?> <?php the_title() ?></p></li>
			<li><p><?php esc_html_e( 'Description:', 'understrap' ); ?>  <?php echo esc_html( $descripion )?></p></li>
			<li><p><?php esc_html_e( 'Primary Type:', 'understrap' ); ?>  <?php echo esc_html( $primary_type )?></p></li>
			<li><p><?php esc_html_e( 'Secondary Type:', 'understrap' ); ?>  <?php echo esc_html( $secondary_type )?></p></li>
			<li><p><?php esc_html_e( 'Pokedex old version:', 'understrap' ); ?>  <?php echo esc_html( $pokedex_old_version ) ?></p></li>
			<li>
				<button id="show_pokedex"><?php esc_html_e( 'Show it!', 'understrap' ); ?> </button>
				<span id="pokedex_number"></span>
				<span id="pokedex_name"></span>
			</li>
			
			<li>
				<p><?php esc_html_e( 'Attacks:', 'understrap' ); ?> </p>
				<table>
					<th><?php esc_html_e( 'Attack name', 'understrap' ); ?> </th>
					<th><?php esc_html_e( 'Attack description', 'understrap' ); ?> </th>
						<?php
						foreach($attacks as $row) {
							?>
								<tr>
								<td><?php echo ($row["name"]); ?></td>
								<td><?php echo ($row["description"]); ?></td>
								</tr>
							<?php
						}
						?>
				</table>
			</li>
		</ul>

		<?php
		the_content();
		?>

	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->


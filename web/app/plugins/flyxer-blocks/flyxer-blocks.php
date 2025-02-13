<?php
/**
 * Plugin Name:       Flyxer Blocks
 * Text Domain:       flyxerblocks
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_swap_image_block_init() {
	register_block_type( __DIR__ . '/build/swap-image' );
	register_block_type( __DIR__ . '/build/blurb' );
	register_block_type( __DIR__ . '/build/flyxer-cta' );
	register_block_type( __DIR__ . '/build/flyxer-post-cards' );
}
add_action( 'init', 'create_block_swap_image_block_init' );


// register meta box
add_action( 'add_meta_boxes', function (){
	add_meta_box(
		'flyxer_post_blurb',
		__( 'Blurb' ),
		'meta_fields_build_meta_box_callback',
		'post',
		'side',
		'default'
	);
} );

// build meta box
function meta_fields_build_meta_box_callback( $post ){
	wp_nonce_field( 'meta_fields_save_meta_box_data', 'meta_fields_meta_box_nonce' );
	$blurb = get_post_meta( $post->ID, '_flyxer_post_blurb', true );
	?>
	<div class="inside">
		<p><strong>Blurb</strong></p>
		<p><textarea id="flyxer_post_blurb" name="flyxer_post_blurb"><?php echo esc_attr( $blurb ); ?></textarea></p>
	</div>
	<?php
}


add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['meta_fields_meta_box_nonce'] ) )
		return;
	if ( ! wp_verify_nonce( $_POST['meta_fields_meta_box_nonce'], 'meta_fields_save_meta_box_data' ) )
		return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	if ( ! current_user_can( 'edit_post', $post_id ) )
		return;

	if ( ! isset( $_POST['flyxer_post_blurb'] ) )
		return;

	$blurb = sanitize_text_field( $_POST['flyxer_post_blurb'] );
	update_post_meta( $post_id, '_flyxer_post_blurb', $blurb );
} );

<?php
function darlingteatime_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style(
		'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'parent-style' )
	);

	// Enqueue Google Fonts
	wp_enqueue_style( 'darlingteatime-fonts', 'https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,700;1,400&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap', array(), null );
}
add_action( 'wp_enqueue_scripts', 'darlingteatime_enqueue_styles' );

function darlingteatime_register_blocks() {
	register_block_type( __DIR__ . '/blocks/top-products-carousel' );
}
add_action( 'init', 'darlingteatime_register_blocks' );

/**
 * Add wrapper for scalloped borders around WooCommerce catalog images
 */
function darlingteatime_add_scallop_wrapper_start() {
    echo '<div class="darling-scallop-wrapper">';
}

function darlingteatime_add_scallop_wrapper_end() {
    echo '</div>';
}

// Wrapping catalog images
// Priority 9: just before the image (which is 10)
add_action( 'woocommerce_before_shop_loop_item_title', 'darlingteatime_add_scallop_wrapper_start', 9 );
// Priority 11: just after the image (which is 10)
add_action( 'woocommerce_before_shop_loop_item_title', 'darlingteatime_add_scallop_wrapper_end', 11 );

/**
 * Add wrapper for scalloped borders around single product main image
 * Filters the single product image html directly.
 */
function darlingteatime_wrap_single_product_image( $html, $post_thumbnail_id ) {
	// Don't wrap if there's no HTML to wrap
	if ( empty( $html ) ) {
		return $html;
	}

	return '<div class="darling-scallop-wrapper">' . $html . '</div>';
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'darlingteatime_wrap_single_product_image', 10, 2 );

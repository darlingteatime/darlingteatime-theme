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

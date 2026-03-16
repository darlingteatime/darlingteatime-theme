<?php
/**
 * Render the Custom Project Carousel block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// If WooCommerce is not active, don't render anything or show a warning.
if ( ! class_exists( 'WooCommerce' ) ) {
	if ( is_user_logged_in() ) {
		echo '<p>' . esc_html__( 'Please install and activate WooCommerce to use the Custom Project Carousel.', 'darlingteatime' ) . '</p>';
	}
	return;
}

// Fetch the Custom Project product by slug.
$product_slug = 'custom-project';

$args = array(
	'name'        => $product_slug,
	'post_type'   => 'product',
	'post_status' => 'publish',
	'numberposts' => 1,
);
$product_posts = get_posts( $args );

if ( empty( $product_posts ) ) {
	if ( is_user_logged_in() ) {
		echo '<p>' . esc_html__( 'Custom Project product not found.', 'darlingteatime' ) . '</p>';
	}
	return;
}

$product = wc_get_product( $product_posts[0]->ID );

if ( ! $product ) {
	return;
}

$image_ids = array();
$featured_image_id = $product->get_image_id();
if ( $featured_image_id ) {
    $image_ids[] = $featured_image_id;
}

$gallery_image_ids = $product->get_gallery_image_ids();
if ( ! empty( $gallery_image_ids ) ) {
    $image_ids = array_merge( $image_ids, $gallery_image_ids );
}

if ( empty( $image_ids ) ) {
	if ( is_user_logged_in() ) {
		echo '<p>' . esc_html__( 'No images found for the Custom Project product.', 'darlingteatime' ) . '</p>';
	}
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'wp-block-darlingteatime-custom-project-carousel',
	)
);

// We define the Interactivity API context.
// 'isAutoscrolling' controls whether the autoscroll behavior is active.
$context = array(
	'isAutoscrolling' => true,
);

$product_url = $product->get_permalink();
$product_name = $product->get_name();

?>
<div
	<?php echo $wrapper_attributes; ?>
	data-wp-interactive="darlingteatime/custom-project-carousel"
	<?php echo wp_interactivity_data_wp_context( $context ); ?>
	data-wp-on--mouseenter="actions.pauseAutoscroll"
	data-wp-on--mouseleave="actions.resumeAutoscroll"
	data-wp-on--touchstart="actions.pauseAutoscroll"
	data-wp-on--touchend="actions.resumeAutoscroll"
>
	<div class="custom-project-carousel-track" data-wp-init="actions.init">
		<?php foreach ( $image_ids as $image_id ) : ?>
			<div class="custom-project-carousel-item">
				<a href="<?php echo esc_url( $product_url ); ?>" title="<?php echo esc_attr( $product_name ); ?>">
					<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail' ); ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</div>

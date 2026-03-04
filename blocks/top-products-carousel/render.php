<?php
/**
 * Render the Top Products Carousel block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

// If WooCommerce is not active, don't render anything or show a warning.
if ( ! class_exists( 'WooCommerce' ) ) {
	if ( is_user_logged_in() ) {
		echo '<p>' . esc_html__( 'Please install and activate WooCommerce to use the Top Products Carousel.', 'darlingteatime' ) . '</p>';
	}
	return;
}

$number_of_products = isset( $attributes['numberOfProducts'] ) ? (int) $attributes['numberOfProducts'] : 5;

// Fetch best-selling products.
$args = array(
	'limit'    => $number_of_products,
	'status'   => 'publish',
	'orderby'  => 'meta_value_num',
	'meta_key' => 'total_sales',
	'order'    => 'DESC',
);

$products = wc_get_products( $args );

if ( empty( $products ) ) {
	if ( is_user_logged_in() ) {
		echo '<p>' . esc_html__( 'No products found.', 'darlingteatime' ) . '</p>';
	}
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'wp-block-darlingteatime-top-products-carousel',
	)
);

// We define the Interactivity API context.
// 'isAutoscrolling' controls whether the autoscroll behavior is active.
$context = array(
	'isAutoscrolling' => true,
);

?>
<div
	<?php echo $wrapper_attributes; ?>
	data-wp-interactive="darlingteatime/top-products-carousel"
	<?php echo wp_interactivity_data_wp_context( $context ); ?>
	data-wp-on--mouseenter="actions.pauseAutoscroll"
	data-wp-on--mouseleave="actions.resumeAutoscroll"
	data-wp-on--touchstart="actions.pauseAutoscroll"
	data-wp-on--touchend="actions.resumeAutoscroll"
>
	<div class="top-products-carousel-track" data-wp-init="actions.init">
		<?php foreach ( $products as $product ) : ?>
			<div class="top-products-carousel-item">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>" title="<?php echo esc_attr( $product->get_name() ); ?>">
					<?php echo $product->get_image( 'woocommerce_thumbnail' ); // outputs the image tag ?>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</div>

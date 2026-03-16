import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';

registerBlockType( 'darlingteatime/custom-project-carousel', {
	apiVersion: 3,
	title: 'Custom Project Carousel',
	category: 'widgets',
	icon: 'images-alt2',
	description: 'A carousel of images from the Custom Project product gallery.',
	attributes: {
	},
	edit: () => {
		const blockProps = useBlockProps();

		return (
			<div { ...blockProps }>
				<Placeholder
					icon="images-alt2"
					label="Custom Project Carousel"
					instructions="Displays a scrolling carousel of images from the 'Custom Project' WooCommerce product gallery."
				/>
			</div>
		);
	},
	save: () => {
		// Rendered via render.php
		return null;
	}
} );

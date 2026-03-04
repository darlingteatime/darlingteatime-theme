import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

registerBlockType( 'darlingteatime/top-products-carousel', {
	apiVersion: 3,
	title: 'Top Products Carousel',
	category: 'widgets',
	icon: 'images-alt2',
	description: 'A carousel of the top selling WooCommerce products.',
	attributes: {
		numberOfProducts: {
			type: 'number',
			default: 5
		}
	},
	edit: ( { attributes, setAttributes } ) => {
		const { numberOfProducts } = attributes;

		// Fetch products in the editor just to show a placeholder preview
		const products = useSelect(
			( select ) => {
				return select( 'core' ).getEntityRecords( 'postType', 'product', {
					per_page: numberOfProducts,
					orderby: 'meta_value_num',
					meta_key: 'total_sales',
					order: 'desc',
					_fields: 'id,title'
				} );
			},
			[ numberOfProducts ]
		);

		const blockProps = useBlockProps();

		return (
			<div { ...blockProps }>
				<InspectorControls>
					<PanelBody title="Settings">
						<RangeControl
							label="Number of Products"
							value={ numberOfProducts }
							onChange={ ( value ) => setAttributes( { numberOfProducts: value } ) }
							min={ 1 }
							max={ 20 }
						/>
					</PanelBody>
				</InspectorControls>
				<Placeholder
					icon="images-alt2"
					label="Top Products Carousel"
					instructions={`Showing top ${numberOfProducts} best-selling products.`}
				>
					{ ! products && <p>Loading products...</p> }
					{ products && products.length === 0 && <p>No products found.</p> }
					{ products && products.length > 0 && (
						<ul>
							{ products.map( ( product ) => (
								<li key={ product.id }>{ product.title.rendered }</li>
							) ) }
						</ul>
					) }
				</Placeholder>
			</div>
		);
	},
	save: () => {
		// Rendered via render.php
		return null;
	}
} );

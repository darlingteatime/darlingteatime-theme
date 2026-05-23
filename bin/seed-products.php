<?php
// Ensure we are inside WordPress context
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
wp_set_current_user( 1 );

// Clean up existing products to prevent duplicates/orphans
echo "Cleaning up existing products...\n";
$existing_products = wc_get_products( array( 'limit' => -1 ) );
foreach ( $existing_products as $p ) {
    $p->delete( true ); // Force delete from database
}

// Clean up any remaining orphaned posts in the database
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ('product', 'product_variation')" );

// Prepare and register the custom tea placeholder image in the media library
$placeholder_path = '/var/www/html/wp-content/themes/darlingteatime-theme/bin/placeholder.png';
$attachment_id = 0;

$existing_attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_title = 'darlingteatime-placeholder'" );

if ( $existing_attachment ) {
    $attachment_id = (int) $existing_attachment;
    echo "Using existing placeholder image (Attachment ID: $attachment_id)\n";
} else if ( file_exists( $placeholder_path ) ) {
    echo "Registering new placeholder image in the media library...\n";
    $upload_dir = wp_upload_dir();
    $image_data = @file_get_contents( $placeholder_path );
    $filename = 'placeholder.png';
    
    if ( $image_data ) {
        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        
        file_put_contents( $file, $image_data );
        
        $wp_filetype = wp_check_filetype( $filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => 'darlingteatime-placeholder',
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment( $attachment, $file );
        
        if ( ! is_wp_error( $attachment_id ) && $attachment_id > 0 ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
            wp_update_attachment_metadata( $attachment_id, $attachment_data );
            echo "Successfully registered placeholder image! (Attachment ID: $attachment_id)\n";
        } else {
            $attachment_id = 0;
            echo "Warning: Failed to insert attachment into media library.\n";
        }
    } else {
        echo "Warning: Cannot read placeholder image file.\n";
    }
} else {
    echo "Warning: Placeholder image file not found at $placeholder_path\n";
}

// 1. Create the Custom Project product for the front-page carousel
echo "Creating Custom Project product (slug: custom-project)...\n";
$custom_product = new WC_Product_Simple();
$custom_product->set_name( 'Custom Project' );
$custom_product->set_slug( 'custom-project' );
$custom_product->set_status( 'publish' );
$custom_product->set_description( 'Create your custom tea blend or select a personalized teapot design for your teatime experience.' );

if ( $attachment_id > 0 ) {
    $custom_product->set_image_id( $attachment_id );
    // Populate the gallery with multiple slides of the placeholder image
    $custom_product->set_gallery_image_ids( array( $attachment_id, $attachment_id, $attachment_id ) );
}

$custom_id = $custom_product->save();
if ( $custom_id ) {
    echo "Successfully created Custom Project product (ID: $custom_id)!\n";
} else {
    echo "Warning: Failed to create Custom Project product.\n";
}

// 2. Import the 25 standard WooCommerce sample products
$file_path = '/var/www/html/wp-content/plugins/woocommerce/sample-data/sample_products.csv';
if ( ! file_exists( $file_path ) ) {
    echo "Error: CSV file not found at $file_path\n";
    exit(1);
}

$handle = fopen( $file_path, 'r' );
if ( ! $handle ) {
    echo "Error: Cannot open $file_path\n";
    exit(1);
}

// Read header
$header = fgetcsv( $handle );
if ( $header ) {
    // Strip UTF-8 Byte Order Mark (BOM) from the first column if present
    $header[0] = preg_replace( '/\x{FEFF}/u', '', $header[0] );
}

// Map headers to column indices
$map = array_flip( $header );

$imported = 0;
while ( ( $row = fgetcsv( $handle ) ) !== false ) {
    $type = isset( $map['Type'] ) && isset( $row[$map['Type']] ) ? $row[$map['Type']] : 'simple';
    $name = isset( $map['Name'] ) && isset( $row[$map['Name']] ) ? $row[$map['Name']] : '';
    $sku = isset( $map['SKU'] ) && isset( $row[$map['SKU']] ) ? $row[$map['SKU']] : '';
    
    if ( empty( $name ) ) {
        continue;
    }
    
    // Create correct product type object
    if ( $type === 'variable' ) {
        $product = new WC_Product_Variable();
    } else {
        $product = new WC_Product_Simple();
    }
    
    $product->set_name( $name );
    $product->set_sku( $sku );
    $product->set_status( 'publish' );
    
    if ( isset( $map['Description'] ) && isset( $row[$map['Description']] ) ) {
        $product->set_description( $row[$map['Description']] );
    }
    if ( isset( $map['Short description'] ) && isset( $row[$map['Short description']] ) ) {
        $product->set_short_description( $row[$map['Short description']] );
    }
    if ( isset( $map['Regular price'] ) && isset( $row[$map['Regular price']] ) ) {
        $product->set_regular_price( $row[$map['Regular price']] );
    }
    if ( isset( $map['Sale price'] ) && isset( $row[$map['Sale price']] ) ) {
        $product->set_sale_price( $row[$map['Sale price']] );
    }
    
    // Map Categories
    if ( isset( $map['Categories'] ) && isset( $row[$map['Categories']] ) && ! empty( $row[$map['Categories']] ) ) {
        $category_names = explode( ',', $row[$map['Categories']] );
        $category_ids = array();
        foreach ( $category_names as $cat_name ) {
            $cat_name = trim( $cat_name );
            if ( strpos( $cat_name, '>' ) !== false ) {
                $parts = explode( '>', $cat_name );
                $cat_name = trim( end( $parts ) );
            }
            $term = term_exists( $cat_name, 'product_cat' );
            if ( ! $term ) {
                $term = wp_insert_term( $cat_name, 'product_cat' );
            }
            if ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) {
                $category_ids[] = (int) $term['term_id'];
            }
        }
        $product->set_category_ids( $category_ids );
    }
    
    // Assign placeholder image if created
    if ( $attachment_id > 0 ) {
        $product->set_image_id( $attachment_id );
    }
    
    $product_id = $product->save();
    if ( $product_id ) {
        echo "Successfully imported: $name (ID: $product_id)\n";
        $imported++;
    } else {
        echo "Failed to import: $name\n";
    }
}
fclose( $handle );

// Force WooCommerce to regenerate lookup tables to ensure immediate shop page visibility
if ( class_exists( 'WC_Install' ) ) {
    WC_Install::create_tables();
}
if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
    wc_update_product_lookup_tables();
}

echo "Successfully completed seeding! Total products imported: $imported\n";

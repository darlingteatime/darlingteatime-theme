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
// Use get_stylesheet_directory() to dynamically resolve path in local and Playground environments
$placeholder_path = get_stylesheet_directory() . '/bin/placeholder.png';
$attachment_id = 0;

$existing_attachment = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_title = 'darlingteatime-placeholder'" );

if ( $existing_attachment ) {
    $attachment_id = (int) $existing_attachment;
    echo "Using existing placeholder image (Attachment ID: $attachment_id)\n";
} else {
    // Correct paths for Playground /var/www/html/ vs /wordpress/ mismatch
    if ( ! file_exists( $placeholder_path ) ) {
        $alt_placeholder = str_replace( '/var/www/html/', '/wordpress/', $placeholder_path );
        if ( file_exists( $alt_placeholder ) ) {
            $placeholder_path = $alt_placeholder;
        }
    }

    $image_data = null;
    $filename = 'placeholder.png';
    $mime_type = 'image/png';

    if ( file_exists( $placeholder_path ) ) {
        echo "Reading placeholder image from local theme path: $placeholder_path\n";
        $image_data = @file_get_contents( $placeholder_path );
    } else {
        echo "Placeholder image file not found locally at $placeholder_path. Attempting remote download...\n";
        $remote_urls = array(
            'https://raw.githubusercontent.com/darlingteatime/darlingteatime-theme/readme-update/bin/placeholder.png',
            'https://raw.githubusercontent.com/darlingteatime/darlingteatime-theme/main/bin/placeholder.png'
        );

        foreach ( $remote_urls as $url ) {
            echo "Trying remote URL: $url\n";
            $response = wp_remote_get( $url, array( 'timeout' => 15 ) );
            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $image_data = wp_remote_retrieve_body( $response );
                echo "Successfully downloaded placeholder image from remote!\n";
                break;
            }
        }

        if ( ! $image_data ) {
            echo "Warning: Could not download PNG placeholder. Creating a self-contained SVG placeholder instead!\n";
            $image_data = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800" width="800" height="800">
              <rect width="100%" height="100%" fill="#faf6f0"/>
              <circle cx="400" cy="400" r="250" fill="#f0e5d8" stroke="#d5c3b0" stroke-width="4"/>
              <text x="50%" y="45%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="48" fill="#5c4033" font-weight="bold">Darling Teatime</text>
              <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="24" fill="#8b5a2b" font-style="italic">Premium Tea Experience</text>
            </svg>';
            $filename = 'placeholder.svg';
            $mime_type = 'image/svg+xml';
        }
    }

    if ( $image_data ) {
        $upload_dir = wp_upload_dir();
        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents( $file, $image_data );
        
        $attachment = array(
            'post_mime_type' => $mime_type,
            'post_title'     => 'darlingteatime-placeholder',
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment( $attachment, $file );
        
        if ( ! is_wp_error( $attachment_id ) && $attachment_id > 0 ) {
            if ( $mime_type !== 'image/svg+xml' ) {
                require_once ABSPATH . 'wp-admin/includes/image.php';
                $attachment_data = wp_generate_attachment_metadata( $attachment_id, $file );
                wp_update_attachment_metadata( $attachment_id, $attachment_data );
            }
            echo "Successfully registered placeholder image! (Attachment ID: $attachment_id)\n";
        } else {
            $attachment_id = 0;
            echo "Warning: Failed to insert attachment into media library.\n";
        }
    } else {
        echo "Warning: Cannot obtain placeholder image data.\n";
    }
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
// Use WP_PLUGIN_DIR to dynamically resolve plugins folder in local and Playground environments
$file_path = WP_PLUGIN_DIR . '/woocommerce/sample-data/sample_products.csv';

// Fallback path check in Playground if /var/www/html/ is in path but actual is /wordpress/
if ( ! file_exists( $file_path ) ) {
    $alt_path = str_replace( '/var/www/html/', '/wordpress/', $file_path );
    if ( file_exists( $alt_path ) ) {
        $file_path = $alt_path;
    }
}

// Remote URL fallback if still not found
if ( ! file_exists( $file_path ) ) {
    echo "CSV file not found locally at $file_path. Attempting to download from official WooCommerce repository...\n";
    $remote_csv_url = 'https://raw.githubusercontent.com/woocommerce/woocommerce/trunk/sample-data/sample_products.csv';
    
    $response = wp_remote_get( $remote_csv_url, array( 'timeout' => 30 ) );
    if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
        $csv_data = wp_remote_retrieve_body( $response );
        $upload_dir = wp_upload_dir();
        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file_path = $upload_dir['path'] . '/sample_products.csv';
        } else {
            $file_path = $upload_dir['basedir'] . '/sample_products.csv';
        }
        file_put_contents( $file_path, $csv_data );
        echo "Successfully downloaded WooCommerce sample products CSV to $file_path!\n";
    } else {
        echo "Error: CSV file not found locally, and failed to download from remote WooCommerce repository.\n";
        exit(1);
    }
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

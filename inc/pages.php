<?php

function custom_product_post_type() {
	// Register product page
    $labels = array(
        'name'                  => _x( 'Products', 'Post Type General Name', 'icecream' ),
        'singular_name'         => _x( 'Product', 'Post Type Singular Name', 'icecream' ),
        'menu_name'             => __( 'Products', 'icecream' ),
        'name_admin_bar'        => __( 'Product', 'icecream' ),
        'archives'              => __( 'Product Archives', 'icecream' ),
        'attributes'            => __( 'Product Attributes', 'icecream' ),
        'parent_item_colon'     => __( 'Parent Product:', 'icecream' ),
        'all_items'             => __( 'All Products', 'icecream' ),
        'add_new_item'          => __( 'Add New Product', 'icecream' ),
        'add_new'               => __( 'Add New', 'icecream' ),
        'new_item'              => __( 'New Product', 'icecream' ),
        'edit_item'             => __( 'Edit Product', 'icecream' ),
        'update_item'           => __( 'Update Product', 'icecream' ),
        'view_item'             => __( 'View Product', 'icecream' ),
        'view_items'            => __( 'View Products', 'icecream' ),
        'search_items'          => __( 'Search Product', 'icecream' ),
        'not_found'             => __( 'Not found', 'icecream' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'icecream' ),
        'featured_image'        => __( 'Featured Image', 'icecream' ),
        'set_featured_image'    => __( 'Set featured image', 'icecream' ),
        'remove_featured_image' => __( 'Remove featured image', 'icecream' ),
        'use_featured_image'    => __( 'Use as featured image', 'icecream' ),
        'insert_into_item'      => __( 'Insert into product', 'icecream' ),
        'uploaded_to_this_item' => __( 'Uploaded to this product', 'icecream' ),
        'items_list'            => __( 'Products list', 'icecream' ),
        'items_list_navigation' => __( 'Products list navigation', 'icecream' ),
        'filter_items_list'     => __( 'Filter products list', 'icecream' ),
    );
    $args = array(
        'label'                 => __( 'Product', 'icecream' ),
        'description'           => __( 'Custom post type for products', 'icecream' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'custom-fields' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-cart',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
		'rewrite' => false,
    );
    register_post_type( 'icecreams', $args );
	
	// Register sales page
	$labels = array(
        'name' => 'Sales',
        'singular_name' => 'Sale',
        'add_new' => 'Add New Sale',
        'add_new_item' => 'Add New Sale',
        'edit_item' => 'Edit Sale',
        'new_item' => 'New Sale',
        'view_item' => 'View Sale',
        'search_items' => 'Search Sales',
        'not_found' => 'No sales found',
        'not_found_in_trash' => 'No sales found in trash',
        'menu_name' => 'Sales'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => false,
        'supports' => array('title'),
    );

    register_post_type('sale', $args);

}
add_action( 'init', 'custom_product_post_type', 0 );

function custom_product_taxonomy() {
    $labels = array(
        'name' => 'Categories',
        'singular_name' => 'Category',
        'search_items' => 'Search Categories',
        'all_items' => 'All Categories',
        'parent_item' => 'Parent Category',
        'parent_item_colon' => 'Parent Category:',
        'edit_item' => 'Edit Category',
        'update_item' => 'Update Category',
        'add_new_item' => 'Add New Category',
        'new_item_name' => 'New Category Name',
        'menu_name' => 'Categories',
    );

    $args = array(
        'hierarchical' => true,  // Set to true for categories, false for tags
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'product-category'), // Customize the slug as needed
    );

    register_taxonomy('icecream_category', 'icecreams', $args);
}
add_action('init', 'custom_product_taxonomy');
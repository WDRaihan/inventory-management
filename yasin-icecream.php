<?php
/**
 * Plugin Name:       Icecream er Hisab Nikash
 * Version:           1.0.0
 * Author:            MD Raihan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Register product
function custom_product_post_type() {

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
        //'taxonomies'            => array( 'category', 'post_tag' ),
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
    );
    register_post_type( 'icecreams', $args );
	
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
        'rewrite' => array('slug' => 'sales'),
        'supports' => array('title'),
    );

    register_post_type('sale', $args);

}
add_action( 'init', 'custom_product_post_type', 0 );


// Add custom field to link sales with products
function custom_sale_product_meta_box() {

	add_meta_box(
        'custom-icecream-meta-box',
        'Product Stock',
        'custom_icecream_meta_box_callback',
        'icecreams',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'custom_sale_product_meta_box');


function custom_icecream_meta_box_callback($post) {
    $product_stock = get_post_meta($post->ID, 'product_stock', true);
	
	$icecream_price = get_post_meta($post->ID, 'icecream_price', true);
	
    echo '<label for="icecream_price">Price:</label>';
    echo '<input type="text" id="icecream_price" name="icecream_price" value="' . esc_attr($icecream_price) . '">';
	
    echo '<label for="product_stock">Stock:</label>';
    echo '<input type="text" id="product_stock" name="product_stock" value="' . esc_attr($product_stock) . '">';
	wp_nonce_field( 'icecream_meta_box_nonce', 'icecream_meta_box_nonce' );
}

// Save selected product IDs
function custom_save_selected_products($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	
	if ( ! isset( $_POST['icecream_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['icecream_meta_box_nonce'], 'icecream_meta_box_nonce' ) )
        return;
	
	if (isset($_POST['icecream_price'])) {
		$icecream_price = $_POST['icecream_price'];
		update_post_meta($post_id, 'icecream_price', $icecream_price);
	}
	if (isset($_POST['product_stock'])) {
		$product_stock = $_POST['product_stock'];
		update_post_meta($post_id, 'product_stock', $product_stock);
	}
    
}
add_action('save_post', 'custom_save_selected_products');



add_action('admin_init', 'gpm_add_meta_boxes', 2);

function gpm_add_meta_boxes() {
    add_meta_box( 'gpminvoice-group', 'Create invoice', 'Repeatable_meta_box_display', 'sale', 'normal', 'default');
}

function Repeatable_meta_box_display() {
    global $post;
	$post_id = $post->ID;
    $gpminvoice_group = get_post_meta($post->ID, 'customdata_group', true);
     wp_nonce_field( 'gpm_repeatable_meta_box_nonce', 'gpm_repeatable_meta_box_nonce' );
    ?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#add-row').on('click', function() {
			var row = $('.empty-row.screen-reader-text').clone(true);
			row.removeClass('empty-row screen-reader-text');
			row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
			return false;
		});

		$('.remove-row').on('click', function() {
			$(this).parents('tr').remove();
			//Update subtotal price
			calculate_subtotal_price();
			return false;
		});
	});
</script>
<style>
	.wp-core-ui select.disabled, .wp-core-ui select:disabled {
		color: #000;
		border-color: #8c8f94;
	}
</style>
<?php 
	$order_status = get_post_meta( $post_id, 'order_status', true );
	
	$visibility = '';
	$disabled = '';
	if($order_status == 'delivered'){
		$visibility = 'readonly';
		$disabled = 'disabled';
	}
?>
<!--Client info-->
<table width="70%" style="margin:0 auto">
<tbody style="text-align:left">
	<tr>
		<th></th>
		<th>Client Name</th>
		<th><input type="text" name="client_name" value="<?php echo get_post_meta( $post_id, 'client_name', true ); ?>" /></th>
	</tr>
	<tr>
		<th></th>
		<th>Client Number</th>
		<th><input type="text" name="client_number" value="<?php echo get_post_meta( $post_id, 'client_number', true ); ?>" /></th>
	</tr>
	<tr>
		<th></th>
		<th>Client Address</th>
		<th><input type="text" name="client_address" value="<?php echo get_post_meta( $post_id, 'client_address', true ); ?>" /></th>
	</tr>
	</tbody>
</table>
<br>
<br>
<hr>
<br>
<br>

<!--Repeatable form-->
<table id="repeatable-fieldset-one" width="100%">
	<tbody>
		<tr>
			<th width="25%">
				Icecream
			</th>
			<th width="20%">
				Price
			</th>
			<th width="20%">
				Quantity
			</th>
			<th width="10%">
				Available
			</th>
			<th>
				Total
			</th>
			<?php if($order_status != 'delivered') : ?>
			<th>Action</th>
			<?php endif; ?>
		</tr>
		<?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
		<tr>
			<td width="25%">
				<!--        <input type="text"  placeholder="Title" name="IcecreamName[]" value="<?php if($field['IcecreamName'] != '') echo esc_attr( $field['IcecreamName'] ); ?>" />-->
				<select class="icecream-name" name="IcecreamName[]" id="" <?php echo $disabled; ?> >
					<option>-Select Icecream-</option>
					<?php
				$args = array(
					'post_type' => 'icecreams',
					'posts_per_page' => -1
					);
				$loop = new WP_Query( $args );
				if ( $loop->have_posts() ) {
					while ( $loop->have_posts() ) : $loop->the_post();
						?>
						<?php $stock = get_post_meta(get_the_ID(), 'product_stock', true); ?>
					<option id="<?php echo get_the_ID(); ?>" product_stock="<?php echo $stock; ?>" price="<?php echo get_post_meta(get_the_ID(), 'icecream_price', true); ?>" value="<?php echo get_the_ID(); ?>" <?php selected($field['IcecreamName'], get_the_ID(), true); ?>><?php the_title(); ?> (S: <?php echo $stock; ?>)</option>
					<?php
					endwhile;
				}
				wp_reset_postdata();
			?>
				</select>
				<input type="hidden" name="product_id[]" value="" <?php echo $visibility; ?> >
			</td>
			<td width="20%">
				<input type="text" placeholder="<?php if($field['ProductPrice'] != '') echo esc_attr( $field['ProductPrice'] ); ?>" name="ProductPrice[]" value="<?php if($field['ProductPrice'] != '') echo esc_attr( $field['ProductPrice'] ); ?>" <?php echo $visibility; ?> />
			</td>
			<td width="20%">
				<input type="text" min="1" max="" placeholder="Quantity" name="ProductQuantity[]" value="<?php if($field['ProductQuantity'] != '') echo esc_attr( $field['ProductQuantity'] ); ?>" <?php echo $visibility; ?> />
			</td>
			<td width="10%">
				<input type="hidden" class="AvailableStock" name="AvailableStock[]" value="<?php if($field['AvailableStock'] != '') echo esc_attr( $field['AvailableStock'] ); ?>" />
				<span class="AvailableStock"><?php if($field['AvailableStock'] != '') echo esc_attr( $field['AvailableStock'] ); ?></span>
			</td>
			<td width="20%">
				<input type="text" placeholder="Total" name="TotalPrice[]" value="<?php if($field['TotalPrice'] != '') echo esc_attr( $field['TotalPrice'] ); ?>" readonly />
			</td>
			<?php if($order_status != 'delivered') : ?>
			<td width="15%"><a class="button remove-row" href="#1">Remove</a></td>
			<?php endif; ?>
		</tr>
		<?php
    }
    else :
    // show a blank one
    ?>
		<tr>
			<td width="25%">
				<!--        <input type="text" placeholder="Title" title="Title" name="IcecreamName[]" />-->
				<select class="icecream-name" name="IcecreamName[]" id="">
					<option>-Select Icecream-</option>
					<?php
				$args = array(
					'post_type' => 'icecreams',
					'posts_per_page' => -1
					);
				$loop = new WP_Query( $args );
				if ( $loop->have_posts() ) {
					while ( $loop->have_posts() ) : $loop->the_post();
						?>
						<?php $stock = get_post_meta(get_the_ID(), 'product_stock', true); ?>
					<option id="<?php echo get_the_ID(); ?>" product_stock="<?php echo $stock; ?>" price="<?php echo get_post_meta(get_the_ID(), 'icecream_price', true); ?>" value="<?php echo get_the_ID(); ?>"><?php the_title(); ?> (S: <?php echo $stock; ?>)</option>
					<?php
					endwhile;
				}
				wp_reset_postdata();
			?>
				</select>
				<input type="hidden" name="product_id[]" value="">
			</td>
			<td width="20%">
				<input type="text" placeholder="0" name="ProductPrice[]" />
			</td>
			<td width="20%">
				<input type="text" min="1" max="" placeholder="Quantity" name="ProductQuantity[]" />
			</td>
			<td width="10%">
				<input type="hidden" class="AvailableStock" name="AvailableStock[]" value="" />
				<span class="AvailableStock">--</span>
			</td>
			<td>
				<input type="text" placeholder="Total" name="TotalPrice[]" readonly />
			</td>
			<td><a class="button  cmb-remove-row-button button-disabled" href="#">Remove</a></td>
		</tr>
		<?php endif; ?>

		<!-- empty hidden one for jQuery -->
		<tr class="empty-row screen-reader-text">
			<td>
				<!--        <input type="text" placeholder="Title" name="IcecreamName[]"/>-->
				<select class="icecream-name" name="IcecreamName[]" id="">
					<option value="">-Select Icecream-</option>
					<?php
				$args = array(
					'post_type' => 'icecreams',
					'posts_per_page' => -1
					);
				$loop = new WP_Query( $args );
				if ( $loop->have_posts() ) {
					while ( $loop->have_posts() ) : $loop->the_post();
						?>
					<?php $stock = get_post_meta(get_the_ID(), 'product_stock', true); ?>
					<option id="<?php echo get_the_ID(); ?>" product_stock="<?php echo $stock; ?>" price="<?php echo get_post_meta(get_the_ID(), 'icecream_price', true); ?>" value="<?php echo get_the_ID(); ?>"><?php the_title(); ?> (S: <?php echo $stock; ?>)</option>
					<?php
					endwhile;
				}
				wp_reset_postdata();
			?>
				</select>
				<input type="hidden" name="product_id[]" value="">
			</td>
			<td width="20%">
				<input type="text" placeholder="0" name="ProductPrice[]" />
			</td>
			<td width="20%">
				<input type="text" min="1" max="" placeholder="Quantity" name="ProductQuantity[]" />
			</td>
			<td width="10%">
				<input type="hidden" placeholder="" class="AvailableStock" name="AvailableStock[]" value="" />
				<span class="AvailableStock">--</span>
			</td>
			<td>
				<input type="text" placeholder="Total" name="TotalPrice[]" readonly />
			</td>
			<td><a class="button remove-row" href="#">Remove</a></td>
		</tr>
	</tbody>
</table>
<?php if($order_status != 'delivered') : ?>
<p><a id="add-row" class="button" href="#">Add another</a></p>
<?php endif; ?>

<br>
<br>
<hr>
<br>
<br>

<!--Subtotal section-->
<table width="70%" style="margin:0 auto">
<tbody style="text-align:left">
	<tr>
		<th></th>
		<th></th>
		<th>Sub Total</th>
		<th><input type="text" name="subtotal_price" value="<?php if($order_status == 'delivered'){ echo get_post_meta( $post_id, 'subtotal_price', true ); }else{ echo get_post_meta( $post_id, '_subtotal_price', true ); } ?>" readonly /></th>
		<th></th>
	</tr>
	<tr>
		<th></th>
		<th></th>
		<th>Cash Received</th>
		<th><input type="text" name="cash_received" value="<?php if($order_status == 'delivered'){ echo get_post_meta( $post_id, 'cash_received', true ); }else{ echo get_post_meta( $post_id, '_cash_received', true ); } ?>"></th>
		<th></th>
	</tr>
	<tr>
		<th></th>
		<th></th>
		<th>Due</th>
		<th><input type="text" name="due_amount" value="<?php if($order_status == 'delivered'){ echo get_post_meta( $post_id, 'due_amount', true ); }else{ echo get_post_meta( $post_id, '_due_amount', true ); } ?>" readonly></th>
		<th></th>
	</tr>
	</tbody>
</table>
<br>
<hr>
<br>
<?php $_order_status = get_post_meta( $post_id, '_order_status', true ); ?>
<label for="order_status">Order Status</label>
<select name="order_status" id="order_status">
	<?php if($_order_status != 'once_delivered'): ?>
	<option value="">-Select order status-</option>
	<?php endif; ?>
	<?php //if($_order_status != 'once_canceled'): ?>
	<option value="delivered" <?php selected($order_status,'delivered',true); ?>>Complete</option>
	<?php //endif; ?>
	<option value="canceled" <?php selected($order_status,'canceled',true); ?>>Canceled</option>
</select>

<script>
	jQuery(document).on('change', '.icecream-name', function() {
		var product_id = jQuery(this).val();
		var product_stock = jQuery(this).find('option:selected').attr('product_stock');
		var product_price = jQuery(this).find('option:selected').attr('price');

		jQuery(this).parents('tr').find('input[name="product_id[]"]').val(product_id);
		jQuery(this).parents('tr').find('input[name="ProductPrice[]"]').val(product_price);
		jQuery(this).parents('tr').find('input[name="AvailableStock[]"]').val(product_stock);
		if (!product_stock) {
			product_stock = '0';
		}
		jQuery(this).parents('tr').find('span.AvailableStock').text(product_stock);
		jQuery(this).parents('tr').find('input[name="ProductQuantity[]"]').val(0);
		jQuery(this).parents('tr').find('input[name="ProductQuantity[]"]').attr('max', product_stock);
		jQuery(this).parents('tr').find('input[name="TotalPrice[]"]').val('');
		
		//Update subtotal price
		calculate_subtotal_price();
	});

	jQuery(document).on('input', 'input[name="ProductQuantity[]"]', function() {
		var qty = jQuery(this).val();
		
		var stock = jQuery(this).parents('tr').find('select.icecream-name option:selected').attr('product_stock');
		var avialable = stock - qty;
		jQuery(this).parents('tr').find('input[name="AvailableStock[]"]').val(avialable);
		jQuery(this).parents('tr').find('span.AvailableStock').text(avialable);
		
		var item_price = jQuery(this).parents('tr').find('input[name="ProductPrice[]"]').val();
		
		var total_price = item_price * qty;
		
		jQuery(this).parents('tr').find('input[name="TotalPrice[]"]').val(total_price);
		
		//Update subtotal price
		calculate_subtotal_price();
		
	});
	
	jQuery(document).on('input', 'input[name="ProductPrice[]"]', function() {
		var item_price = jQuery(this).val();
				
		var qty = jQuery(this).parents('tr').find('input[name="ProductQuantity[]"]').val();
		
		var total_price = item_price * qty;
		
		jQuery(this).parents('tr').find('input[name="TotalPrice[]"]').val(total_price);
		
		//Update subtotal price
		calculate_subtotal_price();
		
	});

	jQuery(document).ready(function() {
		jQuery('.icecream-name').each(function() {
			var product_id = jQuery(this).val();
			var product_stock = jQuery(this).find('option:selected').attr('product_stock');
			var product_price = jQuery(this).find('option:selected').attr('price');

			jQuery(this).parents('tr').find('input[name="product_id[]"]').val(product_id);
			//jQuery(this).parents('tr').find('input[name="ProductPrice[]"]').val(product_price);
			//jQuery(this).parents('tr').find('input[name="AvailableStock[]"]').val(product_stock);
			if (!product_stock) {
				product_stock = '0';
			}
			//jQuery(this).parents('tr').find('span.AvailableStock').text(product_stock);
			//jQuery(this).parents('tr').find('input[name="ProductQuantity[]"]').val('');
			jQuery(this).parents('tr').find('input[name="ProductQuantity[]"]').attr('max', product_stock);
		});

		jQuery('input[name="ProductQuantity[]"]').each(function() {
			//var qty = jQuery(this).val();
			
			//var stock = jQuery(this).parents('tr').find('select.icecream-name option:selected').attr('product_stock');
			//var avialable = stock - qty;
			//jQuery(this).parents('tr').find('input[name="AvailableStock[]"]').val(stock);
			//jQuery(this).parents('tr').find('span.AvailableStock').text(stock);
			
		});
		
		calculate_subtotal_price();
	});
	
	//Calculate subtotal price
	function calculate_subtotal_price(){
		var totalPrice = 0;
		
		jQuery('input[name="TotalPrice[]"]').each(function() {
			// Get the value of the current input field
			var price = parseInt(jQuery(this).val());
			
			if (!isNaN(price)) {
				
				totalPrice += price;
			}
		});
		
		jQuery('input[name="subtotal_price"]').val(totalPrice);
		
		calculate_due_amount();
	}
	
	//Calculate due price
	function calculate_due_amount(){
		var duePrice = 0;
		
		var subTotal = parseInt(jQuery('input[name="subtotal_price"]').val());
		var cashReceived = parseInt(jQuery('input[name="cash_received"]').val());
		
		if (!isNaN(subTotal) && !isNaN(cashReceived)) {
				
			duePrice = subTotal - cashReceived;
			jQuery('input[name="due_amount"]').val(duePrice);
		}
	}
	
	jQuery('input[name="cash_received"]').on('input', function(){
		calculate_due_amount();
	});
	
</script>
<?php
}

add_action('save_post', 'custom_repeatable_meta_box_save');
function custom_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['gpm_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['gpm_repeatable_meta_box_nonce'], 'gpm_repeatable_meta_box_nonce' ) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!current_user_can('edit_post', $post_id)) return;
	
	//Update order status
	update_post_meta( $post_id, 'order_status', $_POST['order_status'] );
	
	$_order_status = get_post_meta($post_id, '_order_status', true);
	
	//Get saved data to compare and update new data
    $old = get_post_meta($post_id, 'customdata_group', true);
    $new = array();
    $invoiceItems = $_POST['IcecreamName'];
    $prices = $_POST['ProductPrice'];
    $ProductQuantity = $_POST['ProductQuantity'];
    $TotalPrice = $_POST['TotalPrice'];
    $AvailableStock = $_POST['AvailableStock'];
    $product_id = $_POST['product_id'];
	
     $count = count( $invoiceItems );
     for ( $i = 0; $i < $count; $i++ ) {
        if ( $invoiceItems[$i] != '' ) :
           	
			if($_order_status != 'once_delivered'){
				$new[$i]['IcecreamName'] = stripslashes( strip_tags( $invoiceItems[$i] ) );
				$new[$i]['ProductPrice'] = stripslashes( $prices[$i] ); // and however you want to sanitize
				$new[$i]['ProductQuantity'] = stripslashes( $ProductQuantity[$i] );
				$new[$i]['AvailableStock'] = stripslashes( $AvailableStock[$i] );

				$total_price = (intval($prices[$i]) * intval($ProductQuantity[$i]));
				$new[$i]['TotalPrice'] = stripslashes( $total_price );
			}
		 	if( $_POST['order_status'] == 'delivered' ){
				
				if($_order_status != 'once_delivered'){
					
					update_post_meta( $product_id[$i], 'product_stock', $AvailableStock[$i] );
				}
				
			}elseif( $_POST['order_status'] == 'canceled' ){
				
				if($_order_status != 'once_canceled'){
					
					$available = get_post_meta( $product_id[$i], 'product_stock', true );
					update_post_meta( $product_id[$i], 'product_stock', (intval($available) + intval($ProductQuantity[$i])) );
					$new[$i]['ProductQuantity'] = '';
					$new[$i]['IcecreamName'] = '';
					$new[$i]['ProductPrice'] = '';
					$new[$i]['AvailableStock'] = '';
					$new[$i]['TotalPrice'] = '';
					
				}
			}
		 
        endif;
    }
    if ( !empty( $new ) && $new != $old ){
		update_post_meta( $post_id, 'customdata_group', $new );
	}elseif ( empty($new) && $old ){
		if($_order_status != 'once_delivered'){
        	delete_post_meta( $post_id, 'customdata_group', $old );
		}
	}
	
	//Update temporary data
	update_post_meta( $post_id, '_subtotal_price', $_POST['subtotal_price'] );
	update_post_meta( $post_id, '_cash_received', $_POST['cash_received'] );
	update_post_meta( $post_id, '_due_amount', $_POST['due_amount'] );
	
	//Calculate Subtotal price after completing the order
	if( $_POST['order_status'] == 'delivered' ){
		
		$get_updated_data = get_post_meta($post_id, 'customdata_group', true);
		$TotalPrice = 0;
		if ( $get_updated_data ) {
			foreach ( $get_updated_data as $field ) {
				if($field['TotalPrice'] != ''){
					$TotalPrice += intval($field['TotalPrice']);
				}
			}
		}
		
		//Update permanant data
		update_post_meta( $post_id, 'subtotal_price', $TotalPrice );
		
		if($_POST['cash_received'] == ''){
			$cash_received = $TotalPrice;
			$due_amount = 0;
		}else{
			$cash_received = $_POST['cash_received'];
			$due_amount = $TotalPrice - intval($_POST['cash_received']);
		}
		update_post_meta( $post_id, 'cash_received', $cash_received );
		update_post_meta( $post_id, 'due_amount', $due_amount );
		
		//delete temporary data
		/*update_post_meta( $post_id, '_subtotal_price', '' );
		update_post_meta( $post_id, '_cash_received', '' );
		update_post_meta( $post_id, '_due_amount', '' );*/
		
	}elseif( $_POST['order_status'] == 'canceled' ){
		
		//Delete permanant data
		update_post_meta( $post_id, 'subtotal_price', 0 );
		update_post_meta( $post_id, 'cash_received', 0 );
		update_post_meta( $post_id, 'due_amount', 0 );
		
		//delete temporary data
		update_post_meta( $post_id, '_subtotal_price', '' );
		update_post_meta( $post_id, '_cash_received', '' );
		update_post_meta( $post_id, '_due_amount', '' );
	}
	
	//Temporary order status update
	if( $_POST['order_status'] == 'delivered' ){
		//Update temporary order status
		update_post_meta( $post_id, '_order_status', 'once_delivered' );

	}elseif( $_POST['order_status'] == 'canceled' ){
		//Update temporary order status
		update_post_meta( $post_id, '_order_status', 'once_canceled' );
	}

}
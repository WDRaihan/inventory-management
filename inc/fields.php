<?php

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

// Save icecream
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

// Add icecream column
function icecream_post_type_columns($columns) {
    $columns['icecream_stock'] = 'Stock'; 
    $columns['icecream_price'] = 'Price'; 
    
    return $columns;
}
add_filter('manage_edit-icecreams_columns', 'icecream_post_type_columns');

// Add data to the custom column
function icecream_post_type_column_data($column, $post_id) {
    if ($column === 'icecream_stock') {
        
        echo get_post_meta($post_id, 'product_stock', true); 
    }
	if ($column === 'icecream_price') {
        
        echo get_post_meta($post_id, 'icecream_price', true); 
    }
}
add_action('manage_icecreams_posts_custom_column', 'icecream_post_type_column_data', 10, 2);


// Add sale column
function sale_post_type_columns($columns) {
    $columns['client_info'] = 'Client'; 
    $columns['order_status'] = 'Status'; 
    $columns['payment_info'] = 'Payment Info'; 
    
    return $columns;
}
add_filter('manage_edit-sale_columns', 'sale_post_type_columns');

// Add data to the custom column
function sale_post_type_column_data($column, $post_id) {
    if ($column === 'client_info') {
        
        echo 'Name: '.get_post_meta($post_id, 'client_name', true).'<br>'; 
        echo 'Number: '.get_post_meta($post_id, 'client_number', true).'<br>'; 
        echo 'Address: '.get_post_meta($post_id, 'client_address', true); 
    }
	if ($column === 'order_status') {
        
        echo get_post_meta($post_id, 'order_status', true); 
    }
	if ($column === 'payment_info') {
        
        echo 'Subtotal: '.get_post_meta($post_id, 'subtotal_price', true).'<br>'; 
        echo 'Cash: '.get_post_meta($post_id, 'cash_received', true).'<br>'; 
        echo 'Due: '.get_post_meta($post_id, 'due_amount', true); 
    }
}
add_action('manage_sale_posts_custom_column', 'sale_post_type_column_data', 10, 2);


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

<?php if($order_status == 'delivered'): ?>
<p><a href="#" id="generate_pdf" sale-id="<?php echo $post_id; ?>">Print Invoice</a></p>
<?php endif; ?>

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

			if (!product_stock) {
				product_stock = '0';
			}
			
			jQuery(this).parents('tr').find('input[name="ProductQuantity[]"]').attr('max', product_stock);
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
	
	
	jQuery(document).ready(function($) {
		jQuery('#generate_pdf').on('click', function(e) {
			e.preventDefault();
			var saleId = jQuery(this).attr('sale-id');
			
			// Send an AJAX request to generate the PDF
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				data: {
					action: 'generate_pdf',
					saleId: saleId
				},
				success: function(response) {
					// Handle the PDF generation success or failure
					var data = JSON.parse(response);
					if (data.success) {
						window.open(data.pdf_url, '_blank');
					} else {
						alert('PDF generation failed.');
					}
				}
			});
		});
	});
</script>
<?php

}
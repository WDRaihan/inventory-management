<?php

add_action('save_post', 'custom_repeatable_meta_box_save');
function custom_repeatable_meta_box_save($post_id) {
    if ( ! isset( $_POST['gpm_repeatable_meta_box_nonce'] ) ||
    ! wp_verify_nonce( $_POST['gpm_repeatable_meta_box_nonce'], 'gpm_repeatable_meta_box_nonce' ) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!current_user_can('edit_post', $post_id)) return;
	
	//Update order status
	update_post_meta( $post_id, 'order_status', $_POST['order_status'] );
	//Update client info
	update_post_meta( $post_id, 'client_name', $_POST['client_name'] );
	update_post_meta( $post_id, 'client_number', $_POST['client_number'] );
	update_post_meta( $post_id, 'client_address', $_POST['client_address'] );
	
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
		update_post_meta( $post_id, 'generate_invoice_pdf', 'ok' );

	}elseif( $_POST['order_status'] == 'canceled' ){
		//Update temporary order status
		update_post_meta( $post_id, '_order_status', 'once_canceled' );
	}

}
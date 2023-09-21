<?php

//Sales widget output
function sales_dashboard_widget() {
    // Widget content
    ?>
    <div class="sales-dashboard-widget">
        <h2>Get sales by date</h2>
        <form method="post">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo date('Y-m-d'); ?>" required>
            <input type="submit" name="calculate_subtotal" value="Get sales">
        </form>
        <?php
        if (isset($_POST['calculate_subtotal'])) {
            $start_date = sanitize_text_field($_POST['start_date']);
			$end_date = sanitize_text_field($_POST['end_date']);
			
			//Get sales by date
			$subtotal = wd_get_sales_by_date($start_date, $end_date);
			
			if ($subtotal > 0) {
				echo '<p><strong>Total Sales ('.$start_date.' to '.$end_date.'): ' . number_format($subtotal, 2) . '</strong></p>';
			} else {
				echo '<p>No data found for the selected date range.</p>';
			}
        }else{
			$subtotal = wd_get_sales_by_date(date('Y-m-d'), date('Y-m-d'));
			echo '<p><strong>Sales Today: ' . number_format($subtotal, 2) . '</strong></p>';
			
			$subtotal = wd_get_sales_by_date();
			echo '<p><strong>Total Sales: ' . number_format($subtotal, 2) . '</strong></p>';
		}
        ?>
    </div>
    <?php
}

//Cash widget output
function cash_dashboard_widget(){
	// Widget content
    ?>
    <div class="cash-dashboard-widget">
        <h2>Get cash by date</h2>
        <form method="post">
            <label for="cash_start_date">Start Date:</label>
            <input type="date" id="cash_start_date" name="cash_start_date" required>
            <label for="cash_end_date">End Date:</label>
            <input type="date" id="cash_end_date" name="cash_end_date" value="<?php echo date('Y-m-d'); ?>" required>
            <input type="submit" name="calculate_cash" value="Get Cash">
        </form>
        <?php
        if (isset($_POST['calculate_cash'])) {
            $start_date = sanitize_text_field($_POST['cash_start_date']);
			$end_date = sanitize_text_field($_POST['cash_end_date']);
			
			//Get cash by date
			$subtotal = wd_get_cash_by_date($start_date, $end_date);
			
			if ($subtotal > 0) {
				echo '<p><strong>Total Cash ('.$start_date.' to '.$end_date.'): ' . number_format($subtotal, 2) . '</strong></p>';
			} else {
				echo '<p>No data found for the selected date range.</p>';
			}
        }else{
			$subtotal = wd_get_cash_by_date(date('Y-m-d'), date('Y-m-d'));
			echo '<p><strong>Cash Today: ' . number_format($subtotal, 2) . '</strong></p>';
			
			$subtotal = wd_get_cash_by_date();
			echo '<p><strong>Total Cash: ' . number_format($subtotal, 2) . '</strong></p>';
		}
        ?>
    </div>
    <?php
}

//Due widget output
function due_dashboard_widget(){
	// Widget content
    ?>
    <div class="due-dashboard-widget">
        <h2>Get due by date</h2>
        <form method="post">
            <label for="due_start_date">Start Date:</label>
            <input type="date" id="due_start_date" name="due_start_date" required>
            <label for="due_end_date">End Date:</label>
            <input type="date" id="due_end_date" name="due_end_date" value="<?php echo date('Y-m-d'); ?>" required>
            <input type="submit" name="calculate_due" value="Get Due">
        </form>
        <?php
        if (isset($_POST['calculate_due'])) {
            $start_date = sanitize_text_field($_POST['due_start_date']);
			$end_date = sanitize_text_field($_POST['due_end_date']);
			
			//Get cash by date
			$subtotal = wd_get_due_by_date($start_date, $end_date);
			
			if ($subtotal > 0) {
				echo '<p><strong>Total Due ('.$start_date.' to '.$end_date.'): ' . number_format($subtotal, 2) . '</strong></p>';
			} else {
				echo '<p>No data found for the selected date range.</p>';
			}
        }else{
			$subtotal = wd_get_due_by_date(date('Y-m-d'), date('Y-m-d'));
			echo '<p><strong>Due Today: ' . number_format($subtotal, 2) . '</strong></p>';
			
			$subtotal = wd_get_due_by_date();
			echo '<p><strong>Total Due: ' . number_format($subtotal, 2) . '</strong></p>';
		}
        ?>
    </div>
    <?php
}

//Add dashboard widget
function wd_add_custom_dashboard_widgets() {
    wp_add_dashboard_widget('sales_dashboard_widget', 'Sales results', 'sales_dashboard_widget');
    wp_add_dashboard_widget('cash_dashboard_widget', 'Cash results', 'cash_dashboard_widget');
    wp_add_dashboard_widget('due_dashboard_widget', 'Due results', 'due_dashboard_widget');
}
add_action('wp_dashboard_setup', 'wd_add_custom_dashboard_widgets');


//Get sales by date function
function wd_get_sales_by_date($start_date = '', $end_date = ''){
	
	if(empty($start_date) && empty($end_date)){
		$start_date = '2023-01-01';
		$end_date = date('Y-m-d');;
	}

	$start_timestamp = strtotime($start_date);
	$end_timestamp = strtotime($end_date);
	
	$post_type = 'sale';

	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'date_query' => array(
			'after' => date('Y-m-d H:i:s', $start_timestamp),
			'before' => date('Y-m-d H:i:s', $end_timestamp),
			'inclusive' => true,
		),
		'meta_query' => array(
			array(
				'key' => 'order_status',
				'value' => 'delivered',
			),
		),
	);

	$query = new WP_Query($args);

	// Calculate the subtotal price based on retrieved posts
	$subtotal = 0;
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			// Get the subtotal price from post meta
			$price = get_post_meta(get_the_ID(), 'subtotal_price', true);
			if (!empty($price)) {
				$subtotal += floatval($price);
			}
		}
	}

	// Reset post data
	wp_reset_postdata();

	// Output the subtotal
	return $subtotal;
}

//Get cash by date function
function wd_get_cash_by_date($start_date = '', $end_date = ''){
	
	if(empty($start_date) && empty($end_date)){
		$start_date = '2023-01-01';
		$end_date = date('Y-m-d');;
	}

	$start_timestamp = strtotime($start_date);
	$end_timestamp = strtotime($end_date);
	
	$post_type = 'sale';

	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'date_query' => array(
			'after' => date('Y-m-d H:i:s', $start_timestamp),
			'before' => date('Y-m-d H:i:s', $end_timestamp),
			'inclusive' => true,
		),
		'meta_query' => array(
			array(
				'key' => 'order_status',
				'value' => 'delivered',
			),
		),
	);

	$query = new WP_Query($args);

	$subtotal = 0;
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			
			$price = get_post_meta(get_the_ID(), 'cash_received', true);
			if (!empty($price)) {
				$subtotal += floatval($price);
			}
		}
	}

	// Reset post data
	wp_reset_postdata();

	// Output the subtotal
	return $subtotal;
}

//Get due by date function
function wd_get_due_by_date($start_date = '', $end_date = ''){
	
	if(empty($start_date) && empty($end_date)){
		$start_date = '2023-01-01';
		$end_date = date('Y-m-d');;
	}

	$start_timestamp = strtotime($start_date);
	$end_timestamp = strtotime($end_date);
	
	$post_type = 'sale';

	$args = array(
		'post_type' => $post_type,
		'post_status' => 'publish',
		'date_query' => array(
			'after' => date('Y-m-d H:i:s', $start_timestamp),
			'before' => date('Y-m-d H:i:s', $end_timestamp),
			'inclusive' => true,
		),
		'meta_query' => array(
			array(
				'key' => 'order_status',
				'value' => 'delivered',
			),
		),
	);

	$query = new WP_Query($args);

	$subtotal = 0;
	if ($query->have_posts()) {
		while ($query->have_posts()) {
			$query->the_post();
			
			$price = get_post_meta(get_the_ID(), 'due_amount', true);
			if (!empty($price)) {
				$subtotal += floatval($price);
			}
		}
	}

	// Reset post data
	wp_reset_postdata();

	// Output the subtotal
	return $subtotal;
}
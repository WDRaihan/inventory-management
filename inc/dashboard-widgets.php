<?php
function custom_dashboard_widget() {
    // Widget content goes here
    ?>
    <div class="custom-dashboard-widget">
        <h2>Subtotal Price Calculator</h2>
        <form method="post">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
            <input type="submit" name="calculate_subtotal" value="Calculate Subtotal">
        </form>
        <?php
        if (isset($_POST['calculate_subtotal'])) {
            $start_date = sanitize_text_field($_POST['start_date']);
            $end_date = sanitize_text_field($_POST['end_date']);
            
			$start_timestamp = strtotime($start_date);
            $end_timestamp = strtotime($end_date);
            // Define your custom post type or specify the post type you want to query
            $post_type = 'sale';

            // Create a WP_Query instance to retrieve posts
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
                    // Get the subtotal price from post meta (replace 'subtotal_price' with your meta key)
                    $price = get_post_meta(get_the_ID(), 'subtotal_price', true);
                    if (!empty($price)) {
                        $subtotal += floatval($price);
                    }
                }
            }

            // Reset post data
            wp_reset_postdata();

            // Output the subtotal
            if ($subtotal > 0) {
                echo '<p>Subtotal Price:' . number_format($subtotal, 2) . '</p>';
            } else {
                echo '<p>No data found for the selected date range.</p>';
            }
        }
        ?>
    </div>
    <?php
}

function add_custom_dashboard_widget() {
    wp_add_dashboard_widget('custom_dashboard_widget', 'Subtotal Price Calculator', 'custom_dashboard_widget');
}
add_action('wp_dashboard_setup', 'add_custom_dashboard_widget');

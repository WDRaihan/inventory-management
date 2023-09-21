<?php
/**
 * Plugin Name:       Icecream er Hisab Nikash
 * Version:           1.0.0
 * Author:            MD Raihan
 * Author URI: me.wdraihan.com
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function enqueue_ajax_pdf_script() {
    wp_enqueue_script('icecream-scripts', plugin_dir_url(__FILE__) . 'assets/scripts.js', array('jquery'), null, true);
    wp_enqueue_style('icecream-style', plugin_dir_url(__FILE__) . 'assets/styles.css');
}
add_action('admin_enqueue_scripts', 'enqueue_ajax_pdf_script');

require_once "inc/dashboard-widgets.php";
require_once('inc/pages.php');
require_once('inc/fields.php');
require_once('inc/save-fields.php');

function custom_unregister_post_types() {
    unregister_post_type('page');
    unregister_post_type('post');
}
add_action('init', 'custom_unregister_post_types');

//Invoice
add_action('wp_ajax_nopriv_generate_pdf', 'generate_invoice_pdf');
add_action('wp_ajax_generate_pdf', 'generate_invoice_pdf');
function generate_invoice_pdf() {
    $post_id = $_POST['saleId'];
    //$post_id = $_GET['post'];
	
	// Load TCPDF library
	require_once('invoice/tcpdf.php');

	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Raihan');
	$pdf->SetTitle('Sales Invoice');
	$pdf->SetSubject('Sales Invoice');
	//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	/*if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		require_once(dirname(__FILE__).'/lang/eng.php');
		$pdf->setLanguageArray($l);
	}*/

	// set font
	//$pdf->SetFont('times', 'BI', 20);

	// add a page
	$pdf->AddPage();

	$html = invoice_html($post_id);

	// output the HTML content
	$pdf->writeHTML($html, true, false, true, false, '');
	
	// Determine the upload directory
    $upload_dir = wp_upload_dir();
    $pdf_dir = trailingslashit($upload_dir['basedir']) . 'invoices';

    // Create the PDF directory if it doesn't exist
    if (!file_exists($pdf_dir)) {
        mkdir($pdf_dir, 0755);
    }

    // Save the PDF to the server
    //$pdf_file = trailingslashit($pdf_dir) . 'invoice-'.$post_id.'.pdf';
    $pdf_file = trailingslashit($pdf_dir) . 'invoice.pdf';
    $pdf->Output($pdf_file, 'F');
	
	$pdf_file = trailingslashit(site_url()).'wp-content/uploads/invoices/invoice.pdf';
    // Provide a response to the client
    echo json_encode(array('success' => true, 'pdf_url' => $pdf_file));

    // Important: Terminate the script
    exit;
	
}

function invoice_html($post_id){

    $gpminvoice_group = get_post_meta($post_id, 'customdata_group', true);
	ob_start();
?>
<table width="100%" style="text-align: left;margin:0 auto">
	<tbody>
		<tr>
			<th style="text-align: right;">Client Name: </th>
			<th><?php echo get_post_meta( $post_id, 'client_name', true ); ?></th>
		</tr>
		<tr>
			<th style="text-align: right;">Client Number: </th>
			<th><?php echo get_post_meta( $post_id, 'client_number', true ); ?></th>
		</tr>
		<tr>
			<th style="text-align: right;">Client Address: </th>
			<th><?php echo get_post_meta( $post_id, 'client_address', true ); ?></th>
		</tr>
	</tbody>
</table>
<br>
<hr>
<br>
<table width="100%" border="1" style="text-align: center">
	<tbody>
		<tr>
			<th style="margin:5px">
				<strong>Product Name</strong>
			</th>
			<th style="padding:5px">
				<strong>Price</strong>
			</th>
			<th style="padding:5px">
				<strong>Quantity</strong>
			</th>
			<th style="padding:5px">
				<strong>Total</strong>
			</th>
		</tr>
		<?php
     if ( $gpminvoice_group ) :
      foreach ( $gpminvoice_group as $field ) {
    ?>
		<tr>
			<td>
				<?php echo get_the_title($field['IcecreamName']); ?>
			</td>
			<td>
				<?php echo $field['ProductPrice']; ?>
			</td>
			<td>
				<?php echo $field['ProductQuantity']; ?>
			</td>
			<td>
				<?php echo $field['TotalPrice']; ?>
			</td>
		</tr>
		<?php
    }
	endif;
	?>
	
	<tr>
		<td>

		</td>
		<td>

		</td>
		<td>

		</td>
		<td>

		</td>
	</tr>
	<tr>
		<td>

		</td>
		<td>

		</td>
		<td style="text-align: right;">
			Sub Total:
		</td>
		<td>
			<?php echo get_post_meta( $post_id, 'subtotal_price', true ); ?>
		</td>
	</tr>
	<tr>
		<td>

		</td>
		<td>

		</td>
		<td style="text-align: right;">
			Cash:
		</td>
		<td>
			<?php echo get_post_meta( $post_id, 'cash_received', true ); ?>
		</td>
	</tr>
	<tr>
		<td>

		</td>
		<td>

		</td>
		<td style="text-align: right;">
			Due:
		</td>
		<td>
			<?php echo get_post_meta( $post_id, 'due_amount', true ); ?>
		</td>
	</tr>
	</tbody>
</table>
<?php
return ob_get_clean();
}
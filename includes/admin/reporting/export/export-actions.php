<?php
/**
 * Exports Actions
 *
 * These are actions related to exporting data from CommerceStore.
 *
 * @package     CS
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2018, CommerceStore, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Process the download file generated by a batch export.
 *
 * @since 2.4
 */
function cs_process_batch_export_download() {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'cs-batch-export' ) ) {
		wp_die( esc_html__( 'Nonce verification failed', 'commercestore' ), esc_html__( 'Error', 'commercestore' ), array( 'response' => 403 ) );
	}

	require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';
	do_action( 'cs_batch_export_class_include', $_REQUEST['class'] );

	if ( class_exists( $_REQUEST['class'] ) && 'CS_Batch_Export' === get_parent_class( $_REQUEST['class'] ) ) {
		$export = new $_REQUEST['class']();
		$export->export();
	}
}
add_action( 'cs_download_batch_export', 'cs_process_batch_export_download' );

/**
 * Export all the customers to a CSV file.
 *
 * Note: The WordPress Database API is being used directly for performance
 * reasons (workaround of calling all posts and fetch data respectively)
 *
 * @since 1.4.4
 * @return void
 */
function cs_export_all_customers() {
	require_once CS_PLUGIN_DIR . 'includes/admin/reporting/class-export-customers.php';

	$customer_export = new CS_Customers_Export();

	$customer_export->export();
}
add_action( 'cs_email_export', 'cs_export_all_customers' );

/**
 * Exports all the downloads to a CSV file using the CS_Export class.
 *
 * @since 1.4.4
 * @return void
 */
function cs_export_all_downloads_history() {
	require_once CS_PLUGIN_DIR . 'includes/admin/reporting/class-export-download-history.php';

	$file_download_export = new CS_Download_History_Export();

	$file_download_export->export();
}
add_action( 'cs_downloads_history_export', 'cs_export_all_downloads_history' );

/**
 * Add a hook allowing extensions to register a hook on the batch export process
 *
 * @since  2.4.2
 * @return void
 */
function cs_register_batch_exporters() {
	if ( is_admin() ) {
		do_action( 'cs_register_batch_exporter' );
	}
}
add_action( 'plugins_loaded', 'cs_register_batch_exporters', 99 );

/**
 * Register the payments batch exporter
 * @since  2.4.2
 */
function cs_register_payments_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_payments_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_payments_batch_export', 10 );

/**
 * Loads the payments batch processor if needed.
 *
 * @since 2.4.2
 *
 * @param string $class The class being requested to run for the batch export
 */
function cs_include_payments_batch_processor( $class ) {
	if ( 'CS_Batch_Payments_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-payments.php';
	}
}

/**
 * Register the customers batch exporter.
 *
 * @since 2.4.2
 */
function cs_register_customers_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_customers_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_customers_batch_export', 10 );

/**
 * Loads the customers batch processor if needed.
 *
 * @since 2.4.2
 *
 * @param string $class The class being requested to run for the batch export.
 */
function cs_include_customers_batch_processor( $class ) {
	if ( 'CS_Batch_Customers_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-customers.php';
	}
}

/**
 * Register the download products batch exporter
 *
 * @since  2.5
 */
function cs_register_downloads_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_downloads_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_downloads_batch_export', 10 );

/**
 * Loads the file downloads batch process if needed
 *
 * @since  2.5
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function cs_include_downloads_batch_processor( $class ) {
	if ( 'CS_Batch_Downloads_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-downloads.php';
	}
}

/**
 * Register the file downloads batch exporter
 * @since  2.4.2
 */
function cs_register_file_downloads_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_file_downloads_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_file_downloads_batch_export', 10 );

/**
 * Loads the file downloads batch process if needed
 *
 * @since  2.4.2
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function cs_include_file_downloads_batch_processor( $class ) {
	if ( 'CS_Batch_File_Downloads_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-file-downloads.php';
	}
}

/**
 * Register the sales batch exporter.
 *
 * @since 2.7
 */
function cs_register_sales_export_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_sales_export_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_sales_export_batch_export', 10 );

/**
 * Loads the sales export batch process if needed
 *
 * @since  2.7
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function cs_include_sales_export_batch_processor( $class ) {
	if ( 'CS_Batch_Sales_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-sales.php';
	}
}

/**
 * Register the earnings report batch exporter
 *
 * @since  2.7
 */
function cs_register_earnings_report_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_earnings_report_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_earnings_report_batch_export', 10 );

/**
 * Loads the earnings report batch process if needed
 *
 * @since  2.7
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function cs_include_earnings_report_batch_processor( $class ) {
	if ( 'CS_Batch_Earnings_Report_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-earnings-report.php';
	}
}

/**
 * Register the API requests batch exporter
 *
 * @since  2.7
 */
function cs_register_api_requests_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_api_requests_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_api_requests_batch_export', 10 );

/**
 * Loads the API requests batch process if needed
 *
 * @since  2.7
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function cs_include_api_requests_batch_processor( $class ) {
	if ( 'CS_Batch_API_Requests_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-api-requests.php';
	}
}

/**
 * Register the taxed orders report batch exporter.
 *
 * @since 3.0
 */
function cs_register_taxed_orders_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_taxed_orders_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_taxed_orders_batch_export', 10 );

/**
 * Loads the taxed orders report batch process if needed.
 *
 * @since 3.0
 *
 * @param string $class The class being requested to run for the batch export
 */
function cs_include_taxed_orders_batch_processor( $class ) {
	if ( 'CS_Batch_Taxed_Orders_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-taxed-orders.php';
	}
}

/**
 * Register the taxed orders report batch exporter.
 *
 * @since 3.0
 */
function cs_register_taxed_customers_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_taxed_customers_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_taxed_customers_batch_export', 10 );

/**
 * Loads the taxed customers report batch process if needed.
 *
 * @since 3.0
 *
 * @param string $class The class being requested to run for the batch export
 */
function cs_include_taxed_customers_batch_processor( $class ) {
	if ( 'CS_Batch_Taxed_Customers_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-taxed-customers.php';
	}
}

/**
 * Register the sales and earnings report batch exporter.
 *
 * @since 3.0
 */
function cs_register_sales_and_earnings_batch_export() {
	add_action( 'cs_batch_export_class_include', 'cs_include_sales_and_earnings_batch_processor', 10, 1 );
}
add_action( 'cs_register_batch_exporter', 'cs_register_sales_and_earnings_batch_export', 10 );

/**
 * Loads the sales and earnings batch process if needed.
 *
 * @since 3.0
 *
 * @param string $class The class being requested to run for the batch export
 */
function cs_include_sales_and_earnings_batch_processor( $class ) {
	if ( 'CS_Batch_Sales_And_Earnings_Export' === $class ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export-sales-and-earnings.php';
	}
}

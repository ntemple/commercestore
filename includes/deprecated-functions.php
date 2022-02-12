<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     CS
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

use CS\Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a download.
 *
 * @since       1.0
 * @deprecated  1.3.4
 *
 * @param int $download_id ID number of the download to retrieve a log for
 * @param bool $paginate Whether to paginate the results or not
 * @param int $number Number of results to return
 * @param int $offset Number of items to skip
 *
 * @return mixed array|bool
*/
function cs_get_download_sales_log( $download_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$sales_log = get_post_meta( $download_id, '_cs_sales_log', true );

	if ( $sales_log ) {
		$sales_log = array_reverse( $sales_log );
		$log = array();
		$log['number'] = count( $sales_log );
		$log['sales'] = $sales_log;

		if ( $paginate ) {
			$log['sales'] = array_slice( $sales_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get Downloads Of Purchase
 *
 * Retrieves an array of all files purchased.
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param int  $payment_id ID number of the purchase
 * @param null $payment_meta
 * @return bool|mixed
 */
function cs_get_downloads_of_purchase( $payment_id, $payment_meta = null ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.4', 'cs_get_payment_meta_downloads', $backtrace );

	if ( is_null( $payment_meta ) ) {
		$payment_meta = cs_get_payment_meta( $payment_id );
	}

	$downloads = maybe_unserialize( $payment_meta['downloads'] );

	if ( $downloads ) {
		return $downloads;
	}

	return false;
}

/**
 * Get Menu Access Level
 *
 * Returns the access level required to access the downloads menu. Currently not
 * changeable, but here for a future update.
 *
 * @since 1.0
 * @deprecated 1.4.4
 * @return string
*/
function cs_get_menu_access_level() {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.4.4', 'current_user_can(\'manage_shop_settings\')', $backtrace );

	return apply_filters( 'cs_menu_access_level', 'manage_options' );
}



/**
 * Check if only local taxes are enabled meaning users must opt in by using the
 * option set from the CommerceStore Settings.
 *
 * @since 1.3.3
 * @deprecated 1.6
 * @global $cs_options
 * @return bool $local_only
 */
function cs_local_taxes_only() {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	global $cs_options;

	$local_only = isset( $cs_options['tax_condition'] ) && $cs_options['tax_condition'] == 'local';

	return apply_filters( 'cs_local_taxes_only', $local_only );
}

/**
 * Checks if a customer has opted into local taxes
 *
 * @since 1.4.1
 * @deprecated 1.6
 * @uses CS_Session::get()
 * @return bool
 */
function cs_local_tax_opted_in() {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	$opted_in = CS()->session->get( 'cs_local_tax_opt_in' );
	return ! empty( $opted_in );
}

/**
 * Show taxes on individual prices?
 *
 * @since 1.4
 * @deprecated 1.9
 * @global $cs_options
 * @return bool Whether or not to show taxes on prices
 */
function cs_taxes_on_prices() {
	global $cs_options;

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.9', 'no alternatives', $backtrace );

	return apply_filters( 'cs_taxes_on_prices', isset( $cs_options['taxes_on_prices'] ) );
}

/**
 * Show Has Purchased Item Message
 *
 * Prints a notice when user has already purchased the item.
 *
 * @since 1.0
 * @deprecated 1.8
 * @global $user_ID
 */
function cs_show_has_purchased_item_message() {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.8', 'no alternatives', $backtrace );

	global $user_ID, $post;

	if ( !isset( $post->ID ) ) {
		return;
	}

	if ( cs_has_user_purchased( $user_ID, $post->ID ) ) {
		$alert = '<p class="cs_has_purchased">' . __( 'You have already purchased this item, but you may purchase it again.', 'commercestore' ) . '</p>';
		echo apply_filters( 'cs_show_has_purchased_item_message', $alert );
	}
}

/**
 * Flushes the total earning cache when a new payment is created
 *
 * @since 1.2
 * @deprecated 1.8.4
 * @param int $payment Payment ID
 * @param array $payment_data Payment Data
 * @return void
 */
function cs_clear_earnings_cache( $payment, $payment_data ) {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.8.4', 'no alternatives', $backtrace );

	delete_transient( 'cs_total_earnings' );
}
//add_action( 'cs_insert_payment', 'cs_clear_earnings_cache', 10, 2 );

/**
 * Get Cart Amount
 *
 * @since 1.0
 * @deprecated 1.9
 * @param bool $add_taxes Whether to apply taxes (if enabled) (default: true)
 * @param bool $local_override Force the local opt-in param - used for when not reading $_POST (default: false)
 * @return float Total amount
*/
function cs_get_cart_amount( $add_taxes = true, $local_override = false ) {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '1.9', 'cs_get_cart_subtotal() or cs_get_cart_total()', $backtrace );

	$amount = cs_get_cart_subtotal( );
	if ( ! empty( $_POST['cs-discount'] ) || cs_get_cart_discounts() !== false ) {
		// Retrieve the discount stored in cookies
		$discounts = cs_get_cart_discounts();

		// Check for a posted discount
		$posted_discount = isset( $_POST['cs-discount'] ) ? trim( $_POST['cs-discount'] ) : '';

		if ( $posted_discount && ! in_array( $posted_discount, $discounts ) ) {
			// This discount hasn't been applied, so apply it
			$amount = cs_get_discounted_amount( $posted_discount, $amount );
		}

		if ( ! empty( $discounts ) ) {
			// Apply the discounted amount from discounts already applied
			$amount -= cs_get_cart_discounted_amount();
		}
	}

	if ( cs_use_taxes() && cs_is_cart_taxed() && $add_taxes ) {
		$tax = cs_get_cart_tax();
		$amount += $tax;
	}

	if ( $amount < 0 ) {
		$amount = 0.00;
	}

	return apply_filters( 'cs_get_cart_amount', $amount, $add_taxes, $local_override );
}

/**
 * Get Purchase Receipt Template Tags
 *
 * Displays all available template tags for the purchase receipt.
 *
 * @since 1.6
 * @deprecated 1.9
 * @author Daniel J Griffiths
 * @return string $tags
 */
function cs_get_purchase_receipt_template_tags() {
	$tags = __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:','commercestore' ) . '<br/>' .
			'{download_list} - ' . __('A list of download links for each download purchased','commercestore' ) . '<br/>' .
			'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased','commercestore' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','commercestore' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','commercestore' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','commercestore' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','commercestore' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','commercestore' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','commercestore' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','commercestore' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','commercestore' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','commercestore' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','commercestore' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','commercestore' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','commercestore' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','commercestore' ) . '<br/>' .
			'{receipt_link} - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'commercestore' );

	return apply_filters( 'cs_purchase_receipt_template_tags_description', $tags );
}


/**
 * Get Sale Notification Template Tags
 *
 * Displays all available template tags for the sale notification email
 *
 * @since 1.7
 * @deprecated 1.9
 * @author Daniel J Griffiths
 * @return string $tags
 */
function cs_get_sale_notification_template_tags() {
	$tags = __( 'Enter the email that is sent to sale notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'commercestore' ) . '<br/>' .
			'{download_list} - ' . __('A list of download links for each download purchased','commercestore' ) . '<br/>' .
			'{file_urls} - ' . __('A plain-text list of download URLs for each download purchased','commercestore' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','commercestore' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','commercestore' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','commercestore' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','commercestore' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','commercestore' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','commercestore' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','commercestore' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','commercestore' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','commercestore' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','commercestore' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','commercestore' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','commercestore' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','commercestore' );

	return apply_filters( 'cs_sale_notification_template_tags_description', $tags );
}

/**
 * Email Template Header
 *
 * @access private
 * @since 1.0.8.2
 * @deprecated 2.0
 * @return string Email template header
 */
function cs_get_email_body_header() {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	?>
	<html>
	<head>
		<style type="text/css">#outlook a { padding: 0; }</style>
	</head>
	<body dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
	<?php
	do_action( 'cs_email_body_header' );
	return ob_get_clean();
}

/**
 * Email Template Footer
 *
 * @since 1.0.8.2
 * @deprecated 2.0
 * @return string Email template footer
 */
function cs_get_email_body_footer() {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	do_action( 'cs_email_body_footer' );
	?>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Applies the Chosen Email Template
 *
 * @since 1.0.8.2
 * @deprecated 2.0
 * @param string $body The contents of the receipt email
 * @param int $payment_id The ID of the payment we are sending a receipt for
 * @param array $payment_data An array of meta information for the payment
 * @return string $email Formatted email with the template applied
 */
function cs_apply_email_template( $body, $payment_id, $payment_data = array() ) {
	global $cs_options;

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	$template_name = isset( $cs_options['email_template'] ) ? $cs_options['email_template'] : 'default';
	$template_name = apply_filters( 'cs_email_template', $template_name, $payment_id );

	if ( $template_name == 'none' ) {
		if ( is_admin() ) {
			$body = cs_email_preview_template_tags( $body );
		}

		// Return the plain email with no template
		return $body;
	}

	ob_start();

	do_action( 'cs_email_template_' . $template_name );

	$template = ob_get_clean();

	if ( is_admin() ) {
		$body = cs_email_preview_template_tags( $body );
	}

	$body = apply_filters( 'cs_purchase_receipt_' . $template_name, $body );

	$email = str_replace( '{email}', $body, $template );

	return $email;

}

/**
 * Checks if the user has enabled the option to calculate taxes after discounts
 * have been entered
 *
 * @since 1.4.1
 * @deprecated 2.1
 * @global $cs_options
 * @return bool Whether or not taxes are calculated after discount
 */
function cs_taxes_after_discounts() {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.1', 'none', $backtrace );

	global $cs_options;
	$ret = isset( $cs_options['taxes_after_discounts'] ) && cs_use_taxes();
	return apply_filters( 'cs_taxes_after_discounts', $ret );
}

/**
 * Verifies a download purchase using a purchase key and email.
 *
 * @deprecated Please avoid usage of this function in favor of the tokenized urls with cs_validate_url_token()
 * introduced in CommerceStore 2.3
 *
 * @since 1.0
 *
 * @param int    $download_id
 * @param string $key
 * @param string $email
 * @param string $expire
 * @param int    $file_key
 *
 * @return bool True if payment and link was verified, false otherwise
 */
function cs_verify_download_link( $download_id = 0, $key = '', $email = '', $expire = '', $file_key = 0 ) {

	$meta_query = array(
		'relation'  => 'AND',
		array(
			'key'   => '_cs_payment_purchase_key',
			'value' => $key
		),
		array(
			'key'   => '_cs_payment_user_email',
			'value' => $email
		)
	);

	$accepted_stati = apply_filters( 'cs_allowed_download_stati', array( 'publish', 'complete' ) );

	$payments = get_posts( array( 'meta_query' => $meta_query, 'post_type' => 'cs_payment', 'post_status' => $accepted_stati ) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$cart_details = cs_get_payment_meta_cart_details( $payment->ID, true );

			if ( ! empty( $cart_details ) ) {
				foreach ( $cart_details as $cart_key => $cart_item ) {

					if ( $cart_item['id'] != $download_id ) {
						continue;
					}

					$price_options 	= isset( $cart_item['item_number']['options'] ) ? $cart_item['item_number']['options'] : false;
					$price_id 		= isset( $price_options['price_id'] ) ? $price_options['price_id'] : false;

					$file_condition = cs_get_file_price_condition( $cart_item['id'], $file_key );

					// Check to see if the file download limit has been reached
					if ( cs_is_file_at_download_limit( $cart_item['id'], $payment->ID, $file_key, $price_id ) ) {
						wp_die( apply_filters( 'cs_download_limit_reached_text', __( 'Sorry but you have hit your download limit for this file.', 'commercestore' ) ), __( 'Error', 'commercestore' ), array( 'response' => 403 ) );
					}

					// If this download has variable prices, we have to confirm that this file was included in their purchase
					if ( ! empty( $price_options ) && $file_condition != 'all' && cs_has_variable_prices( $cart_item['id'] ) ) {
						if ( $file_condition == $price_options['price_id'] ) {
							return $payment->ID;
						}
					}

					// Make sure the link hasn't expired

					if ( base64_encode( base64_decode( $expire, true ) ) === $expire ) {
						$expire = base64_decode( $expire ); // If it is a base64 string, decode it. Old expiration dates were in base64
					}

					if ( current_time( 'timestamp' ) > $expire ) {
						wp_die( apply_filters( 'cs_download_link_expired_text', __( 'Sorry but your download link has expired.', 'commercestore' ) ), __( 'Error', 'commercestore' ), array( 'response' => 403 ) );
					}
					return $payment->ID; // Payment has been verified and link is still valid
				}
			}
		}

	} else {
		wp_die( __( 'No payments matching your request were found.', 'commercestore' ), __( 'Error', 'commercestore' ), array( 'response' => 403 ) );
	}
	// Payment not verified
	return false;
}

/**
 * Get Success Page URL
 *
 * @param string $query_string
 * @since       1.0
 * @deprecated  2.6 Please avoid usage of this function in favor of cs_get_success_page_uri()
 * @return      string
*/
function cs_get_success_page_url( $query_string = null ) {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.6', 'cs_get_success_page_uri()', $backtrace );

	return apply_filters( 'cs_success_page_url', cs_get_success_page_uri( $query_string ) );
}

/**
 * Reduces earnings and sales stats when a purchase is refunded
 *
 * @since 1.8.2
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @deprecated  2.5.7 Please avoid usage of this function in favor of refund() in CS_Payment
 * @internal param Arguments $data passed
 */
function cs_undo_purchase_on_refund( $payment_id, $new_status, $old_status ) {

	$backtrace = debug_backtrace();
	_cs_deprecated_function( 'cs_undo_purchase_on_refund', '2.5.7', 'CS_Payment->refund()', $backtrace );

	$payment = new CS_Payment( $payment_id );
	$payment->refund();
}

/**
 * Get Earnings By Date
 *
 * @since 1.0
 * @deprecated 2.7
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $earnings Earnings
 */
function cs_get_earnings_by_date( $day, $month_num = null, $year = null, $hour = null, $include_taxes = true ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.7', 'CS_Payment_Stats()->get_earnings()', $backtrace );

	global $wpdb;

	$args = array(
		'post_type'      => 'cs_payment',
		'nopaging'       => true,
		'year'           => $year,
		'monthnum'       => $month_num,
		'post_status'    => array( 'publish', 'revoked' ),
		'fields'         => 'ids',
		'include_taxes'  => $include_taxes,
		'update_post_term_cache' => false,
	);

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) || $hour == 0 ) {
		$args['hour'] = $hour;
	}

	$args   = apply_filters( 'cs_get_earnings_by_date_args', $args );
	$cached = get_transient( 'cs_stats_earnings' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = get_posts( $args );
		$earnings = 0;
		if ( $sales ) {
			$sales = implode( ',', $sales );

			$total_earnings = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_cs_payment_total' AND post_id IN ({$sales})" );
			$total_tax      = 0;

			if ( ! $include_taxes ) {
				$total_tax = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_cs_payment_tax' AND post_id IN ({$sales})" );
			}

			$earnings += ( $total_earnings - $total_tax );
		}
		// Cache the results for one hour
		$cached[ $key ] = $earnings;
		set_transient( 'cs_stats_earnings', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return round( $result, 2 );
}

/**
 * Get Sales By Date
 *
 * @since 1.1.4.0
 * @deprecated 2.7
 * @author Sunny Ratilal
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Sales
 */
function cs_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.7', 'CS_Payment_Stats()->get_sales()', $backtrace );

	$args = array(
		'post_type'      => 'cs_payment',
		'nopaging'       => true,
		'year'           => $year,
		'fields'         => 'ids',
		'post_status'    => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	);

	$show_free = apply_filters( 'cs_sales_by_date_show_free', true, $args );

	if ( false === $show_free ) {
		$args['meta_query'] = array(
			array(
				'key' => '_cs_payment_total',
				'value' => 0,
				'compare' => '>',
				'type' => 'NUMERIC',
			),
		);
	}

	if ( ! empty( $month_num ) ) {
		$args['monthnum'] = $month_num;
	}

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'cs_get_sales_by_date_args', $args  );

	$cached = get_transient( 'cs_stats_sales' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;

		// Cache the results for one hour
		$cached[ $key ] = $count;
		set_transient( 'cs_stats_sales', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return $result;
}

/**
 * Set the Page Style for PayPal Purchase page
 *
 * @since 1.4.1
 * @deprecated 2.8
 * @return string
 */
function cs_get_paypal_page_style() {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '2.8', 'cs_get_paypal_image_url', $backtrace );

	$page_style = trim( cs_get_option( 'paypal_page_style', 'PayPal' ) );
	return apply_filters( 'cs_paypal_page_style', $page_style );
}

/**
 * Should we add schema.org microdata?
 *
 * @since 1.7
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/commercestore/commercestore/issues/5240
 *
 * @return bool
 */
function cs_add_schema_microdata() {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'CS_Structured_Data', $backtrace );

	// Don't modify anything until after wp_head() is called
	$ret = (bool)did_action( 'wp_head' );
	return apply_filters( 'cs_add_schema_microdata', $ret );
}

/**
 * Add Microdata to download titles
 *
 * @since 1.5
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/commercestore/commercestore/issues/5240
 *
 * @param string $title Post Title
 * @param int $id Post ID
 * @return string $title New title
 */
function cs_microdata_title( $title, $id = 0 ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'CS_Structured_Data', $backtrace );

	global $post;

	if ( ! cs_add_schema_microdata() || ! is_object( $post ) ) {
		return $title;
	}

	if ( $post->ID == $id && is_singular( 'download' ) && 'download' == get_post_type( intval( $id ) ) ) {
		$title = '<span itemprop="name">' . $title . '</span>';
	}

	return $title;
}

/**
 * Start Microdata to wrapper download
 *
 * @since 2.3
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/commercestore/commercestore/issues/5240
 *
 * @return void
 */
function cs_microdata_wrapper_open( $query ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'CS_Structured_Data', $backtrace );

	static $microdata_open = NULL;

	if ( ! cs_add_schema_microdata() || true === $microdata_open || ! is_object( $query ) ) {
		return;
	}

	if ( $query && ! empty( $query->query['post_type'] ) && $query->query['post_type'] == 'download' && is_singular( 'download' ) && $query->is_main_query() ) {
		$microdata_open = true;
		echo '<div itemscope itemtype="http://schema.org/Product">';
	}
}

/**
 * End Microdata to wrapper download
 *
 * @since 2.3
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/commercestore/commercestore/issues/5240
 *
 * @return void
 */
function cs_microdata_wrapper_close() {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'CS_Structured_Data', $backtrace );

	global $post;

	static $microdata_close = NULL;

	if ( ! cs_add_schema_microdata() || true === $microdata_close || ! is_object( $post ) ) {
		return;
	}

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		$microdata_close = true;
		echo '</div>';
	}
}

/**
 * Add Microdata to download description
 *
 * @since 1.5
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/commercestore/commercestore/issues/5240
 *
 * @param $content
 * @return mixed|void New title
 */
function cs_microdata_description( $content ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'CS_Structured_Data', $backtrace );

	global $post;

	static $microdata_description = NULL;

	if ( ! cs_add_schema_microdata() || true === $microdata_description || ! is_object( $post ) ) {
		return $content;
	}

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		$microdata_description = true;
		$content = apply_filters( 'cs_microdata_wrapper', '<div itemprop="description">' . $content . '</div>' );
	}
	return $content;
}

/**
 * Output schema markup for single price products.
 *
 * @since  2.6.14
 * @since 3.0 - Deprecated as the switch was made to JSON-LD.
 * @see https://github.com/commercestore/commercestore/issues/5240
 *
 * @param  int $download_id The download being output.
 * @return void
 */
function cs_purchase_link_single_pricing_schema( $download_id = 0, $args = array() ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'CS_Structured_Data', $backtrace );

	// Bail if the product has variable pricing, or if we aren't showing schema data.
	if ( cs_has_variable_prices( $download_id ) || ! cs_add_schema_microdata() ) {
		return;
	}

	// Grab the information we need.
	$download = new CS_Download( $download_id );
	?>
    <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<meta itemprop="price" content="<?php echo esc_attr( $download->price ); ?>" />
		<meta itemprop="priceCurrency" content="<?php echo esc_attr( cs_get_currency() ); ?>" />
	</span>
	<?php
}

/**
 * Renders the Logs tab in the Reports screen.
 *
 * @since 1.3
 * @deprecated 3.0 Use cs_tools_tab_logs() instead.
 * @see cs_tools_tab_logs()
 * @return void
 */
function cs_reports_tab_logs() {
	_cs_deprecated_function( __FUNCTION__, '3.0', 'cs_tools_tab_logs' );

	if ( ! function_exists( 'cs_tools_tab_logs' ) ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/tools/logs.php';
	}

	cs_tools_tab_logs();
}

/**
 * Defines views for the legacy 'Reports' tab.
 *
 * @since 1.4
 * @deprecated 3.0 Use \CS\Reports\get_reports()
 * @see \CS\Reports\get_reports()
 *
 * @return array $views Report Views
 */
function cs_reports_default_views() {
	_cs_deprecated_function( __FUNCTION__, '3.0', '\CS\Reports\get_reports' );

	return Reports\get_reports();
}

/**
 * Renders the Reports page
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 */
function cs_reports_tab_reports() {

	_cs_deprecated_function( __FUNCTION__, '3.0' );

	if ( ! current_user_can( 'view_shop_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'commercestore' ), __( 'Error', 'commercestore' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = cs_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) ) {
		$current_view = $_GET['view'];
	}

	/**
	 * Legacy: fired inside the old global 'Reports' tab.
	 *
	 * The dynamic portion of the hook name, `$current_view`, represented the parsed value of
	 * the 'view' query variable.
	 *
	 * @since 1.3
	 * @deprecated 3.0 Unused.
	 */
	cs_do_action_deprecated( 'cs_reports_view_' . $current_view, array(), '3.0' );

}

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since 1.9.6
 * @deprecated 3.0 Unused.
 *
 * @return string $view Report View
 */
function cs_get_reporting_view( $default = 'earnings' ) {

	_cs_deprecated_function( __FUNCTION__, '3.0' );

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( cs_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	/**
	 * Legacy: filters the current reporting view (now implemented solely via the 'tab' var).
	 *
	 * @since 1.9.6
	 * @deprecated 3.0 Unused.
	 *
	 * @param string $view View slug.
	 */
	return cs_apply_filters_deprecated( 'cs_get_reporting_view', array( $view ), '3.0' );
}

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 *
 * @return void
 */
function cs_report_views() {

	_cs_deprecated_function( __FUNCTION__, '3.0' );

	/**
	 * Legacy: fired before the view actions drop-down was output.
	 *
	 * @since 1.3
	 * @deprecated 3.0 Unused.
	 */
	cs_do_action_deprecated( 'cs_report_view_actions', array(), '3.0' );

	/**
	 * Legacy: fired after the view actions drop-down was output.
	 *
	 * @since 1.3
	 * @deprecated 3.0 Unused.
	 */
	cs_do_action_deprecated( 'cs_report_view_actions_after', array(), '3.0' );

	return;
}

/**
 * Show report graph date filters.
 *
 * @since 1.3
 * @deprecated 3.0 Unused.
 */
function cs_reports_graph_controls() {
	_cs_deprecated_function( __FUNCTION__, 'CS 3.0' );
}

/**
 * Sets up the dates used to filter graph data
 *
 * Date sent via $_GET is read first and then modified (if needed) to match the
 * selected date-range (if any)
 *
 * @since 1.3
 * @deprecated 3.0 Use \CS\Reports\get_dates_filter() instead
 * @see \CS\Reports\get_dates_filter()
 *
 * @param string $timezone Optional. Timezone to force for report filter dates calculations.
 *                         Default is the WP timezone.
 * @return array Array of report filter dates.
 */
function cs_get_report_dates( $timezone = null ) {

	_cs_deprecated_function( __FUNCTION__, '3.0', '\CS\Reports\get_dates_filter' );

	Reports\Init::bootstrap();

	add_filter( 'cs_get_dates_filter_range', '\CS\Reports\compat_filter_date_range' );

	$filter_dates = Reports\get_dates_filter( 'objects', $timezone );
	$range        = Reports\get_dates_filter_range();

	remove_filter( 'cs_get_report_dates_default_range', '\CS\Reports\compat_filter_date_range' );

	$dates = array(
		'range'    => $range,
		'day'      => $filter_dates['start']->format( 'd' ),
		'day_end'  => $filter_dates['end']->format( 'd' ),
		'm_start'  => $filter_dates['start']->month,
		'm_end'    => $filter_dates['end']->month,
		'year'     => $filter_dates['start']->year,
		'year_end' => $filter_dates['end']->year,
	);

	/**
	 * Filters the legacy list of parsed report dates for use in the Reports API.
	 *
	 * @since 1.3
	 * @deprecated 3.0
	 *
	 * @param array $dates Array of legacy date parts.
	 */
	return cs_apply_filters_deprecated( 'cs_report_dates', array( $dates ), '3.0' );
}

/**
 * Intercept default Edit post links for CommerceStore orders and rewrite them to the View Order Details screen.
 *
 * @since 1.8.3
 * @deprecated 3.0 No alternative present as get_post() does not work with orders.
 *
 * @param $url
 * @param $post_id
 * @param $context
 *
 * @return string
 */
function cs_override_edit_post_for_payment_link( $url = '', $post_id = 0, $context = '') {
	_cs_deprecated_function( __FUNCTION__, '3.0', '' );

	$post = get_post( $post_id );

	if ( empty( $post ) ) {
		return $url;
	}

	if ( 'cs_payment' !== $post->post_type ) {
		return $url;
	}

	return cs_get_admin_url( array(
		'page' => 'cs-payment-history',
		'view' => 'view-order-details',
		'id'   => $post_id
	) );
}

/**
 * Record sale as a log.
 *
 * Stores log information for a download sale.
 *
 * @since 1.0
 * @deprecated 3.0 Sales logs are no longed stored.
 *
 * @param int    $download_id Download ID
 * @param int    $payment_id  Payment ID.
 * @param int    $price_id    Optional. Price ID.
 * @param string $sale_date   Optional. Date of the sale.
 */
function cs_record_sale_in_log( $download_id, $payment_id, $price_id = false, $sale_date = null ) {
	_cs_deprecated_function( __FUNCTION__, '3.0' );

	$cs_logs = CS()->debug_log;

	$log_data = array(
		'post_parent'   => $download_id,
		'log_type'      => 'sale',
		'post_date'     => ! empty( $sale_date ) ? $sale_date : null,
		'post_date_gmt' => ! empty( $sale_date ) ? get_gmt_from_date( $sale_date ) : null,
	);

	$log_meta = array(
		'payment_id' => $payment_id,
		'price_id'   => (int) $price_id,
	);

	$cs_logs->insert_log( $log_data, $log_meta );
}

/**
 * Outputs the JavaScript code for the Agree to Terms section to toggle
 * the T&Cs text
 *
 * @since 1.0
 * @deprecated 3.0 Moved to external scripts in assets/js/frontend/checkout/components/agree-to-terms
 */
function cs_agree_to_terms_js() {
	_cs_deprecated_function( __FUNCTION__, '3.0' );
}

/**
 * Record payment status change
 *
 * @since 1.4.3
 * @deprecated since 3.0
 * @param int    $payment_id the ID number of the payment.
 * @param string $new_status the status of the payment, probably "publish".
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending".
 * @return void
 */
function cs_record_status_change( $payment_id, $new_status, $old_status ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'cs_record_order_status_change', $backtrace );

	// Get the list of statuses so that status in the payment note can be translated
	$stati      = cs_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf( __( 'Status changed from %s to %s', 'commercestore' ), $old_status, $new_status );

	cs_insert_payment_note( $payment_id, $status_change );
}

/**
 * Shows checkbox to automatically refund payments made in PayPal.
 *
 * @deprecated 3.0 In favour of `cs_paypal_refund_checkbox()`
 * @see cs_paypal_refund_checkbox()
 *
 * @since  2.6.0
 *
 * @param int $payment_id The current payment ID.
 * @return void
 */
function cs_paypal_refund_admin_js( $payment_id = 0 ) {

	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'cs_paypal_refund_checkbox', $backtrace );

	// If not the proper gateway, return early.
	if ( 'paypal' !== cs_get_payment_gateway( $payment_id ) ) {
		return;
	}

	// If our credentials are not set, return early.
	$key       = cs_get_payment_meta( $payment_id, '_cs_payment_mode', true );
	$username  = cs_get_option( 'paypal_' . $key . '_api_username' );
	$password  = cs_get_option( 'paypal_' . $key . '_api_password' );
	$signature = cs_get_option( 'paypal_' . $key . '_api_signature' );

	if ( empty( $username ) || empty( $password ) || empty( $signature ) ) {
		return;
	}

	// Localize the refund checkbox label.
	$label = __( 'Refund Payment in PayPal', 'commercestore' );

	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('select[name=cs-payment-status]').change(function() {
				if ( 'refunded' === $(this).val() ) {
					$(this).parent().parent().append('<input type="checkbox" id="cs-paypal-refund" name="cs-paypal-refund" value="1" style="margin-top:0">');
					$(this).parent().parent().append('<label for="cs-paypal-refund"><?php echo $label; ?></label>');
				} else {
					$('#cs-paypal-refund').remove();
					$('label[for="cs-paypal-refund"]').remove();
				}
			});
		});
	</script>
	<?php
}

/**
 * Possibly refunds a payment made with PayPal Standard or PayPal Express.
 *
 * @deprecated 3.0 In favour of `cs_paypal_maybe_refund_transaction()`
 * @see cs_paypal_maybe_refund_transaction()
 *
 * @since  2.6.0
 *
 * @param object|CS_Payment $payment The current payment ID.
 * @return void
 */
function cs_maybe_refund_paypal_purchase( CS_Payment $payment ) {
	$backtrace = debug_backtrace();

	_cs_deprecated_function( __FUNCTION__, '3.0', 'cs_paypal_maybe_refund_transaction', $backtrace );

	if ( ! current_user_can( 'edit_shop_payments', $payment->ID ) ) {
		return;
	}

	if ( empty( $_POST['cs-paypal-refund'] ) ) {
		return;
	}

	$processed = $payment->get_meta( '_cs_paypal_refunded', true );

	// If the status is not set to "refunded", return early.
	if ( 'complete' !== $payment->old_status && 'revoked' !== $payment->old_status ) {
		return;
	}

	// If not PayPal/PayPal Express, return early.
	if ( 'paypal' !== $payment->gateway ) {
		return;
	}

	// If the payment has already been refunded in the past, return early.
	if ( $processed ) {
		return;
	}

	// Process the refund in PayPal.
	cs_refund_paypal_purchase( $payment );
}

/**
 * Jilt Callback
 *
 * Renders Jilt Settings
 *
 * @deprecated 2.10.2
 *
 * @param array $args arguments passed by the setting.
 * @return void
 */
function cs_jilt_callback( $args ) {

	_cs_deprecated_function( __FUNCTION__, '2.10.2' );

	$activated   = is_callable( 'cs_jilt' );
	$connected   = $activated && cs_jilt()->get_integration()->is_jilt_connected();
	$connect_url = $activated ? cs_jilt()->get_connect_url() : '';
	$account_url = $connected ? cs_jilt()->get_integration()->get_jilt_app_url() : '';

	echo wp_kses_post( $args['desc'] );

	if ( $activated ) :
		?>

		<?php if ( $connected ) : ?>

		<p>
			<button id="cs-jilt-disconnect" class="button"><?php esc_html_e( 'Disconnect Jilt', 'commercestore' ); ?></button>
		</p>

		<p>
			<?php
			wp_kses_post(
				sprintf(
				/* Translators: %1$s - <a> tag, %2$s - </a> tag */
					__( '%1$sClick here%2$s to visit your Jilt dashboard', 'commercestore' ),
					'<a href="' . esc_url( $account_url ) . '" target="_blank">',
					'</a>'
				)
			);
			?>
		</p>

	<?php else : ?>

		<p>
			<a id="cs-jilt-connect" class="button button-primary" href="<?php echo esc_url( $connect_url ); ?>">
				<?php esc_html_e( 'Connect to Jilt', 'commercestore' ); ?>
			</a>
		</p>

	<?php endif; ?>

	<?php elseif( current_user_can( 'install_plugins' ) ) : ?>

		<p>
			<button id="cs-jilt-connect" class="button button-primary">
				<?php esc_html_e( 'Install Jilt', 'commercestore' ); ?>
			</button>
		</p>

	<?php
	endif;
}

/**
 * Handle installation and activation for Jilt via AJAX
 *
 * @deprecated 2.10.2
 * @since n.n.n
 */
function cs_jilt_remote_install_handler() {

	_cs_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! current_user_can( 'manage_shop_settings' ) || ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'commercestore' ),
			)
		);
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if ( ! array_key_exists( 'jilt-for-cs/jilt-for-cs.php', $plugins ) ) {
		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api(
			'plugin_information',
			array(
				'slug' => 'jilt-for-edd',
			)
		);

		if ( is_wp_error( $api ) ) {
			wp_send_json_error(
				array(
					'error' => $api->get_error_message(),
					'debug' => $api,
				)
			);
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install  = $upgrader->install( $api->download_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error(
				array(
					'error' => $install->get_error_message(),
					'debug' => $api,
				)
			);
		}

		activate_plugin( $upgrader->plugin_info() );

	} else {

		activate_plugin( 'jilt-for-cs/jilt-for-cs.php' );
	}

	/*
	* Final check to see if Jilt is available.
	*/
	if ( ! class_exists( 'CS_Jilt_Loader' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. Jilt was not installed correctly.', 'commercestore' ),
			)
		);
	}

	wp_send_json_success();
}

/**
 * Handle connection for Jilt via AJAX
 *
 * @deprecated 2.10.2
 * @since n.n.n
 */
function cs_jilt_connect_handler() {

	_cs_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'commercestore' ),
			)
		);
	}

	if ( ! is_callable( 'cs_jilt' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'Something went wrong. Jilt was not installed correctly.', 'commercestore' ),
			)
		);
	}

	wp_send_json_success( array( 'connect_url' => cs_jilt()->get_connect_url() ) );
}

/**
 * Handle disconnection and deactivation for Jilt via AJAX
 *
 * @deprecated 2.10.2
 * @since n.n.n
 */
function cs_jilt_disconnect_handler() {

	_cs_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error(
			array(
				'error' => __( 'You do not have permission to do this.', 'commercestore' ),
			)
		);
	}

	if ( is_callable( 'cs_jilt' ) ) {

		cs_jilt()->get_integration()->unlink_shop();
		cs_jilt()->get_integration()->revoke_authorization();
		cs_jilt()->get_integration()->clear_connection_data();
	}

	deactivate_plugins( 'jilt-for-cs/jilt-for-cs.php' );

	wp_send_json_success();
}

/**
 * Maybe adds a notice to abandoned payments if Jilt isn't installed.
 *
 * @deprecated 2.10.2
 * @since n.n.n
 *
 * @param int $payment_id The ID of the abandoned payment, for which a jilt notice is being thrown.
 */
function cs_maybe_add_jilt_notice_to_abandoned_payment( $payment_id ) {

	_cs_deprecated_function( __FUNCTION__, '2.10.2' );

	if ( ! is_callable( 'cs_jilt' )
		&& ! is_plugin_active( 'recapture-for-cs/recapture.php' )
		&& 'abandoned' === cs_get_payment_status( $payment_id )
		&& ! get_user_meta( get_current_user_id(), '_cs_try_jilt_dismissed', true )
	) {
		?>
		<div class="notice notice-warning jilt-notice">
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* Translators: %1$s - <strong> tag, %2$s - </strong> tag, %3$s - <a> tag, %4$s - </a> tag */
						__( '%1$sRecover abandoned purchases like this one.%2$s %3$sTry Jilt for free%4$s.', 'commercestore' ),
						'<strong>',
						'</strong>',
						'<a href="https://commercestore.com/downloads/jilt" target="_blank">',
						'</a>'
					)
				);
				?>
			</p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* Translators: %1$s - Opening anchor tag, %2$s - The url to dismiss the ajax notice, %3$s - Complete the opening of the anchor tag, %4$s - Open span tag, %4$s - Close span tag */
					__( '%1$s %2$s %3$s %4$s Dismiss this notice. %5$s', 'commercestore' ),
					'<a href="',
					esc_url(
						add_query_arg(
							array(
								'cs_action' => 'dismiss_notices',
								'cs_notice' => 'try_jilt',
							)
						)
					),
					'" type="button" class="notice-dismiss">',
					'<span class="screen-reader-text">',
					'</span>
				</a>'
				)
			);
			?>
		</div>
		<?php
	}
}

/**
 * SendWP Callback
 *
 * Renders SendWP Settings
 *
 * @since 2.9.15
 * @param array $args Arguments passed by the setting
 * @return void
 */
function cs_sendwp_callback( $args ) {

	_cs_deprecated_function( __FUNCTION__, '2.11.4' );

	// Connection status partial label based on the state of the SendWP email sending setting (Tools -> SendWP)
	$connected  = '<a href="https://app.sendwp.com/dashboard" target="_blank" rel="noopener noreferrer">';
	$connected .= __( 'Access your SendWP account', 'commercestore' );
	$connected .= '</a>.';

	$disconnected = sprintf(
		__( '<em><strong>Note:</strong> Email sending is currently disabled. <a href="' . admin_url( '/tools.php?page=sendwp' ) . '">Click here</a> to enable it.</em>', 'commercestore' )
	);

	// Checks if SendWP is connected
	$client_connected = function_exists( 'sendwp_client_connected' ) && sendwp_client_connected() ? true : false;

	// Checks if email sending is enabled in SendWP
	$forwarding_enabled = function_exists( 'sendwp_forwarding_enabled' ) && sendwp_forwarding_enabled() ? true : false;

	ob_start();

	echo $args['desc'];

	// Output the appropriate button and label based on connection status
	if( $client_connected ) :
		?>
		<div class="inline notice notice-success">
			<p><?php _e( 'SendWP plugin activated.', 'commercestore' ); ?> <?php echo $forwarding_enabled ? $connected : $disconnected ; ?></p>

			<p>
				<button id="cs-sendwp-disconnect" class="button"><?php _e( 'Disconnect SendWP', 'commercestore' ); ?></button>
			</p>
		</div>
		<?php
	else :
		?>
		<p>
			<?php _e( 'We recommend SendWP to ensure quick and reliable delivery of all emails sent from your store, such as purchase receipts, subscription renewal reminders, password resets, and more.', 'commercestore' ); ?> <?php printf( __( '%sLearn more%s', 'commercestore' ), '<a href="https://sendwp.com/" target="_blank" rel="noopener noreferrer">', '</a>' ); ?>
		</p>
		<p>
			<button type="button" id="cs-sendwp-connect" class="button button-primary"><?php esc_html_e( 'Connect with SendWP', 'commercestore' ); ?>
			</button>
		</p>

		<?php
	endif;

	echo ob_get_clean();
}

/**
 * Handle installation and connection for SendWP via ajax
 *
 * @since 2.9.15
 */
function cs_sendwp_remote_install_handler () {

	_cs_deprecated_function( __FUNCTION__, '2.11.4' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'commercestore' )
		) );
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	include_once ABSPATH . 'wp-admin/includes/file.php';
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	$plugins = get_plugins();

	if( ! array_key_exists( 'sendwp/sendwp.php', $plugins ) ) {

		/*
		* Use the WordPress Plugins API to get the plugin download link.
		*/
		$api = plugins_api( 'plugin_information', array(
			'slug' => 'sendwp',
		) );

		if ( is_wp_error( $api ) ) {
			wp_send_json_error( array(
				'error' => $api->get_error_message(),
				'debug' => $api
			) );
		}

		/*
		* Use the AJAX Upgrader skin to quietly install the plugin.
		*/
		$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
		$install = $upgrader->install( $api->download_link );
		if ( is_wp_error( $install ) ) {
			wp_send_json_error( array(
				'error' => $install->get_error_message(),
				'debug' => $api
			) );
		}

		$activated = activate_plugin( $upgrader->plugin_info() );

	} else {

		$activated = activate_plugin( 'sendwp/sendwp.php' );

	}

	/*
	* Final check to see if SendWP is available.
	*/
	if( ! function_exists('sendwp_get_server_url') ) {
		wp_send_json_error( array(
			'error' => __( 'Something went wrong. SendWP was not installed correctly.', 'commercestore' )
		) );
	}

	wp_send_json_success( array(
		'partner_id'      => 81,
		'register_url'    => sendwp_get_server_url() . '_/signup',
		'client_name'     => sendwp_get_client_name(),
		'client_secret'   => sendwp_get_client_secret(),
		'client_redirect' => admin_url( '/edit.php?post_type=download&page=cs-settings&tab=emails&cs-message=sendwp-connected' ),
	) );
}
add_action( 'wp_ajax_cs_sendwp_remote_install', 'cs_sendwp_remote_install_handler' );

/**
 * Handle deactivation of SendWP via ajax
 *
 * @since 2.9.15
 */
function cs_sendwp_disconnect () {

	_cs_deprecated_function( __FUNCTION__, '2.11.4' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_send_json_error( array(
			'error' => __( 'You do not have permission to do this.', 'commercestore' )
		) );
	}

	sendwp_disconnect_client();

	deactivate_plugins( 'sendwp/sendwp.php' );

	wp_send_json_success();
}
add_action( 'wp_ajax_cs_sendwp_disconnect', 'cs_sendwp_disconnect' );

/**
 * Reverts to the original download URL validation.
 *
 * @since 2.11.4
 * @todo  Remove this function in 3.0.
 *
 * @param bool   $ret
 * @param string $url
 * @param array  $query_args
 * @param string $original_url
 */
add_filter( 'cs_validate_url_token', function( $ret, $url, $query_args, $original_url ) {
	// If the URL is already validated, we don't need to validate it again.
	if ( $ret ) {
		return $ret;
	}
	$allowed = cs_get_url_token_parameters();
	$remove  = array();
	foreach ( $query_args as $key => $value ) {
		if ( ! in_array( $key, $allowed, true ) ) {
			$remove[] = $key;
		}
	}

	if ( ! empty( $remove ) ) {
		$original_url = remove_query_arg( $remove, $original_url );
	}

	return isset( $query_args['token'] ) && hash_equals( $query_args['token'], cs_get_download_token( $original_url ) );
}, 10, 4 );

/**
 * Get the path of the Product Reviews plugin
 *
 * @since 2.9.20
 *
 * @return mixed|string
 */
function cs_reviews_location() {

	_cs_deprecated_function( __FUNCTION__, '2.11.4' );

	$possible_locations = array( 'cs-reviews/cs-reviews.php', 'CS-Reviews/cs-reviews.php' );
	$reviews_location   = '';

	foreach ( $possible_locations as $location ) {

		if ( 0 !== validate_plugin( $location ) ) {
			continue;
		}
		$reviews_location = $location;
	}

	return $reviews_location;
}

/**
 * Outputs a metabox for the Product Reviews extension to show or activate it.
 *
 * @since 2.8
 * @return void
 */
function cs_render_review_status_metabox() {

	_cs_deprecated_function( __FUNCTION__, '2.11.4' );

	$reviews_location = cs_reviews_location();
	$is_promo_active  = cs_is_promo_active();

	ob_start();

	if ( ! empty( $reviews_location ) ) {
		$review_path  = '';
		$base_url     = wp_nonce_url( admin_url( 'plugins.php' ), 'activate-plugin_' . $reviews_location );
		$args         = array(
			'action'        => 'activate',
			'plugin'        => sanitize_text_field( $reviews_location ),
			'plugin_status' => 'all',
		);
		$activate_url = add_query_arg( $args, $base_url );
		?><p style="text-align: center;"><a href="<?php echo esc_url( $activate_url ); ?>" class="button-secondary"><?php _e( 'Activate Reviews', 'commercestore' ); ?></a></p><?php

	} else {

		// Adjust UTM params based on state of promotion.
		if ( true === $is_promo_active ) {
			$args = array(
				'utm_source'   => 'download-metabox',
				'utm_medium'   => 'wp-admin',
				'utm_campaign' => 'bfcm2019',
				'utm_content'  => 'product-reviews-metabox-bfcm',
			);
		} else {
			$args = array(
				'utm_source'   => 'edit-download',
				'utm_medium'   => 'enable-reviews',
				'utm_campaign' => 'admin',
			);
		}

		$base_url = 'https://commercestore.com/downloads/product-reviews';
		$url      = add_query_arg( $args, $base_url );
		?>
		<p>
			<?php
			// Translators: The %s represents the link to the Product Reviews extension.
			echo wp_kses_post( sprintf( __( 'Would you like to enable reviews for this product? Check out our <a target="_blank" href="%s">Product Reviews</a> extension.', 'commercestore' ), esc_url( $url ) ) );
			?>
		</p>
		<?php
		// Add an additional note if a promotion is active.
		if ( true === $is_promo_active ) {
			?>
			<p>
				<?php echo wp_kses_post( __( 'Act now and <strong>SAVE 25%</strong> on your purchase. Sale ends <em>23:59 PM December 6th CST</em>. Use code <code>BFCM2019</code> at checkout.', 'commercestore' ) ); ?>
			</p>
			<?php
		}
	}

	$rendered = ob_get_contents();
	ob_end_clean();

	echo wp_kses_post( $rendered );
}

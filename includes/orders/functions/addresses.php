<?php
/**
 * Order Address Functions.
 *
 * @package     CS
 * @subpackage  Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add an order address.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Array of order address data. Default empty.
 *
 *     The `date_created` and `date_modified` parameters do not need to be passed.
 *     They will be automatically populated if empty.
 *
 *     @type int    $order_id      Order ID. Default `0`.
 *     @type string $name          Customer's full name. Default empty.
 *     @type string $address       First line of address. Default empty.
 *     @type string $address2      Second line of address. Default empty.
 *     @type string $city          City. Default empty.
 *     @type string $region        Region. See `cs_get_shop_states()` for
 *                                 accepted values. Default empty.
 *     @type string $postal_code   Postal code. Default empty.
 *     @type string $country       Country. See `cs_get_country_list()` for
 *                                 accepted values. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 * @return int|false ID of newly created order address, false on error.
 */
function cs_add_order_address( $data ) {

	// An order ID must be supplied for every address inserted.
	if ( empty( $data['order_id'] ) ) {
		return false;
	}

	// Set up an array with empty address keys. If all of these are empty in $data, the address should not be added.
	$empty_address    = array(
		'address'     => '',
		'address2'    => '',
		'city'        => '',
		'region'      => '',
		'country'     => '',
		'postal_code' => '',
	);
	$address_to_check = array_intersect_key( $data, $empty_address );
	$address_to_check = array_filter( $address_to_check );
	if ( empty( $address_to_check ) ) {
		return false;
	}

	// Instantiate a query object
	$order_addresses = new CS\Database\Queries\Order_Address();

	return $order_addresses->add_item( $data );
}

/**
 * Delete an order address.
 *
 * @since 3.0
 *
 * @param int $order_address_id Order address ID.
 * @return int|false `1` if the address was deleted successfully, false on error.
 */
function cs_delete_order_address( $order_address_id = 0 ) {
	$order_addresses = new CS\Database\Queries\Order_Address();

	return $order_addresses->delete_item( $order_address_id );
}

/**
 * Update an order address.
 *
 * @since 3.0
 *
 * @param int   $order_address_id Order address ID.
 * @param array $data {
 *     Array of order address data. Default empty.
 *
 *     @type int    $order_id      Order ID. Default `0`.
 *     @type string $name          Customer's full name. Default empty.
 *     @type string $address       First line of address. Default empty.
 *     @type string $address2      Second line of address. Default empty.
 *     @type string $city          City. Default empty.
 *     @type string $region        Region. See `cs_get_shop_states()` for
 *                                 accepted values. Default empty.
 *     @type string $postal_code   Postal code. Default empty.
 *     @type string $country       Country. See `cs_get_country_list()` for
 *                                 accepted values. Default empty.
 *     @type string $date_created  Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was inserted.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 *     @type string $date_modified Optional. Automatically calculated on add/edit.
 *                                 The date & time the address was last modified.
 *                                 Format: YYYY-MM-DD HH:MM:SS. Default empty.
 * }
 *
 * @return bool Whether or not the API request order was updated.
 */
function cs_update_order_address( $order_address_id = 0, $data = array() ) {
	$order_addresses = new CS\Database\Queries\Order_Address();

	return $order_addresses->update_item( $order_address_id, $data );
}

/**
 * Get an order address by ID.
 *
 * @since 3.0
 *
 * @param int $order_address_id Order address ID.
 * @return \CS\Orders\Order_Address|false Order_Address if successful, false
 *                                         otherwise.
 */
function cs_get_order_address( $order_address_id = 0 ) {
	$order_addresses = new CS\Database\Queries\Order_Address();

	// Return order address
	return $order_addresses->get_item( $order_address_id );
}

/**
 * Get an order address by a specific field value.
 *
 * @since 3.0
 *
 * @param string $field Database table field.
 * @param string $value Value of the row.
 *
 * @return \CS\Orders\Order_Address|false Order_Address if successful, false otherwise.
 */
function cs_get_order_address_by( $field = '', $value = '' ) {
	$order_addresses = new CS\Database\Queries\Order_Address();

	// Return order address
	return $order_addresses->get_item_by( $field, $value );
}

/**
 * Query for order addresses.
 *
 * @see \CS\Database\Queries\Order_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `CS\Database\Queries\Order_Address` for
 *                    accepted arguments.
 * @return \CS\Orders\Order_Address[] Array of `Order_Address` objects.
 */
function cs_get_order_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'number' => 30,
	) );

	// Instantiate a query object
	$order_addresses = new CS\Database\Queries\Order_Address();

	// Return orders
	return $order_addresses->query( $r );
}

/**
 * Count order addresses.
 *
 * @see \CS\Database\Queries\Order_Address::__construct()
 *
 * @since 3.0
 *
 * @param array $args Arguments. See `CS\Database\Queries\Order_Address` for
 *                    accepted arguments.
 * @return int Number of order addresses returned based on query arguments passed.
 */
function cs_count_order_addresses( $args = array() ) {

	// Parse args
	$r = wp_parse_args( $args, array(
		'count' => true,
	) );

	// Query for count(s)
	$order_addresses = new CS\Database\Queries\Order_Address( $r );

	// Return count(s)
	return absint( $order_addresses->found_items );
}

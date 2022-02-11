<?php
/**
 * Discount Codes Table Class
 *
 * @package     CS
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, CommerceStore, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use CS\Admin\List_Table;

/**
 * CS_Discount_Codes_Table Class
 *
 * Renders the Discount Codes table on the Discount Codes page
 *
 * @since 1.4
 * @since 3.0 Updated to work with the discount code migration to custom tables.
 */
class CS_Discount_Codes_Table extends List_Table {

	/**
	 * Get things started
	 *
	 * @since 1.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'discount',
			'plural'   => 'discounts',
			'ajax'     => false,
		) );

		$this->process_bulk_action();
		$this->get_counts();
	}

	/**
	 * Get the base URL for the discount list table
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_base_url() {

		// Remove some query arguments
		$base = remove_query_arg( cs_admin_removable_query_args(), cs_get_admin_base_url() );

		// Add base query args
		return cs_get_admin_url( array(
			'page' => 'cs-discounts'
		), $base );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.4
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return apply_filters( 'cs_discounts_table_columns', array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name',       'commercestore' ),
			'code'       => __( 'Code',       'commercestore' ),
			'amount'     => __( 'Amount',     'commercestore' ),
			'use_count'  => __( 'Uses',       'commercestore' ),
			'start_date' => __( 'Start Date', 'commercestore' ),
			'end_date'   => __( 'End Date',   'commercestore' )
		) );
	}

	/**
	 * Retrieve the sortable columns
	 *
	 * @since 1.4
	 *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return apply_filters( 'cs_discounts_table_sortable_columns', array(
			'name'       => array( 'name',       false ),
			'code'       => array( 'code',       false ),
			'use_count'  => array( 'use_count',  false ),
			'start_date' => array( 'start_date', false ),
			'end_date'   => array( 'end_date',   false )
		) );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.4
	 *
	 * @param CS_Discount $discount Discount object.
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $discount, $column_name ) {
		$value = property_exists( $discount, $column_name ) ? $discount->$column_name : '';

		return apply_filters( 'cs_discounts_table_column', $value, $discount, $column_name );
	}

	/**
	 * This function renders the amount column.
	 *
	 * @since 3.0
	 *
	 * @param CS_Discount $discount Data for the discount code.
	 * @return string Formatted amount.
	 */
	public function column_amount( $discount ) {
		return cs_format_discount_rate( $discount->type, $discount->amount );
	}

	/**
	 * This function renders the start column.
	 *
	 * @since 3.0
	 *
	 * @param CS_Discount $discount Discount object.
	 * @return string Start  date
	 */
	public function column_start_date( $discount ) {
		$start_date            = $discount->start_date;
		$timezone_abbreviation = cs_get_timezone_abbr();

		if ( $start_date ) {
			$display = cs_date_i18n( $start_date, 'M. d, Y' ) . '<br>' . cs_date_i18n( $start_date, 'H:i' ) . ' ' . $timezone_abbreviation;
		} else {
			$display = '&mdash;';
		}

		return $display;
	}

	/**
	 * Render the Expiration column.
	 *
	 * @since 3.0
	 *
	 * @param CS_Discount $discount Discount object.
	 * @return string Expiration date.
	 */
	public function column_end_date( $discount ) {
		$expiration            = $discount->end_date;
		$timezone_abbreviation = cs_get_timezone_abbr();

		if ( $expiration ) {
			$display = cs_date_i18n( $expiration, 'M. d, Y' ) . '<br>' . cs_date_i18n( $expiration, 'H:i' ) . ' ' . $timezone_abbreviation;
		} else {
			$display = '&mdash;';
		}

		return $display;
	}

	/**
	 * Render the Name column.
	 *
	 * @since 1.4
	 *
	 * @param CS_Discount $discount Discount object.
	 * @return string Data shown in the Name column
	 */
	public function column_name( $discount ) {
		$base        = $this->get_base_url();
		$state       = '';
		$row_actions = array();
		$status      = $this->get_status();

		// Bail if current user cannot manage discounts
		if ( ! current_user_can( 'manage_shop_discounts' ) ) {
			return;
		}

		// State
		if ( ( ! empty( $status ) && ( $status !== $discount->status ) ) || ( 'active' !== $discount->status ) ) {
			$state = ' &mdash; ' . cs_get_discount_status_label( $discount->id );
		}

		// Edit
		$row_actions['edit'] = '<a href="' . add_query_arg( array(
			'cs-action' => 'edit_discount',
			'discount'   => $discount->id,
		), $base ) . '">' . __( 'Edit', 'commercestore' ) . '</a>';

		// Active, so add "deactivate" action
		if ( 'active' === strtolower( $discount->status ) ) {
			$row_actions['cancel'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'cs-action' => 'deactivate_discount',
				'discount'   => $discount->id,
			), $base ), 'cs_discount_nonce' ) ) . '">' . __( 'Deactivate', 'commercestore' ) . '</a>';

		// Inactive, so add "activate" action
		} elseif ( 'inactive' === strtolower( $discount->status ) ) {
			$row_actions['activate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'cs-action' => 'activate_discount',
				'discount'   => $discount->id,
			), $base ), 'cs_discount_nonce' ) ) . '">' . __( 'Activate', 'commercestore' ) . '</a>';
		}

		// Delete
		if ( 0 === (int) $discount->use_count ) {
			$row_actions['delete'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'cs-action' => 'delete_discount',
				'discount'   => $discount->id,
			), $base ), 'cs_discount_nonce' ) ) . '">' . __( 'Delete', 'commercestore' ) . '</a>';
		}

		// Filter all discount row actions
		$row_actions = apply_filters( 'cs_discount_row_actions', $row_actions, $discount );

		// Wrap discount title in strong anchor
		$discount_title = '<strong><a class="row-title" href="' . add_query_arg( array(
			'cs-action' => 'edit_discount',
			'discount'   => $discount->id,
		), $base ) . '">' . stripslashes( $discount->name ) . '</a>' . esc_html( $state ) . '</strong>';

		// Return discount title & row actions
		return $discount_title . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.4
	 *
	 * @param CS_Discount $discount Discount object.
	 * @return string Checkbox HTML.
	 */
	public function column_cb( $discount ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" id="%1$s-%2$s" value="%2$s" /><label for="%1$s-%2$s" class="screen-reader-text">%3$s</label>',
			/*$1%s*/ 'discount',
			/*$2%s*/ esc_attr( $discount->id ),
			/* translators: discount name */
			esc_html( sprintf( __( 'Select %s', 'commercestore' ), $discount->name ) )
		);
	}

	/**
	 * Return discount code wrapped in a `<code>` tag.
	 *
	 * @since 3.0
	 *
	 * @param CS_Discount $discount Discount object.
	 * @return string Discount code HTML.
	 */
	public function column_code( $discount ) {
		return '<code class="cs-discount-code">' . $discount->code . '</code>';
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 1.7.2
	 */
	public function no_items() {
		esc_html_e( 'No discounts found.', 'commercestore' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since 1.4
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'activate'   => __( 'Activate',   'commercestore' ),
			'deactivate' => __( 'Deactivate', 'commercestore' ),
			'delete'     => __( 'Delete',     'commercestore' )
		);
	}

	/**
	 * Process bulk actions.
	 *
	 * @since 1.4
	 */
	public function process_bulk_action() {

		// Bail if a nonce was not supplied.
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-discounts' ) ) {
			return;
		}

		$ids = wp_parse_id_list( (array) $this->get_request_var( 'discount', false ) );

		// Bail if no IDs
		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			switch ( $this->current_action() ) {
				case 'delete':
					cs_delete_discount( $id );
					break;

				case 'cancel':
					cs_update_discount_status( $id, 'cancelled' );
					break;

				case 'activate':
					cs_update_discount_status( $id, 'active' );
					break;

				case 'deactivate':
					cs_update_discount_status( $id, 'inactive' );
					break;
			}
		}
	}

	/**
	 * Retrieve the discount code counts.
	 *
	 * @since 1.4
	 */
	public function get_counts() {
		$this->counts = cs_get_discount_counts();
	}

	/**
	 * Retrieves all the data for all the discount codes.
	 *
	 * @since 1.4
	 * @deprecated 3.0 Use get_data()
	 *
	 * @return array Discount codes.
	 */
	public function discount_codes_data() {
		_cs_deprecated_function( __METHOD__, '3.0', 'CS_Discount_Codes_Table::get_data()' );

		return $this->get_data();
	}

	/**
	 * Retrieves all of the table data for the discount codes.
	 *
	 * @since 3.0
	 *
	 * @return array Discount codes table data.
	 */
	public function get_data() {

		// Parse pagination
		$this->args = $this->parse_pagination_args( array(
			'status' => $this->get_status(),
			'search' => $this->get_search(),
		) );

		// Return data
		return cs_get_discounts( $this->args );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.4
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();

		$status = $this->get_status( 'total' );

		// Setup pagination
		$this->set_pagination_args( array(
			'total_pages' => ceil( $this->counts[ $status ] / $this->per_page ),
			'total_items' => $this->counts[ $status ],
			'per_page'    => $this->per_page,
		) );
	}
}

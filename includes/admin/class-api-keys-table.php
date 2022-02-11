<?php
/**
 * API Key Table Class
 *
 * @package     CS
 * @subpackage  Admin/Tools/APIKeys
 * @copyright   Copyright (c) 2018, CommerceStore, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * CS_API_Keys_Table Class
 *
 * Renders the API Keys table
 *
 * @since 2.0
 */
class CS_API_Keys_Table extends WP_List_Table {

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular'  => __( 'API Key',  'commercestore' ),
			'plural'    => __( 'API Keys', 'commercestore' ),
			'ajax'      => false
		) );

		$this->query();
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
		return 'user';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 2.0
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Displays the public key rows
	 *
	 * @since 2.4
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_key( $item ) {
		return '<input readonly="readonly" type="text" class="code" value="' . esc_attr( $item[ 'key' ] ) . '"/>';
	}

	/**
	 * Displays the token rows
	 *
	 * @since 2.4
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_token( $item ) {
		return '<input readonly="readonly" type="text" class="code" value="' . esc_attr( $item[ 'token' ] ) . '"/>';
	}

	/**
	 * Displays the secret key rows
	 *
	 * @since 2.4
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_secret( $item ) {
		return '<input readonly="readonly" type="text" class="code" value="' . esc_attr( $item[ 'secret' ] ) . '"/>';
	}

	/**
	 * Renders the column for the user field
	 *
	 * @since 2.0
	 * @return void
	 */
	public function column_user( $item ) {

		$actions = array();

		if ( apply_filters( 'cs_api_log_requests', true ) ) {
			$actions['view'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( cs_get_admin_url( array( 'view' => 'api_requests', 'page' => 'cs-tools', 'tab' => 'logs', 's' => $item['email'] ) ) ),
				__( 'View Log', 'commercestore' )
			);
		}

		$actions['reissue'] = sprintf(
			'<a href="%s" class="cs-regenerate-api-key">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array( 'user_id' => $item['id'], 'cs_action' => 'process_api_key', 'cs_api_process' => 'regenerate' ) ), 'cs-api-nonce' ) ),
			__( 'Reissue', 'commercestore' )
		);

		$actions['revoke'] = sprintf(
			'<a href="%s" class="cs-revoke-api-key cs-delete">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array( 'user_id' => $item['id'], 'cs_action' => 'process_api_key', 'cs_api_process' => 'revoke' ) ), 'cs-api-nonce' ) ),
			__( 'Revoke', 'commercestore' )
		);

		$actions = apply_filters( 'cs_api_row_actions', array_filter( $actions ) );

		return sprintf( '%1$s %2$s', $item['user'], $this->row_actions( $actions ) );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 2.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'user'   => __( 'Username',   'commercestore' ),
			'key'    => __( 'Public Key', 'commercestore' ),
			'token'  => __( 'Token',      'commercestore' ),
			'secret' => __( 'Secret Key', 'commercestore' )
		);
	}

	/**
	 * Display the key generation form
	 *
	 * @since 1.5
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		static $cs_api_is_bottom = false;

		if ( true === $cs_api_is_bottom ) {
			return;
		}

		if ( 'top' !== $which ) {
			return;
		}

		$cs_api_is_bottom = true; ?>

		<form id="api-key-generate-form" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=cs-tools&tab=api_keys' ); ?>">
			<input type="hidden" name="cs_action" value="process_api_key" />
			<input type="hidden" name="cs_api_process" value="generate" />
			<?php wp_nonce_field( 'cs-api-nonce' ); ?>
			<?php echo CS()->html->ajax_user_search(); ?>
			<?php submit_button( __( 'Generate New API Keys', 'commercestore' ), 'secondary', 'submit', false ); ?>
		</form>

		<?php
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		} ?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div><?php

			$this->extra_tablenav( $which );
			$this->pagination( $which );

			?><br class="clear" />
		</div>

		<?php
	}

	/**
	 * Performs the key query
	 *
	 * @since 2.0
	 * @return void
	 */
	public function query() {
		$users = get_users( array(
			'meta_value' => 'cs_user_secret_key',
			'number'     => $this->per_page,
			'offset'     => $this->per_page * ( $this->get_paged() - 1 )
		) );

		$keys = array();

		foreach( $users as $user ) {
			$keys[$user->ID]['id']     = $user->ID;
			$keys[$user->ID]['email']  = $user->user_email;
			$keys[$user->ID]['user']   = '<a href="' . add_query_arg( 'user_id', $user->ID, 'user-edit.php' ) . '"><strong>' . esc_html( $user->user_login ) . '</strong></a>';

			$keys[$user->ID]['key']    = CS()->api->get_user_public_key( $user->ID );
			$keys[$user->ID]['secret'] = CS()->api->get_user_secret_key( $user->ID );
			$keys[$user->ID]['token']  = CS()->api->get_token( $user->ID );
		}

		return $keys;
	}

	/**
	 * Retrieve count of total users with keys
	 *
	 * @since 2.0
	 * @return int
	 */
	public function total_items() {
		global $wpdb;

		if ( ! get_transient( 'cs_total_api_keys' ) ) {
			$total_items = $wpdb->get_var( "SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_value='cs_user_secret_key'" );

			set_transient( 'cs_total_api_keys', $total_items, 60 * 60 );
		}

		return get_transient( 'cs_total_api_keys' );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 2.0
	 * @return void
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			array(),
			'user'
		);

		$total_items = $this->total_items();
		$this->items = $this->query();
		$per_page    = ! empty( $this->per_page )
			? $this->per_page
			: 30;

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => $total_items > 0
				? ceil( $total_items / $per_page )
				: 0
		) );
	}
}

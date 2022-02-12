<?php
/**
 * API Requests Log View Class
 *
 * @package     CS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * CS_API_Request_Log_Table List Table Class
 *
 * @since 1.5
 * @since 3.0 Updated to use the custom tables and new query classes.
 */
class CS_API_Request_Log_Table extends CS_Base_Log_List_Table {

	/**
	 * Log type
	 *
	 * @var string
	 */
	protected $log_type = 'api_requests';

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'ID'      => __( 'Log ID',          'commercestore' ),
			'details' => __( 'Request Details', 'commercestore' ),
			'version' => __( 'API Version',     'commercestore' ),
			'ip'      => __( 'Request IP',      'commercestore' ),
			'speed'   => __( 'Request Speed',   'commercestore' ),
			'date'    => __( 'Date',            'commercestore' )
		);
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
		return 'ID';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.5
	 *
	 * @param array $item Contains all the data of the api request
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ip':
				return '<a href="' . esc_url( 'https://ipinfo.io/' . esc_attr( $item['ip'] ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $item['ip'] ) . '</a>';
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Output Error Message column
	 *
	 * @since 1.5
	 * @param array $item Contains all the data of the log
	 * @return void
	 */
	public function column_details( $item ) {
	?>
		<a href="#TB_inline?width=640&amp;inlineId=log-details-<?php echo $item['ID']; ?>" class="thickbox"><?php _e( 'View Request', 'commercestore' ); ?></a>
		<div id="log-details-<?php echo $item['ID']; ?>" style="display:none;">
			<?php

			$request = $item['request'];
			$error   = $item['error'];
			echo '<p><strong>' . __( 'API Request:', 'commercestore' ) . '</strong></p>';
			echo '<div>' . $request . '</div>';
			if ( ! empty( $error ) ) {
				echo '<p><strong>' . __( 'Error', 'commercestore' ) . '</strong></p>';
				echo '<div>' . esc_html( $error ) . '</div>';
			}
			echo '<p><strong>' . __( 'API User:', 'commercestore' ) . '</strong></p>';
			echo '<div>' . $item['user_id'] . '</div>';
			echo '<p><strong>' . __( 'API Key:', 'commercestore' ) . '</strong></p>';
			echo '<div>' . $item['api_key'] . '</div>';
			echo '<p><strong>' . __( 'Request Date:', 'commercestore' ) . '</strong></p>';
			echo '<div>' . $item['date'] . '</div>';
			?>
		</div>
	<?php
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since 1.5
	 *
	 * @return array $logs_data Array of all the Log entries
	 */
	public function get_logs( $log_query = array() ) {
		$logs_data = array();
		$logs      = cs_get_api_request_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				/** @var $log CS\Logs\Api_Request_Log */

				$logs_data[] = array(
					'ID'      => $log->id,
					'version' => $log->version,
					'speed'   => $log->time,
					'ip'      => $log->ip,
					'date'    => $log->date_created,
					'api_key' => $log->api_key,
					'request' => $log->request,
					'error'   => $log->error,
					'user_id' => $log->user_id,
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Get the total number of items
	 *
	 * @since 3.0
	 *
	 * @param array $log_query
	 *
	 * @return int
	 */
	public function get_total( $log_query = array() ) {
		return cs_count_api_request_logs( $log_query );
	}
}

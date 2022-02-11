<?php
/**
 * 3.0 Data Migration - Customer Email Addresses.
 *
 * @subpackage  Admin/Upgrades/v3
 * @copyright   Copyright (c) 2018, CommerceStore, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace CS\Admin\Upgrades\v3;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Customer_Email_Addresses Class.
 *
 * @since 3.0
 */
class Customer_Email_Addresses extends Base {

	/**
	 * Constructor.
	 *
	 * @param int $step Step.
	 */
	public function __construct( $step = 1 ) {
		parent::__construct( $step );

		$this->completed_message = __( 'Customer email addresses migration completed successfully.', 'commercestore' );
		$this->upgrade           = 'migrate_customer_email_addresses';
	}

	/**
	 * Retrieve the data pertaining to the current step and migrate as necessary.
	 *
	 * @since 3.0
	 *
	 * @return bool True if data was migrated, false otherwise.
	 */
	public function get_data() {
		$offset = ( $this->step - 1 ) * $this->per_step;

		$results = $this->get_db()->get_results( $this->get_db()->prepare(
			"SELECT *
			 FROM {$this->get_db()->cs_customermeta}
			 WHERE meta_key = %s
			 LIMIT %d, %d",
			esc_sql( 'additional_email' ), $offset, $this->per_step
		) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				// Check if email has already been migrated.
				if ( ! empty( $result->cs_customer_id ) && $result->meta_value ) {
					$number_results = cs_count_customer_email_addresses( array(
						'customer_id' => $result->cs_customer_id,
						'email'       => $result->meta_value
					) );
					if ( $number_results > 0 ) {
						continue;
					}
				}

				Data_Migrator::customer_email_addresses( $result );
			}

			return true;
		}

		return false;
	}

	/**
	 * Calculate the percentage completed.
	 *
	 * @since 3.0
	 *
	 * @return float Percentage.
	 */
	public function get_percentage_complete() {
		$total = $this->get_db()->get_var( $this->get_db()->prepare( "SELECT COUNT(meta_id) AS count FROM {$this->get_db()->cs_customermeta} WHERE meta_key = %s", esc_sql( 'additional_email' ) ) );

		if ( empty( $total ) ) {
			$total = 0;
		}

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}

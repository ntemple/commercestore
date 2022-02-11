<?php
/**
 * Install Function
 *
 * @package     CS
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2018, CommerceStore, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Get the current database version
 *
 * @since 3.0
 *
 * @return string
 */
function cs_get_db_version() {
	$db_version = get_option( 'cs_version' );
	$retval     = ! empty( $db_version )
		? cs_format_db_version( $db_version )
		: false;

	return $retval;
}

/**
 * Update the CommerceStore version in the options table
 *
 * @since 3.0
 */
function cs_update_db_version() {
	if ( defined( 'CS_VERSION' ) ) {
		update_option( 'cs_version', cs_format_db_version( CS_VERSION ) );
	}
}

/**
 * Format the CommerceStore version (going into or coming from the database.)
 *
 * @since 3.0
 *
 * @param string $version
 * @return string
 */
function cs_format_db_version( $version = '' ) {
	return preg_replace( '/[^0-9.].*/', '', $version );
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  2.3
 * @param  string $upgrade_action The upgrade action to check completion for
 * @return bool                   If the action has been added to the copmleted actions array
 */
function cs_has_upgrade_completed( $upgrade_action = '' ) {

	// Bail if no upgrade action to check
	if ( empty( $upgrade_action ) ) {
		return false;
	}

	// Get completed upgrades
	$completed_upgrades = cs_get_completed_upgrades();

	// Return true if in array, false if not
	return in_array( $upgrade_action, $completed_upgrades, true );
}

/**
 * Get's the array of completed upgrade actions
 *
 * @since  2.3
 * @return array The array of completed upgrades
 */
function cs_get_completed_upgrades() {

	// Get the completed upgrades for this site
	$completed_upgrades = get_option( 'cs_completed_upgrades', array() );

	// Return array of completed upgrades
	return (array) $completed_upgrades;
}

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'downloads' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages.
 *
 * @since 1.0
 * @param  bool $network_wide If the plugin is being network-activated
 * @return void
 */
function cs_install( $network_wide = false ) {

	// Multi-site install
	if ( is_multisite() && ! empty( $network_wide ) ) {
		cs_run_multisite_install();

	// Single site install
	} else {
		cs_run_install();
	}
}

/**
 * Run the CommerceStore installation on every site in the current network.
 *
 * @since 3.0
 */
function cs_run_multisite_install() {
	global $wpdb;

	// Get site count
	$network_id = get_current_network_id();
	$query      = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->blogs} WHERE site_id = %d", $network_id );
	$count      = $wpdb->get_var( $query );

	// Bail if no sites (this is really strange and bad)
	if ( empty( $count ) || is_wp_error( $count ) ) {
		return;
	}

	// Setup the steps
	$per_step    = 100;
	$total_steps = ceil( $count / $per_step );
	$step        = 1;
	$offset      = 0;

	// Step through all sites in this network in groups of 100
	do {

		// Get next batch of site IDs
		$query    = $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = %d LIMIT %d, %d", $network_id, $offset, $per_step );
		$site_ids = $wpdb->get_col( $query );

		// Proceed if site IDs exist
		if ( ! empty( $site_ids ) ) {
			foreach ( $site_ids as $site_id ) {
				cs_run_install( $site_id );
			}
		}

		// Bump the limit for the next iteration
		$offset = ( $step * $per_step ) - 1;

		// Bump the step
		++$step;

	// Bail when steps are greater than or equal to total steps
	} while ( $total_steps > $step );
}

/**
 * Run the CommerceStore Install process
 *
 * @since 2.5
 * @since 3.0 Added $site_id parameter
 */
function cs_run_install( $site_id = false ) {

	// Not switched
	$switched = false;

	// Maybe switch to a site
	if ( ! empty( $site_id ) ) {
		switch_to_blog( $site_id );
		$switched = true;
	}

	// Get the current database version
	$current_version = cs_get_db_version();

	// Setup the components (customers, discounts, logs, etc...)
	cs_setup_components();

	// Setup the Downloads Custom Post Type
	cs_setup_cs_post_types();

	// Setup the Download Taxonomies
	cs_setup_download_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Install the default pages
	cs_install_pages();

	// Maybe save the previous version, only if different than current
	if ( ! empty( $current_version ) && ( cs_format_db_version( CS_VERSION ) !== $current_version ) ) {
		if ( version_compare( $current_version, cs_format_db_version( CS_VERSION ), '>' ) ) {
			$downgraded = true;
			update_option( 'cs_version_downgraded_from', $current_version );
		}

		update_option( 'cs_version_upgraded_from', $current_version );
	}

	// Install the default settings
	cs_install_settings();

	// Set the activation date.
	cs_get_activation_date();

	// Create wp-content/uploads/commercestore/ folder and the .htaccess file
	if ( ! function_exists( 'cs_create_protection_files' ) ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/upload-functions.php';
	}
	if ( function_exists( 'cs_create_protection_files' ) ) {
		cs_create_protection_files( true );
	}

	// Create custom tables. (@todo move to BerlinDB)
	CS()->notifications->create_table();

	// Create CommerceStore shop roles
	$roles = new CS_Roles;
	$roles->add_roles();
	$roles->add_caps();

	// API version
	$api = new CS_API;
	update_option( 'cs_default_api_version', 'v' . $api->get_version() );

	// Check for PHP Session support, and enable if available
	CS()->session->use_php_sessions();

	// Maybe set all upgrades as complete (only on fresh installation)
	cs_set_all_upgrades_complete();

	// Update the database version (must be at end, but before site restore)
	cs_update_db_version();

	// Maybe switch back
	if ( true === $switched ) {
		restore_current_blog();
	}
}

/**
 * Maybe set upgrades as complete during a fresh
 * @since 3.0
 */
function cs_set_all_upgrades_complete() {

	// Bail if not a fresh installation
	if ( ! cs_get_db_version() ) {
		return;
	}

	// Maybe include an admin-area only file/function
	if ( ! function_exists( 'cs_set_upgrade_complete' ) ) {
		require_once CS_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
	}

	// When new upgrade routines are added, mark them as complete on fresh install
	$upgrade_routines = array(
		'upgrade_payment_taxes',
		'upgrade_customer_payments_association',
		'upgrade_user_api_keys',
		'remove_refunded_sale_logs',
		'update_file_download_log_data',
	);

	// Loop through upgrade routines and mark them as complete
	foreach ( $upgrade_routines as $upgrade ) {
		cs_set_upgrade_complete( $upgrade );
	}
}

/**
 * Install the required pages
 *
 * @since 3.0
 */
function cs_install_pages() {

	// Get all of the CommerceStore settings
	$current_options = get_option( 'cs_settings', array() );

	// Required store pages
	$pages = array_flip( array(
		'purchase_page',
		'success_page',
		'failure_page',
		'purchase_history_page'
	) );

	// Look for missing pages
	$missing_pages  = array_diff_key( $pages, $current_options );
	$pages_to_check = array_intersect_key( $current_options, $pages );

	// Query for any existing pages
	$posts = new WP_Query(
		array(
			'include'   => array_values( $pages_to_check ),
			'post_type' => 'page',
			'fields'    => 'ids',
		)
	);

	// Default value for checkout page
	$checkout = 0;

	// We'll only update settings on change
	$changed  = false;

	// Loop through all pages, fix or create any missing ones
	foreach ( array_flip( $pages ) as $page ) {

		$page_id = ! empty( $pages_to_check[ $page ] ) ? $pages_to_check[ $page ] : false;

		// Checks if the page option exists
		$page_object = ! array_key_exists( $page, $missing_pages ) && ! empty( $posts->posts ) && ! empty( $page_id )
			? get_post( $page_id )
			: array();

		// Skip if page exists
		if ( ! empty( $page_object ) ) {

			// Set the checkout page
			if ( 'purchase_page' === $page ) {
				$checkout = $page_object->ID;
			}

			// Skip if page exists
			continue;
		}

		// Get page attributes for missing pages
		switch ( $page ) {

			// Checkout
			case 'purchase_page':
				$page_attributes = array(
					'post_title'     => __( 'Checkout', 'commercestore' ),
					'post_content'   => "<!-- wp:shortcode -->[download_checkout]<!-- /wp:shortcode -->",
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_parent'    => 0,
					'post_type'      => 'page',
					'comment_status' => 'closed',
				);
				break;

			// Success
			case 'success_page':
				$text            = __( 'Thank you for your purchase!', 'commercestore' );
				$page_attributes = array(
					'post_title'     => __( 'Purchase Confirmation', 'commercestore' ),
					'post_content'   => "<!-- wp:paragraph --><p>{$text}</p><!-- /wp:paragraph --><!-- wp:shortcode -->[cs_receipt]<!-- /wp:shortcode -->",
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_parent'    => $checkout,
					'post_type'      => 'page',
					'comment_status' => 'closed',
				);
				break;

			// Failure
			case 'failure_page':
				$text            = __( 'Your transaction failed, please try again or contact site support.', 'commercestore' );
				$page_attributes = array(
					'post_title'     => __( 'Transaction Failed', 'commercestore' ),
					'post_content'   => "<!-- wp:paragraph --><p>{$text}</p><!-- /wp:paragraph -->",
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'post_parent'    => $checkout,
					'comment_status' => 'closed',
				);
				break;

			// Purchase History
			case 'purchase_history_page':
				$page_attributes = array(
					'post_title'     => __( 'Purchase History', 'commercestore' ),
					'post_content'   => "<!-- wp:shortcode -->[purchase_history]<!-- /wp:shortcode -->",
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'post_parent'    => $checkout,
					'comment_status' => 'closed',
				);
				break;
		}

		// Create the new page
		$new_page = wp_insert_post( $page_attributes );

		// Update the checkout page ID
		if ( 'purchase_page' === $page ) {
			$checkout = $new_page;
		}

		// Set the page option
		$current_options[ $page ] = $new_page;

		// Pages changed
		$changed = true;
	}

	// Update the option
	if ( true === $changed ) {
		update_option( 'cs_settings', $current_options );
	}
}

/**
 * Install the default settings
 *
 * @since 3.0
 *
 * @global array $cs_options
 */
function cs_install_settings() {
	global $cs_options;

	// Setup some default options
	$options = array();

	// Populate some default values
	$all_settings = cs_get_registered_settings();

	if ( ! empty( $all_settings ) ) {
		foreach ( $all_settings as $tab => $sections ) {
			foreach ( $sections as $section => $settings) {

				// Check for backwards compatibility
				$tab_sections = cs_get_settings_tab_sections( $tab );
				if ( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
					$section  = 'main';
					$settings = $sections;
				}

				foreach ( $settings as $option ) {
					if ( ! empty( $option['type'] ) && 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
						$options[ $option['id'] ] = '1';
					}
				}
			}
		}
	}

	// Get the settings
	$settings       = get_option( 'cs_settings', array() );
	$merged_options = array_merge( $settings, $options );
	$cs_options    = $merged_options;

	// Update the settings
	update_option( 'cs_settings', $merged_options );
}

/**
 * When a new Blog is created in multisite, see if CommerceStore is network activated, and run the installer
 *
 * @since  2.5
 * @param  int|WP_Site $blog WordPress 5.1 passes a WP_Site object.
 * @return void
 */
function cs_new_blog_created( $blog ) {

	// Bail if plugin is not activated for the network
	if ( ! is_plugin_active_for_network( plugin_basename( CS_PLUGIN_FILE ) ) ) {
		return;
	}

	if ( ! is_int( $blog ) ) {
		$blog = $blog->id;
	}

	switch_to_blog( $blog );
	cs_install();
	restore_current_blog();
}
if ( version_compare( get_bloginfo( 'version' ), '5.1', '>=' ) ) {
	add_action( 'wp_initialize_site', 'cs_new_blog_created' );
} else {
	add_action( 'wpmu_new_blog', 'cs_new_blog_created' );
}

/**
 * Drop our custom tables when a mu site is deleted
 *
 * @deprecated 3.0   Handled by WP_DB_Table
 * @since      2.5
 * @param      array $tables  The tables to drop
 * @param      int   $blog_id The Blog ID being deleted
 * @return     array          The tables to drop
 */
function cs_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$customers_db     = new CS_DB_Customers();
	$customer_meta_db = new CS_DB_Customer_Meta();
	if ( $customers_db->installed() ) {
		$tables[] = $customers_db->table_name;
		$tables[] = $customer_meta_db->table_name;
	}
	restore_current_blog();

	return $tables;

}

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * cs_after_install hook.
 *
 * @since 1.7
 * @return void
 */
function cs_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$cs_options = get_transient( '_cs_installed' );

	do_action( 'cs_after_install', $cs_options );

	if ( false !== $cs_options ) {
		// Delete the transient
		delete_transient( '_cs_installed' );
	}
}
add_action( 'admin_init', 'cs_after_install' );

/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when CommerceStore is network activation so we need to create them during admin_init
 *
 * @since 1.9
 * @return void
 */
function cs_install_roles_on_network() {

	global $wp_roles;

	if( ! is_object( $wp_roles ) ) {
		return;
	}


	if( empty( $wp_roles->roles ) || ! array_key_exists( 'shop_manager', $wp_roles->roles ) ) {

		// Create CommerceStore shop roles
		$roles = new CS_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}

}
add_action( 'admin_init', 'cs_install_roles_on_network' );

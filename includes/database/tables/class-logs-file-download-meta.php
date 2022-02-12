<?php
/**
 * Log Meta Table.
 *
 * @package     CS
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace CS\Database\Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use CS\Database\Table;

/**
 * Setup the global "cs_logs_file_downloadmeta" database table
 *
 * @since 3.0
 */
final class Logs_File_Download_Meta extends Table {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'logs_file_downloadmeta';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201907291;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL auto_increment,
			cs_logs_file_download_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY (meta_id),
			KEY cs_logs_file_download_id (cs_logs_file_download_id),
			KEY meta_key (meta_key({$max_index_length}))";
	}
}

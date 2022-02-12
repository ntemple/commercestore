<?php
/**
 * Log Database Object Class.
 *
 * @package     CS
 * @subpackage  Database\Rows
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace CS\Database\Rows;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use CS\Database\Row;

/**
 * Log database row class.
 *
 * This class exists solely to encapsulate database schema changes, to help
 * separate the needs of the application layer from the requirements of the
 * database layer.
 *
 * For example, if a database column is renamed or a return value needs to be
 * formatted differently, this class will make sure old values are still
 * supported and new values do not conflict.
 *
 * @since 3.0
 */
class Log extends Row {

}

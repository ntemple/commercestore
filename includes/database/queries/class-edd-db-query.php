<?php

/**
 * Orders: EDD_DB_Query class
 *
 * @package Plugins/EDD/Database/Queries
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Base class used for querying custom database tables.
 *
 * @since 3.0.0
 *
 * @see EDD_DB_Query::__construct() for accepted arguments.
 */
class EDD_DB_Query {

	/** Table Properties ******************************************************/

	/**
	 * Name of the database table to query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_name = '';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $table_alias = '';

	/** Item ******************************************************************/

	/**
	 * Name for a single item
	 *
	 * I.E. "order"
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name = '';

	/**
	 * Plural version for a group of items.
	 * 
	 * I.E. "orders"
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $item_name_plural = '';

	/**
	 * Callback function for turning IDs into objects.
	 *
	 * I.E. "edd_get_order()"
	 *
	 * @since 3.0.0
	 * @access public
	 * @var mixed
	 */
	public $single_item_callback = '';

	/** Cache *****************************************************************/

	/**
	 * Group to cache queries and queried items in.
	 *
	 * I.E. "edd_orders"
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $cache_group = '';

	/** Columns ***************************************************************/

	/**
	 * Primary database table column.
	 *
	 * This is set based on the column that has primary=true
	 *
	 * @since 3.0.0
	 * @access public
	 * @var WP_DB_Column
	 */
	public $primary_column = false;

	/**
	 * Array of database column objects
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $columns = array();

	/** Clauses ***************************************************************/

	/**
	 * SQL query clauses.
	 *
	 * @since 3.0.0
	 * @access protected
	 * @var array
	 */
	protected $query_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => array(),
		'groupby' => '',
		'orderby' => '',
		'limits'  => ''
	);

	/**
	 * Request clauses.
	 *
	 * @since 3.0.0
	 * @access protected
	 * @var array
	 */
	protected $request_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => '',
		'groupby' => '',
		'orderby' => '',
		'limits'  => ''
	);

	/**
	 * Meta query container.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var object WP_Date_Query
	 */
	protected $meta_query = false;

	/**
	 * Query vars set by the user.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Default values for query vars.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	protected $query_var_defaults = array();

	/** Items *****************************************************************/

	/**
	 * List of items located by the query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var array
	 */
	public $items = array();

	/**
	 * The amount of found items for the current query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $found_items = 0;

	/**
	 * The number of pages.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * SQL for database query.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var string
	 */
	public $request = '';

	/** Methods ***************************************************************/

	/**
	 * Sets up the item query, based on the query vars passed.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of item query parameters. Default empty.
	 *
	 *     @type string       $fields         Site fields to return. Accepts 'ids' (returns an array of item IDs)
	 *                                        or empty (returns an array of complete item objects). Default empty.
	 *     @type boolean      $count          Whether to return a item count (true) or array of item objects.
	 *                                        Default false.
	 *     @type integer      $limit          Limit number of items to retrieve. Default null (no limit).
	 *     @type integer      $offset         Number of items to offset the query. Used to build LIMIT clause.
	 *                                        Default 0.
	 *     @type boolean      $no_found_rows  Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby        Site status or array of statuses. Accepts false, an empty array,
	 *                                        or 'none' to disable `ORDER BY` clause. Default 'id'.
	 *     @type string       $item           How to item retrieved items. Accepts 'ASC', 'DESC'. Default 'ASC'.
	 *     @type string       $search         Search term(s) to retrieve matching items for. Default empty.
	 *     @type array        $search_columns Array of column names to be searched. Accepts 'email', 'date_created', 'date_completed'.
	 *                                        Default empty array.
	 *
	 *     @type boolean      $update_cache   Whether to prime the cache for found items. Default false.
	 * }
	 */
	public function __construct( $query = '' ) {

		// Default query variables
		$this->query_var_defaults = array(
			'fields'            => '',
			'limit'             => 100,
			'offset'            => '',
			'orderby'           => 'id',
			'order'             => 'ASC',
			'search'            => '',
			'search_columns'    => array(),
			'count'             => false,
			'meta_query'        => null, // See WP_Meta_Query
			'no_found_rows'     => true,

			// Caching
			'update_cache'      => true,
			'update_meta_cache' => true
		);

		// Set columns
		$this->set_columns();

		// Maybe query
		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Set columns objects
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function set_columns() {

		// Default array
		$new_columns = array();

		// Loop through columns array
		foreach ( $this->columns as $column ) {
			if ( is_array( $column ) ) {
				$new_columns[] = new EDD_DB_Column( $column );
			} elseif ( $column instanceof EDD_DB_Column ) {
				$new_columns[] = $column;
			}
		}

		// Set columns
		$this->columns = $new_columns;
	}

	/**
	 * Return the global database interface
	 *
	 * @since 3.0.0
	 *
	 * @return wpdb
	 */
	public function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new stdClass();
	}

	/**
	 * Parses arguments passed to the item query with default query parameters.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @see EDD_Order_Query::__construct()
	 *
	 * @param string|array $query Array or string of EDD_Order_Query arguments. See EDD_Order_Query::__construct().
	 */
	public function parse_query( $query = '' ) {
		if ( empty( $query ) ) {
			$query = $this->query_vars;
		}

		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		/**
		 * Fires after the item query vars have been parsed.
		 *
		 * @since 3.0.0
		 *
		 * @param EDD_Order_Query &$this The EDD_Order_Query instance (passed by reference).
		 */
		do_action_ref_array( "parse_{$this->item_name_plural}_query", array( &$this ) );
	}

	/**
	 * Sets up the WordPress query for retrieving items.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public function query( $query = array() ) {
		$this->query_vars = wp_parse_args( $query );

		return $this->get_items();
	}

	/**
	 * Retrieves a list of items matching the query vars.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @return array|int List of items, or number of items when 'count' is passed as a query var.
	 */
	public function get_items() {
		$this->parse_query();

		/**
		 * Fires before object items are retrieved.
		 *
		 * @since 3.0.0
		 *
		 * @param EDD_Order_Query &$this Current instance of EDD_Order_Query, passed by reference.
		 */
		do_action_ref_array( "pre_get_{$this->item_name_plural}", array( &$this ) );

		// $args can include anything. Only use the args defined in the query_var_defaults to compute the key.
		$key          = md5( serialize( wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ) ) );
		$last_changed = wp_cache_get_last_changed( $this->cache_group );

		// Check the cache
		$cache_key   = "get_{$this->item_name_plural}:{$key}:{$last_changed}";
		$cache_value = wp_cache_get( $cache_key, $this->cache_group );

		// No cache value
		if ( false === $cache_value ) {
			$item_ids = $this->get_item_ids();

			// Set the number of found items (make sure it's not a count)
			if ( ! empty( $item_ids ) && is_array( $item_ids ) ) {
				$this->set_found_items( $item_ids );
			}

			// Format the cached value
			$cache_value = array(
				'item_ids'    => $item_ids,
				'found_items' => intval( $this->found_items ),
			);

			// Add value to the cache
			wp_cache_add( $cache_key, $cache_value, $this->cache_group );

		// Value exists in cache
		} else {
			$item_ids          = $cache_value['item_ids'];
			$this->found_items = intval( $cache_value['found_items'] );
		}

		// Pagination
		if ( $this->found_items && $this->query_vars['limit'] ) {
			$this->max_num_pages = ceil( $this->found_items / $this->query_vars['limit'] );
		}

		// Return an int of the count
		if ( $this->query_vars['count'] ) {
			return intval( $item_ids );
		}

		// Cast to integers
		$item_ids = array_map( 'intval', $item_ids );

		// Prime item caches.
		$this->maybe_prime_caches( $item_ids );

		// Return the IDs
		if ( 'ids' === $this->query_vars['fields'] ) {
			$this->items = $item_ids;

			return $this->items;
		}

		// Set items from IDs
		$this->set_items( $item_ids );

		// Return array of items
		return $this->items;
	}

	/**
	 * Set items by mapping them through the single item callback.
	 *
	 * @since 3.0.0
	 * @access private
	 * @param array $item_ids
	 */
	private function set_items( $item_ids = array() ) {

		// Get item instances from IDs.
		$_items = is_callable( $this->single_item_callback )
			? array_map( $this->single_item_callback, $item_ids )
			: $item_ids;

		/**
		 * Filters the object query results.
		 *
		 * @since 3.0.0
		 *
		 * @param array                  $results An array of items.
		 * @param EDD_Order_Query &$this  Current instance of EDD_Order_Query, passed by reference.
		 */
		$_items = apply_filters_ref_array( "the_{$this->item_name_plural}", array( $_items, &$this ) );

		// Make sure items are still item instances.
		$this->items = is_callable( $this->single_item_callback )
			? array_map( $this->single_item_callback, $_items )
			: $item_ids;
	}

	/**
	 * Maybe prime caches for item IDs
	 *
	 * @since 3.0.0
	 *
	 * @param array $item_ids
	 */
	private function maybe_prime_caches( $item_ids = array() ) {
		if ( $this->query_vars['update_cache'] ) {

		}

		if ( $this->query_vars['update_meta_cache'] ) {

		}
	}

	/**
	 * Used internally to get a list of item IDs matching the query vars.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @return int|array A single count of item IDs if a count query. An array of item IDs if a full query.
	 */
	private function get_item_ids() {

		// Setup primary column, and parse the where clause
		$this->set_primary_column();
		$this->parse_where();

		// Order & Order By
		$order   = $this->parse_order( $this->query_vars['order'] );
		$orderby = $this->get_order_by( $order );

		// Limit & Offset
		$limit   = absint( $this->query_vars['limit']  );
		$offset  = absint( $this->query_vars['offset'] );

		// Limits
		if ( ! empty( $limit ) && ( '-1' != $limit ) ) {
			$limits = ! empty( $offset )
				? "LIMIT {$offset}, {$limit}"
				: "LIMIT {$limit}";
		} else {
			$limits = '';
		}

		// Where & Join
		$where = implode( ' AND ', $this->query_clauses['where'] );
		$join  = $this->query_clauses['join'];

		// Fields
		$fields = ! empty( $this->query_vars['count'] )
			? 'COUNT(*)'
			: "{$this->table_alias}.{$this->primary_column->name}";

		/**
		 * Filters the item query clauses.
		 *
		 * @since 3.0.0
		 *
		 * @param array  $pieces A compacted array of item query clauses.
		 * @param object &$this  Current instance passed by reference.
		 */
		$clauses = apply_filters_ref_array( 'item_clauses', array( compact( array( 'fields', 'join', 'where', 'orderby', 'limits', 'groupby' ) ), &$this ) );

		// Setup request
		$this->set_request_clauses( $clauses );
		$this->set_request();

		// Return count
		if ( ! empty( $this->query_vars['count'] ) ) {
			return intval( $this->get_db()->get_var( $this->request ) );
		}

		// Get IDs
		$item_ids = $this->get_db()->get_col( $this->request );

		// Return parsed IDs
		return wp_parse_id_list( $item_ids );
	}

	/**
	 * Set the primary column
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function set_primary_column() {

		// Filter columns
		$filter = wp_filter_object_list( $this->columns, array(
			'primary' => true
		) );

		// Set primary
		$this->primary_column = reset( $filter );
	}

	/**
	 * Set the request clauses
	 *
	 * @since 3.0.0
	 * @access private
	 * @param array $clauses
	 */
	private function set_request_clauses( $clauses = array() ) {

		// Found rows
		$found_rows = empty( $this->query_vars['no_found_rows'] )
			? 'SQL_CALC_FOUND_ROWS'
			: '';

		// Fields
		$fields    = ! empty( $clauses['fields'] )
			? $clauses['fields']
			: '';

		// Join
		$join      = ! empty( $clauses['join'] )
			? $clauses['join']
			: '';

		// Where
		$where     = ! empty( $clauses['where'] )
			? "WHERE {$clauses['where']}"
			: '';

		// Group by
		$groupby   = ! empty( $clauses['groupby'] )
			? "GROUP BY {$clauses['groupby']}"
			: '';

		// Order by
		$orderby   = ! empty( $clauses['orderby'] )
			? "ORDER BY {$clauses['orderby']}"
			: '';

		// Limits
		$limits   = ! empty( $clauses['limits']  )
			? $clauses['limits']
			: '';

		// Select & From
		$table  = $this->get_db()->{$this->table_name};
		$select = "SELECT {$found_rows} {$fields}";
		$from   = "FROM {$table} {$this->table_alias} {$join}";

		// Put query into clauses array
		$this->request_clauses['select']  = $select;
		$this->request_clauses['from']    = $from;
		$this->request_clauses['where']   = $where;
		$this->request_clauses['groupby'] = $groupby;
		$this->request_clauses['orderby'] = $orderby;
		$this->request_clauses['limits']  = $limits;
	}

	/**
	 * Set the request
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function set_request() {
		$this->request = "{$this->request_clauses['select']} {$this->request_clauses['from']} {$this->request_clauses['where']} {$this->request_clauses['groupby']} {$this->request_clauses['orderby']} {$this->request_clauses['limits']}";
	}

	/**
	 * Parse the where clauses for all known columns
	 *
	 * @since 3.0.0
	 * @access private
	 */
	private function parse_where() {

		// Defaults
		$where = $searchable = array();
		$join  = '';
		$and   = '/^\s*AND\s*/';

		// Loop through columns
		foreach ( $this->columns as $column ) {

			// Maybe add name to searchable array
			if ( true === $column->searchable ) {
				$searchable[] = $column->name;
			}

			// Literal column comparison
			if ( ! empty( $this->query_vars[ $column->name ] ) ) {

				// Numeric
				if ( $column->is_numeric() ) {
					$statement = "{$this->table_alias}.{$column->name} = %d";
					$where_id  = absint( $this->query_vars[ $column->name ] );

				// String
				} else {
					$statement = "{$this->table_alias}.{$column->name} = %s";
					$where_id  = $this->query_vars[ $column->name ];
				}

				// Add to where array
				$where[ $column->name ] = $this->get_db()->prepare( $statement, $where_id );
			}

			// __in
			if ( true === $column->in ) {
				$where_id = "{$column->name}__in";

				// Parse item email for an IN clause.
				if ( isset( $this->query_vars[ $where_id ] ) && is_array( $this->query_vars[ $where_id ] ) ) {

					// Convert single item arrays to literal column comparisons
					if ( 1 === count( $this->query_vars[ $where_id ] ) ) {
						$column_value = reset( $this->query_vars[ $where_id ] );
						$statement    = "{$this->table_alias}.{$column->name} = %s";

						$where[ $column->name ] = $this->get_db()->prepare( $statement, $column_value );

					// Implode
					} else {
						$where[ $where_id ] = "{$this->table_alias}.{$column->name} IN ( '" . implode( "', '", $this->get_db()->_escape( $this->query_vars[ $where_id ] ) ) . "' )";
					}
				}
			}

			// __not_in
			if ( true === $column->not_in ) {
				$where_id = "{$column->name}__not_in";

				// Parse item email for an IN clause.
				if ( isset( $this->query_vars[ $where_id ] ) && is_array( $this->query_vars[ $where_id ] ) ) {

					// Convert single item arrays to literal column comparisons
					if ( 1 === count( $this->query_vars[ $where_id ] ) ) {
						$column_value = reset( $this->query_vars[ $where_id ] );
						$statement    = "{$this->table_alias}.{$column->name} != %s";

						$where[ $column->name ] = $this->get_db()->prepare( $statement, $column_value );

					// Implode
					} else {
						$where[ $where_id ] = "{$this->table_alias}.{$column->name} NOT IN ( '" . implode( "', '", $this->get_db()->_escape( $this->query_vars[ $where_id ] ) ) . "' )";
					}
				}
			}

			// date_query
			if ( true === $column->date_query ) {
				$where_id   = "{$column->name}_query";
				$date_query = $this->query_vars[ $where_id ];

				// Setup date query where clause
				if ( ! empty( $date_query ) && is_array( $date_query ) ) {
					$this->{$where_id}  = new WP_Date_Query( $date_query, "{$this->table_alias}.{$column->name}" );
					$where[ $where_id ] = preg_replace( $and, '', $this->{$where_id}->get_sql() );
				}
			}
		}

		// Falsey search strings are ignored.
		if ( strlen( $this->query_vars['search'] ) ) {
			$search_columns = array();

			// Intersect against known searchable columns
			if ( ! empty( $this->query_vars['search_columns'] ) ) {
				$search_columns = array_intersect(
					$this->query_vars['search_columns'],
					$searchable
				);
			}

			// Default to all searchable columns
			if ( empty( $search_columns ) ) {
				$search_columns = $searchable;
			}

			/**
			 * Filters the columns to search in a EDD_Order_Query search.
			 *
			 * The default columns include 'email' and 'path.
			 *
			 * @since 3.0.0
			 *
			 * @param array         $search_columns Array of column names to be searched.
			 * @param string        $search         Text being searched.
			 * @param EDD_Order_Query $this           The current EDD_Order_Query instance.
			 */
			$search_columns = apply_filters( 'item_search_columns', $search_columns, $this->query_vars['search'], $this );

			// Add search query clause
			$where['search'] = $this->get_search_sql( $this->query_vars['search'], $search_columns );
		}

		// Maybe perform a meta-query
		$meta_query = $this->query_vars['meta_query'];
		if ( ! empty( $meta_query ) && is_array( $meta_query ) ) {
			$this->meta_query = new WP_Meta_Query( $meta_query );
			$clauses          = $this->meta_query->get_sql( $this->item_name, $this->table_alias, $this->primary_column->name, $this );
			$join             = $clauses['join'];

			// Add meta_query query clause
			$where['meta_query'] = preg_replace( $and, '', $clauses['where'] );
		}

		// Set where and join clauses
		$this->query_clauses['where'] = $where;
		$this->query_clauses['join']  = $join;
	}

	/**
	 *
	 * @since 3.0.0
	 * @access private
	 * @param string $order
	 * @return string
	 */
	private function get_order_by( $order = '' ) {

		// Disable ORDER BY with 'none', an empty array, or boolean false.
		if ( in_array( $this->query_vars['orderby'], array( 'none', array(), false ), true ) ) {
			$orderby = '';

		// Ordering by something, so figure it out
		} elseif ( ! empty( $this->query_vars['orderby'] ) ) {
			$ordersby = is_array( $this->query_vars['orderby'] )
				? $this->query_vars['orderby']
				: preg_split( '/[,\s]/', $this->query_vars['orderby'] );

			$orderby_array = array();
			$possible_ins  = wp_filter_object_list( $this->columns, array( 'in' => true ), 'and', 'name' );

			// Loop through possible order by's
			foreach ( $ordersby as $_key => $_value ) {

				// Skip if empty
				if ( empty( $_value ) ) {
					continue;
				}

				// Key is numeric
				if ( is_int( $_key ) ) {
					$_orderby = $_value;
					$_item    = $order;

				// Key is string
				} else {
					$_orderby = $_key;
					$_item    = $_value;
				}

				$parsed = $this->parse_orderby( $_orderby );

				// Skip if empty
				if ( empty( $parsed ) ) {
					continue;
				}

				// Set if __in
				if ( in_array( $_orderby, $possible_ins, true ) ) {
					$orderby_array[] = $parsed;
					continue;
				}

				$orderby_array[] = $parsed . ' ' . $this->parse_item( $_item );
			}

			$orderby = implode( ', ', $orderby_array );
		} else {
			$orderby = "{$this->table_alias}.{$this->primary_column->name} {$order}";
		}

		return $orderby;
	}

	/**
	 * Populates found_items and max_num_pages properties for the current query
	 * if the limit clause was used.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param  array $item_ids Optional array of item IDs
	 */
	private function set_found_items( $item_ids = array() ) {
		if ( ! empty( $this->query_vars['limit'] ) && ! empty( $this->query_vars['no_found_rows'] ) ) {
			/**
			 * Filters the query used to retrieve found item count.
			 *
			 * @since 3.0.0
			 *
			 * @param string $found_items_query SQL query. Default 'SELECT FOUND_ROWS()'.
			 * @param object $item_query        The object instance.
			 */
			$found_items_query = apply_filters( "found_{$this->item_name_plural}_query", 'SELECT FOUND_ROWS()', $this );
			$this->found_items = (int) $this->get_db()->get_var( $found_items_query );
		} elseif ( ! empty( $item_ids ) ) {
			$this->found_items = count( $item_ids );
		}
	}

	/**
	 * Used internally to generate an SQL string for searching across multiple columns.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param string $string  Search string.
	 * @param array  $columns Columns to search.
	 * @return string Search SQL.
	 */
	private function get_search_sql( $string = '', $columns = array() ) {

		// Array or String
		$like = ( false !== strpos( $string, '*' ) )
			? '%' . implode( '%', array_map( array( $this->get_db(), 'esc_like' ), explode( '*', $string ) ) ) . '%'
			: '%' . $this->get_db()->esc_like( $string ) . '%';

		// Default array
		$searches = array();

		// Build search SQL
		foreach ( $columns as $column ) {
			$searches[] = $this->get_db()->prepare( "{$column} LIKE %s", $like );
		}

		// Return the clause
		return '(' . implode( ' OR ', $searches ) . ')';
	}

	/**
	 * Parses and sanitizes 'orderby' keys passed to the item query.
	 *
	 * @since 3.0.0
	 * @access private
	 *
	 * @param string $orderby Field for the items to be itemed by.
	 * @return string|false Value to used in the ORDER clause. False otherwise.
	 */
	private function parse_orderby( $orderby = 'id' ) {

		// Default value
		$parsed = false;

		// __in
		if ( false !== strstr( $orderby, '__in' ) ) {
			$column_name = str_replace( '__in', '', $orderby );
			$column      = wp_filter_object_list( $this->column, array( 'name' => $column_name ) );
			$item_in     = ( true === $column->unsigned )
				? implode( ',', array_map( 'absint', $this->query_vars[ $orderby ] ) )
				: implode( ',', $this->query_vars[ $orderby ] );

			$parsed = "FIELD( {$this->table_alias}.{$column->name}, {$item_in} )";

		// Specific column
		} else {
			switch ( $orderby ) {
				case 'id' :
				case 'number' :
				case 'status' :
				case 'date_created' :
				case 'date_completed' :
				case 'user_id' :
				case 'customer_id' :
				case 'email' :
				case 'gateway' :
				case 'payment_key' :
				case 'subtotal' :
				case 'tax' :
				case 'discounts' :
				case 'total' :
				default :
					$parsed = "{$this->table_alias}.{$orderby}";
					break;
			}
		}

		// Return parsed value
		return $parsed;
	}

	/**
	 * Parses an 'order' query variable and cast it to 'ASC' or 'DESC' as necessary.
	 *
	 * @since 3.0.0
	 * @access private
	 * @param string $order The 'order' query variable.
	 * @return string The sanitized 'order' query variable.
	 */
	private function parse_order( $order  = '') {

		// Bail if malformed
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'ASC';
		}

		// Ascending or Descending
		return ( 'ASC' === strtoupper( $order ) )
			? 'ASC'
			: 'DESC';
	}
}

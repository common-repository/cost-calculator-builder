<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class Orders extends DataBaseModel {
	public static $pending = 'pending';
	public static $paid    = 'paid';

	public static $statusList = array( 'pending', 'paid' );

	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;
		$forms_table = Forms::_table();
		$form_id     = Forms::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			calc_id  INT UNSIGNED NOT NULL,
			calc_title VARCHAR(255) DEFAULT NULL,
			total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			currency CHAR(20) NOT NULL,
			payment_method VARCHAR(30) NOT NULL DEFAULT 'no_payments',
			order_details longtext DEFAULT NULL,
			form_details longtext DEFAULT NULL,
            promocodes longtext DEFAULT NULL,
			created_at TIMESTAMP NOT NULL,
			updated_at TIMESTAMP NOT NULL,
			PRIMARY KEY ({$primary_key}),
		    INDEX `idx_calc_id` (`calc_id`),
		    INDEX `idx_created_at` (`created_at`),
		    INDEX `idx_status` (`status`),
		    INDEX `idx_total` (`total`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	/**
	 * Create Order with payment
	 */
	public static function create_order( $order_data, $payment_data ) {

		self::insert( $order_data );
		$order_id = self::insert_id();

		$payment_data['order_id'] = $order_id;
		Payments::insert( $payment_data );

		return $order_id;
	}

	/**
	 * Update Order by id
	 * todo return result later
	 */
	public static function update_order( $data, $id ) {
		global $wpdb;
		$sql   = sprintf( 'SELECT %1$s.* FROM %1$s WHERE %1$s.id = %%d', self::_table() );
		$order = $wpdb->get_row( $wpdb->prepare( $sql, intval( $id ) ), ARRAY_A ); // phpcs:ignore

		$new_order               = array_replace( $order, array_intersect_key( $data, $order ) );
		$new_order['updated_at'] = wp_date( 'Y-m-d H:i:s' );
		self::update( $new_order, array( 'id' => $id ) );
	}


	/**
	 * Update Order total by id
	 * todo return result later
	 */
	public static function update_order_total( $total, $id ) {
		global $wpdb;

		$sql   = sprintf( 'SELECT %1$s.* FROM %1$s WHERE %1$s.id = %%d', self::_table() );
		$order = $wpdb->get_row( $wpdb->prepare( $sql, intval( $id ) ), ARRAY_A ); // phpcs:ignore

		$new_order          = array_replace( $order, array_intersect_key( array(), $order ) );
		$new_order['total'] = $total;
		self::update( $new_order, array( 'id' => $id ) );
	}


	/**
	 * Update orders
	 */
	public static function update_orders( $d, $args ) {
		global $wpdb;
		$table_name = self::_table();
		$sql        = $wpdb->prepare( "UPDATE $table_name SET status = %s WHERE id IN ($d)", $args ); // phpcs:ignore
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	/**
	 * Get Orders by ids
	 */
	public static function get_by_ids( $ids = array() ) {
		if ( empty( $ids ) ) {
			return array();
		}

		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
					FROM %1$s
					WHERE %1$s.id IN ( %3$s )
					ORDER BY %1$s.%2$s DESC',
			self::_table(),
			static::$primary_key,
			implode( ',', $ids )
		);

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}

	/**
	 * Delete Order
	 */
	public static function delete_orders( $d, $ids ) {
		global $wpdb;
		$table_name = self::_table();
		$sql        = $wpdb->prepare( "DELETE FROM $table_name WHERE id IN ($d)", $ids ); // phpcs:ignore
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	/**
	 * Complete Order by id
	 */
	public static function complete_order_by_id( $id ) {
		global $wpdb;
		$table_name = self::_table();
		return $wpdb->get_results( "UPDATE $table_name SET status = 'complete' WHERE id = $id" ); // phpcs:ignore
	}

	/**
	 * Complete Order by id
	 */
	public static function update_order_total_by_id( $id ) {
		global $wpdb;
		$table_name = self::_table();
		return $wpdb->get_results( "UPDATE $table_name SET status = 'complete' WHERE id = $id" ); // phpcs:ignore
	}

	public static function existing_calcs() {
		global $wpdb;
		$table_name = self::_table();
		return $wpdb->get_results( "SELECT DISTINCT calc_id, calc_title FROM $table_name", ARRAY_A ); // phpcs:ignore
	}

	/**
	 * Get total orders
	 */
	public static function get_total_orders( $params ) {
		global $wpdb;

		$payment_method = $params['payment_method'];
		$payment_status = $params['payment_status'];
		$calc_ids       = $params['calc_ids'];
		$calc_ids_str   = is_array( $calc_ids ) ? implode( ', ', array_fill( 0, count( $calc_ids ), '%s' ) ) : '%s';

		$email     = $params['email'];
		$calc_name = $params['calc_name'];
		$order_id  = $params['order_id'];
		$start     = $params['start'];
		$end       = $params['end'];

		$payment_status_query = $payment_status ? Payments::_table() . '.status in (%s)' : '';
		$payment_method_query = $payment_method ? Payments::_table() . '.type in (%s)' : '';
		$include_and          = strlen( $payment_status_query ) > 0 && strlen( $payment_method_query ) ? ' AND ' : '';
		$payments_query       = strlen( $payment_status_query ) > 0 || strlen( $payment_method_query ) ? 'AND (
				' . $payment_status_query . ' 
				' . $include_and . ' 
				' . $payment_method_query . '
			)' : '';

		$calc_ids_query  = self::_table() . '.calc_id IN (' . $calc_ids_str . ')';
		$email_query     = ' AND JSON_UNQUOTE(JSON_EXTRACT(' . self::_table() . ' .form_details, "$.fields[1].value")) LIKE %s';
		$calc_name_query = ' AND (' . self::_table() . '.calc_title LIKE %s)';
		$order_id_query  = ' AND ' . self::_table() . '.id = %s';

		$search_query = '';
		$date_query   = '';
		$query_array  = array();

		if ( is_array( $calc_ids ) ) {
			foreach ( $calc_ids as $id ) {
				$query_array[] = $id;
			}
		} else {
			$query_array[] = $calc_ids;
		}

		$search_query .= $calc_ids_query;

		if ( ! empty( $email ) && empty( $calc_name ) && empty( $order_id ) ) {
			$query_array[] = $email . '%';
			$search_query .= $email_query;
		}
		if ( empty( $email ) && ! empty( $calc_name ) && empty( $order_id ) ) {
			$query_array[] = $calc_name . '%';
			$search_query .= $calc_name_query;
		}
		if ( empty( $email ) && empty( $calc_name ) && ! empty( $order_id ) ) {
			$query_array[] = $order_id;
			$search_query .= $order_id_query;
		}
		if ( strlen( $start ) > 4 && strlen( $end ) > 4 ) {
			$date_query .= ' AND ' . self::_table() . '.created_at BETWEEN "' . $start . ' 00:00:00" AND "' . $end . ' 23:59:59"';
		}
		// Conditionally add payment_status if it's not empty
		if ( ! empty( $payment_status ) ) {
			$query_array[] = $payment_status;
		}

		// Conditionally add payment_method if it's not empty
		if ( ! empty( $payment_method ) ) {
			$query_array[] = $payment_method;
		}
		//phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT COUNT(*) 
			FROM ' . self::_table() . '
			LEFT JOIN ' . Payments::_table() . ' ON ' . self::_table() . '.id = ' . Payments::_table() . '.order_id
			WHERE ' . $search_query . '
			' . $payments_query . '
			' . $date_query . ';',
			$query_array
		);//phpcs:enable

		return $wpdb->get_var( $sql ); // phpcs:ignore
	}

	/**
	 *  Get all orders
	 */
	public static function get_all_orders( $params ) {
		global $wpdb;

		$payment_method = $params['payment_method'];
		$payment_status = $params['payment_status'];
		$calc_ids       = $params['calc_ids'];
		$calc_ids_str   = is_array( $calc_ids ) ? implode( ', ', array_fill( 0, count( $calc_ids ), '%s' ) ) : '%s';

		$email        = $params['email'];
		$calc_name    = $params['calc_name'];
		$order_id     = $params['order_id'];
		$sorting      = $params['sorting'];
		$order_by     = $params['orderBy'];
		$limit        = $params['limit'];
		$offset       = $params['offset'];
		$start        = $params['start'];
		$end          = $params['end'];
		$is_limit_off = $params['limit_off'];

		$payment_status_query = $payment_status ? Payments::_table() . '.status in (%s)' : '';
		$payment_method_query = $payment_method ? Payments::_table() . '.type in (%s)' : '';
		$include_and          = strlen( $payment_status_query ) > 0 && strlen( $payment_method_query ) ? ' AND ' : '';
		$payments_query       = strlen( $payment_status_query ) > 0 || strlen( $payment_method_query ) ? 'AND (
				' . $payment_status_query . ' 
				' . $include_and . ' 
				' . $payment_method_query . '
			)' : '';

		$calc_ids_query  = self::_table() . '.calc_id IN (' . $calc_ids_str . ')';
		$email_query     = ' AND JSON_UNQUOTE(JSON_EXTRACT(' . self::_table() . ' .form_details, "$.fields[1].value")) LIKE %s';
		$calc_name_query = ' AND (' . self::_table() . '.calc_title LIKE %s)';
		$order_id_query  = ' AND ' . self::_table() . '.id = %s';

		$search_query = '';
		$date_query   = '';
		$limit_query  = '';
		$query_array  = array();

		if ( is_array( $calc_ids ) ) {
			foreach ( $calc_ids as $id ) {
				$query_array[] = $id;
			}
		} else {
			$query_array[] = $calc_ids;
		}

		$search_query .= $calc_ids_query;

		if ( ! empty( $email ) && empty( $calc_name ) && empty( $order_id ) ) {
			$query_array[] = $email . '%';
			$search_query .= $email_query;
		}
		if ( empty( $email ) && ! empty( $calc_name ) && empty( $order_id ) ) {
			$query_array[] = $calc_name . '%';
			$search_query .= $calc_name_query;
		}
		if ( empty( $email ) && empty( $calc_name ) && ! empty( $order_id ) ) {
			$query_array[] = $order_id;
			$search_query .= $order_id_query;
		}
		if ( strlen( $start ) > 4 && strlen( $end ) > 4 ) {
			$date_query .= ' AND ' . self::_table() . '.created_at BETWEEN "' . $start . ' 00:00:00" AND "' . $end . ' 23:59:59"';
		}

		if ( true !== $is_limit_off ) {
			$limit_query .= 'LIMIT ' . $limit . ' OFFSET ' . $offset;
		}

		// Conditionally add payment_status if it's not empty
		if ( ! empty( $payment_status ) ) {
			$query_array[] = $payment_status;
		}

		// Conditionally add payment_method if it's not empty
		if ( ! empty( $payment_method ) ) {
			$query_array[] = $payment_method;
		}

		// phpcs:disable
		$sql = $wpdb->prepare(
			'SELECT ' . self::_table() . '.*, 
			' . Payments::_table() . '.type AS paymentMethod,
			' . Payments::_table() . '.currency AS paymentCurrency,
			' . Payments::_table() . '.status AS paymentStatus,
			' . Payments::_table() . '.transaction,
			' . Payments::_table() . '.total
			FROM ' . self::_table() . '
			LEFT JOIN ' . Payments::_table() . ' ON ' . self::_table() . '.id = ' . Payments::_table() . '.order_id
			WHERE ' . $search_query . '
			' . $payments_query . '
			' . $date_query . '
			ORDER BY ' . $order_by . ' ' . $sorting . '
			' . $limit_query . ';',
			$query_array
		); // phpcs:enable

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function get_order_discounts( $id ) {
		$order      = self::get_order_by_id( array( 'id' => $id ) );
		$promocodes = array();

		$meta_data = get_option( 'calc_meta_data_order_' . $id, array() );
		$totals    = $meta_data['totals'];
		if ( isset( $meta_data['totals'] ) && is_string( $meta_data['totals'] ) ) {
			$totals = json_decode( $meta_data['totals'], true );
		}

		if ( ! empty( $order ) ) {
			if ( isset( $order['promocodes'] ) ) {
				$promocodes = json_decode( $order['promocodes'], true ); //phpcs:ignore
			}
		}

		return array(
			'totals'     => $totals,
			'promocodes' => $promocodes,
		);
	}

	public static function get_order_by_id( $params ) {
		global $wpdb;

		$sql = sprintf(
			'SELECT %1$s.*, 
                    %2$s.type as paymentMethod,
                    %2$s.currency as paymentCurrency,
                    %2$s.status as paymentStatus,
                    %2$s.transaction,
                    %2$s.total
                    FROM %1$s
                    LEFT JOIN %2$s ON %1$s.id = %2$s.order_id
                    WHERE %1$s.id = %3$s 
                    ',
			self::_table(),
			Payments::_table(),
			$params['id']
		);

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}
}

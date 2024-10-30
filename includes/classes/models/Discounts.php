<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class Discounts extends DataBaseModel {
	public static $primary_key = 'discount_id';
	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name  = self::_table();
		$primary_key = self::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			{$primary_key} INT UNSIGNED NOT NULL AUTO_INCREMENT,
			title TEXT,
			calc_id INT UNSIGNED NOT NULL,
			is_promo TINYINT(1) DEFAULT 0,
			view_type ENUM('show_with_title', 'show_without_title') NOT NULL,
			period ENUM('period', 'single_day', 'permanently') NOT NULL,
			period_start_date DATE,
			period_end_date DATE,
			single_date DATE,
			discount_status ENUM('active', 'upcoming', 'ended') NOT NULL DEFAULT 'upcoming',
			created_at TIMESTAMP NOT NULL,
			updated_at TIMESTAMP NOT NULL,
			PRIMARY KEY ({$primary_key}),
			INDEX `idx_calc_id` (`calc_id`),
			INDEX `idx_created_at` (`created_at`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	/**
	 * Create Order with payment
	 */
	public static function create_discount( $discount_data, $promocode_data, $condition_data ) {
		self::insert( $discount_data );
		$discount_id = self::insert_id();

		if ( empty( $discount_data['title'] ) ) {
			self::update( array( 'title' => 'Untitled' ), array( 'discount_id' => $discount_id ) );
		}

		if ( ! empty( $discount_id ) && isset( $discount_data['is_promo'] ) && $discount_data['is_promo'] ) {
			$promocode_data['discount_id'] = $discount_id;
			Promocodes::create_poromo( $promocode_data );
		}

		if ( ! empty( $discount_id ) && ! empty( $condition_data ) ) {
			foreach ( $condition_data as $condition ) {
				$condition['discount_id'] = $discount_id;
				Condition::create_discount_condition( $condition );
			}
		}

		return $discount_id;
	}

	/**
	 * Update Order with payment
	 */
	public static function update_discount( $discount_id, $discount_data, $promocode_data, $condition_data ) {
		self::update_discount_inner( $discount_data, $discount_id );

		if ( empty( $discount_data['title'] ) ) {
			self::update( array( 'title' => 'Untitled' ), array( 'discount_id' => $discount_id ) );
		}

		Promocodes::delete_poromo( $discount_id );

		if ( isset( $discount_data['is_promo'] ) && $discount_data['is_promo'] ) {
			$promocode_data['discount_id'] = $discount_id;
			Promocodes::create_poromo( $promocode_data );
		}

		if ( ! empty( $condition_data ) ) {
			Condition::delete_all_discount_conditions( $discount_id );
			foreach ( $condition_data as $condition ) {
				$condition['discount_id'] = $discount_id;
				Condition::create_discount_condition( $condition );
			}
		}

		return $discount_id;
	}

	public static function update_discount_inner( $data, $discount_id ) {
		global $wpdb;
		$sql      = sprintf( 'SELECT * FROM %1$s WHERE discount_id = %2$s', self::_table(), $discount_id );
		$discount = $wpdb->get_row( $sql, ARRAY_A ); // phpcs:ignore

		if ( ! empty( $discount ) ) {
			$new_discount               = array_replace( $discount, array_intersect_key( $data, $discount ) );
			$new_discount['updated_at'] = wp_date( 'Y-m-d H:i:s' );
			self::update( $new_discount, array( 'discount_id' => $discount_id ) );
		}
	}

	public static function delete_discounts( $ids ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %1$s WHERE discount_id IN (%2$s)', self::_table(), is_array( $ids ) ? implode( ',', $ids ) : strval( $ids ) );
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	public static function delete_all_discounts() {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %1$s', self::_table() );
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	public static function duplicate_discounts( $ids, $calc_id ) {
		global $wpdb;

		$sql = sprintf(
			'SELECT %1$s.*,
					%1$s.discount_id as discount_id,
					%1$s.title as title,
					%1$s.is_promo as is_promo,
					%1$s.view_type as view_type,
					%1$s.period as period,
					%1$s.period_start_date as period_start_date,
					%1$s.period_end_date as period_end_date,
					%1$s.single_date as single_date,
					%1$s.discount_status as discount_status,
					%2$s.promocode_count as promocode_count,
					%2$s.promocode as promocode,
					%2$s.promocode_used as promocode_used
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.discount_id = %2$s.discount_id
					WHERE %1$s.calc_id in (%3$s) AND %1$s.discount_id in (%4$s)
					',
			self::_table(),
			Promocodes::_table(),
			$calc_id,
			is_array( $ids ) ? implode( ',', $ids ) : strval( $ids )
		);

		$discounts = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		if ( empty( $discounts ) ) {
			return false;
		}

		foreach ( $discounts as $discount ) {
			$conditions = Condition::get_discount_conditions( $discount['discount_id'] );

			$discount_data = array(
				'title'             => $discount['title'] ?? 'Untitled',
				'calc_id'           => $calc_id,
				'is_promo'          => $discount['is_promo'],
				'view_type'         => $discount['view_type'],
				'period'            => $discount['period'],
				'period_start_date' => $discount['period_start_date'],
				'period_end_date'   => $discount['period_end_date'],
				'single_date'       => $discount['single_date'],
				'discount_status'   => $discount['discount_status'],
				'created_at'        => wp_date( 'Y-m-d H:i:s' ),
				'updated_at'        => wp_date( 'Y-m-d H:i:s' ),
			);

			$promocode_data = array(
				'promocode_count' => $discount['promocode_count'],
				'promocode'       => $discount['promocode'],
				'created_at'      => wp_date( 'Y-m-d H:i:s' ),
				'updated_at'      => wp_date( 'Y-m-d H:i:s' ),
			);

			$conditions_data = array();

			if ( ! empty( $conditions ) ) {
				foreach ( $conditions as $condition ) {
					$conditions_data[] = array(
						'field_alias'      => $condition['field_alias'],
						'over_price'       => $condition['over_price'],
						'discount_amount'  => $condition['discount_amount'],
						'discount_type'    => $condition['discount_type'],
						'condition_symbol' => $condition['condition_symbol'],
						'created_at'       => wp_date( 'Y-m-d H:i:s' ),
						'updated_at'       => wp_date( 'Y-m-d H:i:s' ),
					);
				}
			}

			self::create_discount( $discount_data, $promocode_data, $conditions_data );
		}

		return true;
	}

	public static function get_total_discounts( $params = array() ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT COUNT(%1$s.discount_id)
					FROM %1$s
					WHERE %1$s.calc_id in (%2$s)
					',
			self::_table(),
			$params['calc_id']
		);

		return $wpdb->get_var( $sql ); // phpcs:ignore
	}

	/**
	 * @throws \Exception
	 */
	public static function get_all_discounts( $params ) {
		global $wpdb;

		$discount_status = $params['discount_status'] ?? '';
		$calc_id         = $params['calc_id'];
		$sorting         = $params['direction'] ?? 'ASC';
		$order_by        = $params['orderBy'] ?? 'discount_id';
		$limit           = $params['limit'] ?? 10;
		$offset          = $params['offset'] ?? 0;

		$sql = sprintf(
			'SELECT %1$s.*,
					%1$s.discount_id as discount_id,
					%1$s.title as title,
					%1$s.is_promo as is_promo,
					%1$s.view_type as view_type,
					%1$s.period as period,
					%1$s.period_start_date as period_start_date,
					%1$s.period_end_date as period_end_date,
					%1$s.single_date as single_date,
					%1$s.discount_status as discount_status,
					%2$s.promocode_count as promocode_count,
					%2$s.promocode as promocode,
					%2$s.promocode_used as promocode_used
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.discount_id = %2$s.discount_id
					WHERE %1$s.calc_id in (%3$s)
					%4$s
					ORDER BY %1$s.%5$s %6$s LIMIT %7$s OFFSET %8$s
					',
			self::_table(),
			Promocodes::_table(),
			$calc_id,
			( ! empty( $discount_status ) ) ? ' AND ' . self::_table() . ".discount_status IN ('{$discount_status}')" : '',
			$order_by,
			$sorting,
			$limit,
			$offset
		);

		$discounts = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

		$discounts = self::update_discount_status( $discounts );

		foreach ( $discounts as $idx => $discount ) {
			$conditions                      = Condition::get_discount_conditions( $discount['discount_id'] );
			$discounts[ $idx ]['conditions'] = $conditions;
		}

		return $discounts;
	}

	public static function get_all_calc_discounts( $calc_id ) {
		global $wpdb;

		$calc_id = self::validate_calc_id( $calc_id );

		$sql = sprintf(
			'SELECT %1$s.*,
					%1$s.discount_id as discount_id,
					%1$s.title as title,
					%1$s.is_promo as is_promo,
					%1$s.view_type as view_type,
					%1$s.period as period,
					%1$s.period_start_date as period_start_date,
					%1$s.period_end_date as period_end_date,
					%1$s.single_date as single_date,
					%1$s.discount_status as discount_status,
					%2$s.promocode_count as promocode_count,
					%2$s.promocode as promocode,
					%2$s.promocode_used as promocode_used
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.discount_id = %2$s.discount_id
					WHERE %1$s.calc_id in (%3$s)
					',
			self::_table(),
			Promocodes::_table(),
			$calc_id
		);

		$discounts = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

		foreach ( $discounts as $idx => $discount ) {
			$conditions                      = Condition::get_discount_conditions( $discount['discount_id'] );
			$discounts[ $idx ]['conditions'] = $conditions;
		}

		return $discounts;
	}

	public static function get_calc_active_discounts( $calc_id ) {
		global $wpdb;

		if ( ! defined( 'CCB_PRO_VERSION' ) ) {
			return array();
		}

		$calc_id = self::validate_calc_id( $calc_id );

		$sql = sprintf(
			'SELECT %1$s.*,
					%1$s.discount_id as discount_id,
					%1$s.title as title,
					%1$s.is_promo as is_promo,
					%1$s.view_type as view_type,
					%1$s.period as period,
					%1$s.period_start_date as period_start_date,
					%1$s.period_end_date as period_end_date,
					%1$s.single_date as single_date,
					%1$s.discount_status as discount_status,
					%2$s.promocode_count as promocode_count,
					%2$s.promocode as promocode,
					%2$s.promocode_used as promocode_used
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.discount_id = %2$s.discount_id
					WHERE %1$s.calc_id = %3$s 
					AND ( (%1$s.period_start_date IS NOT NULL AND CURDATE() BETWEEN %1$s.period_start_date AND %1$s.period_end_date) OR (%1$s.single_date IS NOT NULL AND %1$s.single_date = CURDATE()) OR (%1$s.period = "permanently"))
					ORDER BY %1$s.discount_id ASC
					',
			self::_table(),
			Promocodes::_table(),
			$calc_id
		);

		$discounts = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore

		foreach ( $discounts as $idx => $discount ) {
			$conditions                      = Condition::get_discount_conditions( $discount['discount_id'] );
			$discounts[ $idx ]['conditions'] = $conditions;
		}

		return $discounts;
	}

	public static function has_active_promocode( $calc_id ) {
		global $wpdb;

		if ( ! defined( 'CCB_PRO_VERSION' ) ) {
			return false;
		}

		$calc_id = self::validate_calc_id( $calc_id );

		$sql = sprintf(
			'SELECT %1$s.*,
					%1$s.discount_id as discount_id,
					%1$s.title as title,
					%1$s.is_promo as is_promo,
					%1$s.view_type as view_type,
					%1$s.period as period,
					%1$s.period_start_date as period_start_date,
					%1$s.period_end_date as period_end_date,
					%1$s.single_date as single_date,
					%1$s.discount_status as discount_status,
					%2$s.promocode_count as promocode_count,
					%2$s.promocode as promocode,
					%2$s.promocode_used as promocode_used
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.discount_id = %2$s.discount_id
					WHERE %1$s.calc_id = %3$s AND %1$s.is_promo IS NOT NULL
					AND ( (%1$s.period_start_date IS NOT NULL AND CURDATE() BETWEEN %1$s.period_start_date AND %1$s.period_end_date) OR (%1$s.single_date IS NOT NULL AND %1$s.single_date = CURDATE()) OR (%1$s.period = "permanently"))
					ORDER BY %1$s.discount_id ASC
					',
			self::_table(),
			Promocodes::_table(),
			$calc_id
		);

		$discounts = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		return count( $discounts ) > 0;
	}

	public static function get_promocodes_by_promocode( $calc_id, $promocodes ) {
		global $wpdb;

		$calc_id    = self::validate_calc_id( $calc_id );
		$promocodes = self::validate_promocodes( $calc_id, $promocodes );

		$quotedPromocodes = array_map(
			function( $value ) {
				return "'$value'";
			},
			$promocodes
		);

		$sql = sprintf(
			'SELECT %1$s.*,
					%1$s.discount_id as discount_id,
					%1$s.period as period,
					%1$s.period_start_date as period_start_date,
					%1$s.period_end_date as period_end_date,
					%1$s.single_date as single_date,
					%2$s.promocode_id as promocode_id,
					%2$s.promocode_count as promocode_count,
					%2$s.promocode as promocode,
					%2$s.promocode_used as promocode_used
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.discount_id = %2$s.discount_id
					WHERE %1$s.calc_id = %3$s 
					AND ( (%1$s.period_start_date IS NOT NULL AND CURDATE() BETWEEN %1$s.period_start_date AND %1$s.period_end_date) OR (%1$s.single_date IS NOT NULL AND %1$s.single_date = CURDATE()) OR (%1$s.period = "permanently"))
					AND %2$s.promocode IN (%4$s)
					ORDER BY %1$s.discount_id ASC
					',
			self::_table(),
			Promocodes::_table(),
			$calc_id,
			implode( ',', $quotedPromocodes )
		);

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}

	public static function update_discount_status( $discounts ) {
		foreach ( $discounts as $idx => $discount ) {
			$status = 'upcoming';
			if ( 'permanently' === $discount['period'] ) {
				$status = 'active';
			} else {
				$current_date = new \DateTime( wp_date( 'Y-m-d' ) );
				if ( 'period' === $discount['period'] ) {
					if ( ! empty( $discount['period_start_date'] ) && ! empty( $discount['period_end_date'] ) ) {
						$start = new \DateTime( $discount['period_start_date'] );
						$end   = new \DateTime( $discount['period_end_date'] );

						if ( $current_date >= $start && $current_date <= $end ) {
							$status = 'active';
						} elseif ( $current_date > $end ) {
							$status = 'ended';
						}
					}
				} elseif ( 'single_day' === $discount['period'] ) {
					if ( ! empty( $discount['single_date'] ) ) {
						$single_date = new \DateTime( $discount['single_date'] );
						if ( $current_date == $single_date ) { // phpcs:ignore
							$status = 'active';
						} elseif ( $current_date > $single_date ) {
							$status = 'ended';
						}
					}
				}
			}

			$discount['discount_status'] = $status;
			$discounts[ $idx ]           = $discount;
			self::update_discount_inner( array( 'discount_status' => $status ), $discount['discount_id'] );
		}

		return $discounts;
	}

	public static function validate_calc_id( $calc_id ) {
		if ( is_int( intval( $calc_id ) ) ) {
			return intval( $calc_id );
		}
		return 0;
	}

	public static function validate_promocodes( $calc_id, $promocodes ) {
		$discounts           = self::get_all_calc_discounts( $calc_id );
		$existing_promocodes = array();
		$result              = array();

		foreach ( $discounts as $discount ) {
			if ( ! empty( $discount['promocode'] ) ) {
				$existing_promocodes[] = $discount['promocode'];
			}
		}

		foreach ( $promocodes as $promocode ) {
			if ( in_array( $promocode, $existing_promocodes, true ) ) {
				$result[] = $promocode;
			}
		}

		if ( empty( $result ) ) {
			$result[] = '';
		}

		return $result;
	}
}

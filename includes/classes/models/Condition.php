<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class Condition extends DataBaseModel {
	public static $primary_key = 'discount_condition_id';
	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = self::_table();
		$primary_key     = self::$primary_key;
		$discounts_table = Discounts::_table();
		$discounts_id    = Discounts::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            {$primary_key} INT UNSIGNED NOT NULL AUTO_INCREMENT,
            discount_id INT UNSIGNED NOT NULL,
            field_alias VARCHAR(512) NOT NULL,
            over_price DECIMAL(10, 2) NOT NULL,
            discount_amount DECIMAL(10, 2) NOT NULL,
            condition_symbol VARCHAR(10) NOT NULL,
            discount_type ENUM('free', 'fixed_amount', 'percent_of_amount') NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY ({$primary_key}),
            FOREIGN KEY (discount_id) REFERENCES {$discounts_table}($discounts_id) ON DELETE CASCADE,
            INDEX `idx_discount_id` (`discount_id`),
            INDEX `idx_field_alias` (`field_alias`)
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );
	}

	public static function create_discount_condition( $condition_data ) {
		self::insert( $condition_data );
		return self::insert_id();
	}

	public static function delete_all_discount_conditions( $discount_id ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %1$s WHERE discount_id IN (%2$s)', self::_table(), $discount_id );
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	public static function delete_all_conditions() {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %1$s', self::_table() );
		$wpdb->get_results( $sql ); // phpcs:ignore
	}

	public static function update_discount_condition( $data, $id ) {
		global $wpdb;
		$sql                = sprintf( 'SELECT %1$s.* FROM %1$s WHERE %1$s.discount_condition_id = %%d', self::_table() );
		$discount_condition = $wpdb->get_row( $wpdb->prepare( $sql, intval( $id ) ), ARRAY_A ); // phpcs:ignore

		$new_discount_condition               = array_replace( $discount_condition, array_intersect_key( $data, $discount_condition ) );
		$new_discount_condition['updated_at'] = wp_date( 'Y-m-d H:i:s' );
		self::update( $new_discount_condition, array( 'discount_condition_id' => $id ) );
	}

	public static function get_discount_conditions( $discount_id ) {
		global $wpdb;
		$sql = sprintf(
			'SELECT %1$s.*
                    FROM %1$s
                    WHERE %1$s.discount_id = %2$s
                    ORDER BY %1$s.discount_id
                    ',
			self::_table(),
			$discount_id
		);

		return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
	}
}

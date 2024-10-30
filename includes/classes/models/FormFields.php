<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;

class FormFields extends DataBaseModel {
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
			{$primary_key} INT UNSIGNED NOT NULL AUTO_INCREMENT,
			form_id INT UNSIGNED NOT NULL,
			type ENUM('name', 'email', 'phone', 'input-textbox', 'textarea', 'number', 'dropdown', 'radio', 'checkbox', 'formatted-text', 'space', 'button') NOT NULL,
			field_width INT UNSIGNED DEFAULT 10,
			sort_id INT UNSIGNED NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			FOREIGN KEY(form_id) REFERENCES {$forms_table}($form_id) ON DELETE CASCADE,
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );

	}

	public static function create_default_fields( $form_id ) {
		global $wpdb;

		$default_fields = array(
			array(
				'type'          => 'name',
				'sort_id'       => 0,
				'field_width'   => 12,
				'insert_method' => 'name_attributes',
			),
			array(
				'type'          => 'email',
				'sort_id'       => 1,
				'field_width'   => 12,
				'insert_method' => 'email_attributes',
			),
			array(
				'type'          => 'phone',
				'sort_id'       => 2,
				'field_width'   => 12,
				'insert_method' => 'phone_attributes',
			),
			array(
				'type'          => 'textarea',
				'sort_id'       => 2,
				'field_width'   => 12,
				'insert_method' => 'textarea_attributes',
			),
		);

		foreach ( $default_fields as $field ) {
			$form_field = array(
				'form_id'     => $form_id,
				'type'        => $field['type'],
				'field_width' => $field['field_width'],
				'sort_id'     => $field['sort_id'],
			);

			self::insert( $form_field );
			$field_id = $wpdb->insert_id;
			FormFieldsAttributes::{$field['insert_method']}( $field_id );
		}
	}

	public static function get_active_fields( $form_id ) {
		global $wpdb;

		$fields_table     = esc_sql( self::_table() );
		$attributes_table = esc_sql( FormFieldsAttributes::_table() );

		// phpcs:disable
		$sql = "SELECT f.id, f.form_id, f.field_width, f.sort_id, f.type, 
					GROUP_CONCAT(
						CONCAT(a.type, ':', COALESCE(a.text_data, ''))
						ORDER BY a.type
						SEPARATOR '|'
					) as attributes
				FROM {$fields_table} f
				LEFT JOIN {$attributes_table} a ON f.id = a.field_id
				WHERE f.form_id = %d
				GROUP BY f.id
				ORDER BY f.sort_id";



		$prepared_sql = $wpdb->prepare( $sql, $form_id );
		$results      = $wpdb->get_results( $prepared_sql, ARRAY_A ); 
		// phpcs:enable

		foreach ( $results as &$field ) {
			$attributes          = explode( '|', $field['attributes'] );
			$field['attributes'] = array();
			foreach ( $attributes as $attr ) {
				$parts = explode( ':', $attr, 2 );
				if ( count( $parts ) === 2 ) {
					list($type, $value) = $parts;
					if ( 'options' === $type ) {
						$value = json_decode( $value );
					}
					$field['attributes'][ $type ] = $value;
				}
			}
		}

		return $results;

	}

	public static function create_field( $form_id, $field ) {
		global $wpdb;

		$form_field = array(
			'form_id'     => $form_id,
			'type'        => $field['type'],
			'sort_id'     => $field['sort_id'],
			'field_width' => $field['field_width'],
		);

		self::insert( $form_field );
		$field_id = $wpdb->insert_id;

		FormFieldsAttributes::insert_front_field_attributes( $field_id, $field['attributes'] );

		return $field_id;
	}

	public static function update_field( $field ) {
		$data = array(
			'field_width' => $field['field_width'],
			'sort_id'     => $field['sort_id'],
		);

		$where = array(
			'id'      => $field['id'],
			'form_id' => $field['form_id'],
		);

		self::update( $data, $where );

		FormFieldsAttributes::update_attributes( $field['id'], $field['attributes'] );
	}

	public static function clean_list( $form_id, $field_list ) {
		global $wpdb;

		if ( ! empty( $field_list ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $field_list ), '%d' ) );

			$sql = sprintf(
				'DELETE FROM %s WHERE form_id = %%d AND %s NOT IN (%s)',
				self::_table(),
				static::$primary_key,
				$placeholders
			);

		    $wpdb->query( $wpdb->prepare( $sql, array_merge( array( $form_id ), $field_list ) ) ); // phpcs:ignore
		}

	}

	public static function duplicate_form_fields( $new_form_id, $original_form_id ) {
		global $wpdb;

		$form_fields_table = self::_table();
		$field_id          = self::$primary_key;

		$sql = "INSERT INTO {$form_fields_table} (form_id, type, field_width, sort_id, created_at, updated_at)
				SELECT %d, type, field_width, sort_id, NOW(), NOW()
				FROM {$form_fields_table}
				WHERE form_id = %d";

		$wpdb->query( $wpdb->prepare( $sql, $new_form_id, $original_form_id ) ); // phpcs:ignore

		$old_field_ids = $wpdb->get_col( $wpdb->prepare( "SELECT {$field_id} FROM {$form_fields_table} WHERE form_id = %d", $original_form_id ) );// phpcs:ignore
		$new_field_ids = $wpdb->get_col( $wpdb->prepare( "SELECT {$field_id} FROM {$form_fields_table} WHERE form_id = %d ORDER BY {$field_id}", $new_form_id ) );// phpcs:ignore

		FormFieldsAttributes::duplicate_field_attributes( $new_field_ids, $old_field_ids );

	}

}

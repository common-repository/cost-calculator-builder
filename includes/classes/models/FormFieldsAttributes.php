<?php

namespace cBuilder\Classes\Database;

use cBuilder\Classes\Vendor\DataBaseModel;


class FormFieldsAttributes extends DataBaseModel {
	/**
	 * Create Table
	 */
	public static function create_table() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name        = self::_table();
		$primary_key       = self::$primary_key;
		$form_fields_table = FormFields::_table();
		$field_id          = FormFields::$primary_key;

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			{$primary_key} INT UNSIGNED NOT NULL AUTO_INCREMENT,
			field_id INT UNSIGNED NOT NULL,
			type VARCHAR(255) NOT NULL,
            text_data LONGTEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			FOREIGN KEY(field_id) REFERENCES {$form_fields_table}($field_id) ON DELETE CASCADE,
			PRIMARY KEY ({$primary_key})
		) {$wpdb->get_charset_collate()};";

		maybe_create_table( $table_name, $sql );

	}

	public static function insert_field_attributes( $field_id, $attributes ) {
		foreach ( $attributes as $attribute ) {
			$data = array(
				'field_id'  => $field_id,
				'type'      => $attribute['type'],
				'text_data' => is_array( $attribute['text_data'] ) ? wp_json_encode( $attribute['text_data'] ) : $attribute['text_data'],
			);
			self::insert( $data );
		}
	}

	public static function insert_front_field_attributes( $field_id, $attributes ) {
		unset( $attributes['tempId'] );
		$formatted_attributes = self::format_attributes( $attributes );
		self::insert_field_attributes( $field_id, $formatted_attributes );
	}

	public static function update_attributes( $field_id, $attributes ) {
		foreach ( $attributes as $key => $value ) {
			$where = array(
				'field_id' => $field_id,
				'type'     => $key,
			);
			$data  = array( 'text_data' => is_array( $value ) ? wp_json_encode( $value ) : $value );
			self::update( $data, $where );
		}
	}

	public static function duplicate_field_attributes( $new_field_ids, $old_field_ids ) {
		global $wpdb;

		$field_id_mapping            = array_combine( $old_field_ids, $new_field_ids );
		$form_fields_attribute_table = self::_table();

		foreach ( $field_id_mapping as $old_field_id => $new_field_id ) {
			$sql = "INSERT INTO {$form_fields_attribute_table} (field_id, type, text_data, created_at, updated_at)
					SELECT %d, type, text_data, NOW(), NOW()
					FROM {$form_fields_attribute_table}
					WHERE field_id = %d";

			$wpdb->query( $wpdb->prepare( $sql, $new_field_id, $old_field_id ) ); // phpcs:ignore
		}
	}

	public static function format_attributes( $attributes ) {
		$formatted_attributes = array();
		foreach ( $attributes as $key => $value ) {
			$formatted_attributes[] = array(
				'type'      => $key,
				'text_data' => $value,
			);
		}

		return $formatted_attributes;
	}

	public static function name_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Name',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => 'Type your name',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function email_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Email',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => 'Type your email',
			),
			array(
				'type'      => 'confirmation',
				'text_data' => '1',
			),
			array(
				'type'      => 'confirmation_label',
				'text_data' => 'Confirm your email',
			),
			array(
				'type'      => 'confirmation_placeholder',
				'text_data' => '',
			),
			array(
				'type'      => 'position',
				'text_data' => 'Vertical',
			),
			array(
				'type'      => 'primary',
				'text_data' => '1',
			),

		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function phone_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Phone',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => 'Type your phone',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function input_textbox_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Input textbox',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => 'Type your text',
			),
			array(
				'type'      => 'character_limit',
				'text_data' => '80',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function textarea_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Message',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => 'Type your text',
			),
			array(
				'type'      => 'character_limit',
				'text_data' => '400',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function number_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Number',
			),
			array(
				'type'      => 'placeholder',
				'text_data' => 'Type your number',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function dropdown_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Dropdown',
			),
			array(
				'type'      => 'options',
				'text_data' => '',
			),
			array(
				'type'      => 'multiselect',
				'text_data' => '1',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function radio_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Radio',
			),
			array(
				'type'      => 'options',
				'text_data' => '',
			),
			array(
				'type'      => 'multiselect',
				'text_data' => '1',
			),
			array(
				'type'      => 'display',
				'text_data' => 'Horizontal',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function checkbox_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'required',
				'text_data' => '1',
			),
			array(
				'type'      => 'label',
				'text_data' => 'Checkbox',
			),
			array(
				'type'      => 'options',
				'text_data' => '',
			),
			array(
				'type'      => 'display',
				'text_data' => 'Horizontal',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function formatted_text_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'label',
				'text_data' => 'Formatted text',
			),
			array(
				'type'      => 'text',
				'text_data' => 'Formatted text',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function button_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'button_text',
				'text_data' => 'Submit Order',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

	public static function space_attributes( $field_id ) {
		$attributes = array(
			array(
				'type'      => 'space',
				'text_data' => '',
			),
		);
		self::insert_field_attributes( $field_id, $attributes );
	}

}

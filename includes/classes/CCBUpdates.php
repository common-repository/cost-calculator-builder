<?php

namespace cBuilder\Classes;

class CCBUpdates {

	private static $updates = array(
		'3.0.1'  => array(
			'move_from_order_to_payment_table',
		),
		'3.0.2'  => array(
			'ccb_update_payments_table_total_column',
		),
		'3.1.14' => array(
			'calculator_add_templates',
		),
		'3.1.7'  => array(
			'calculator_add_container_blur',
		),
		'3.1.20' => array(
			'calculator_email_templates_footer_toggle',
		),
		'3.1.21' => array(
			'calculator_add_styles',
		),
		'3.1.23' => array(
			'ccb_make_woo_product_category_id_multiple',
		),
		'3.1.29' => array(
			'ccb_update_template_delivery_service_field',
		),
		'3.1.31' => array(
			'calculator_add_svg_color_appearance',
		),
		'3.1.32' => array(
			'ccb_add_default_webhook_settings',
		),
		'3.1.34' => array(
			'ccb_add_text_transform_appearance',
		),
		'3.1.48' => array(
			'ccb_add_thank_you_page_settings',
		),
		'3.1.51' => array(
			'ccb_add_summary_header_appearance',
		),
		'3.1.55' => array(
			'ccb_update_min_date_info_to_unselectable',
		),
		'3.1.58' => array(
			'ccb_update_checkbox_conditions',
		),
		'3.1.64' => array(
			'ccb_add_invoice_success_btn',
		),
		'3.1.67' => array(
			'ccb_update_payment_totals',
			'ccb_update_legacy_totals',
		),
		'3.1.69' => array(
			'calculator_add_invoice_close_btn',
		),
		'3.1.71' => array(
			'ccb_add_show_value_option',
			'ccb_add_price_for_file',
		),
		'3.1.74' => array(
			'ccb_checkbox_box_style',
		),
		'3.1.75' => array(
			'ccb_convert_presets_into_theme',
			'ccb_move_box_style_from_settings',
			'ccb_change_font_weight_options',
		),
		'3.1.76' => array(
			'calculator_woo_products_by_product',
			'ccb_set_saved',
		),
		'3.1.79' => array(
			'ccb_added_payment_gateways',
			'ccb_update_payment_type_enum',
		),
		'3.1.82' => array(
			'ccb_general_settings_terms_and_conditions_update',
			'ccb_calculator_settings_terms_and_conditions_update',
		),
		'3.1.85' => array(
			'ccb_update_total_sign_to_unit_measure',
		),
		'3.1.87' => array(
			'ccb_add_discount',
		),
		'3.2.6'  => array(
			'ccb_add_summary_display',
		),
		'3.2.9'  => array(
			'ccb_date_picker_multi_period',
		),
		'3.2.15' => array(
			'ccb_total_field_hidden_calculate',
		),
		'3.2.18' => array(
			'ccb_add_summary_view_to_image_checkbox_field',
		),
		'3.2.25' => array(
			'ccb_update_paypal_data',
			'ccb_sync_calc_settings',
		),
		'3.2.26' => array(
			'ccb_sync_general_settings',
		),
		'3.2.33' => array(
			'ccb_order_form_fields_database_tables_create',
		),
		'3.2.36' => array(
			'ccb_order_off_autoload_for_meta_values',
		),
	);

	public static function init() {
		if ( version_compare( get_option( 'ccb_version' ), CALC_VERSION, '<' ) ) {
			self::update_version();
		}
	}

	public static function get_updates() {
		return self::$updates;
	}

	public static function needs_to_update() {
		$update_versions    = array_keys( self::get_updates() );
		$current_db_version = get_option( 'calc_db_updates', 1 );
		usort( $update_versions, 'version_compare' );

		return ! is_null( $current_db_version ) && version_compare( $current_db_version, end( $update_versions ), '<' );
	}

	private static function maybe_update_db_version() {
		if ( self::needs_to_update() ) {
			$updates         = self::get_updates();
			$calc_db_version = get_option( 'calc_db_updates' );

			foreach ( $updates as $version => $callback_arr ) {
				if ( version_compare( $calc_db_version, $version, '<' ) ) {
					foreach ( $callback_arr as $callback ) {
						call_user_func( array( '\\cBuilder\\Classes\\CCBUpdatesCallbacks', $callback ) );
					}
				}
			}
		}
		update_option( 'calc_db_updates', sanitize_text_field( CALC_DB_VERSION ), true );
	}

	public static function update_version() {
		update_option( 'ccb_version_from', get_option( 'ccb_version' ) );
		update_option( 'ccb_version', sanitize_text_field( CALC_VERSION ), true );
		self::maybe_update_db_version();
	}

	/**
	 * Run calc updates after import old calculators
	 *
	 * @return void
	 */
	public static function run_calc_updates() {
		check_ajax_referer( 'ccb_run_calc_updates', 'nonce' );

		$updates = self::get_updates();

		$data = $_POST;
		if ( empty( $_POST ) ) {
			$request_body = file_get_contents( 'php://input' );
			$request_data = json_decode( $request_body, true );
			$data         = apply_filters( 'stm_ccb_sanitize_array', $request_data );
		}

		if ( current_user_can( 'manage_options' ) && 'calc-run-calc-updates' === $data['action'] && ! empty( $data['access'] ) ) {
			foreach ( $updates as $callback_arr ) {
				foreach ( $callback_arr as $callback ) {
					call_user_func( array( '\\cBuilder\\Classes\\CCBUpdatesCallbacks', $callback ) );
				}
			}
		}
	}
}

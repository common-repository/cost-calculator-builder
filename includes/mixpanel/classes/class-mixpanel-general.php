<?php

namespace CCB\Includes;

use cBuilder\Classes\CCBCalculatorTemplates;
use cBuilder\Classes\CCBSettingsData;
use cBuilder\Classes\Database\Orders;

class Mixpanel_General extends Mixpanel {

	public static function register_data() {
		$all_templates       = CCBCalculatorTemplates::calc_templates_list();
		$template_categories = array_unique( array_column( $all_templates, 'category' ) );

		self::add_data( 'User Plan', self::get_user_plan() );
		self::add_data( 'Orders Count', count( Orders::get_all() ) );
		self::add_data( 'Global PDF Entries Used', self::pdf_entries_used() );
		self::add_data( 'Global Contact Form Used', self::contact_form_used() );
		self::add_data( 'Global Paypal Used', self::paypal_used() );
		self::add_data( 'Global Stripe Used', self::stripe_used() );
		self::add_data( 'Global Razorpay Used', self::razorpay_used() );
		self::add_data( 'Global Cash Payment Used', self::cash_payment_used() );
		self::add_data( 'Global Captcha Used', self::captcha_used() );
		self::add_data( 'Global Currency', self::global_currency() );
		self::add_data( 'Calculator Names', self::get_calculator_titles() );
		self::add_data( 'Backup Settings Enabled', self::get_backup_settings_status() );
		foreach ( $all_templates as $template ) {
			if ( false !== self::get_templates_usage_count( $template['title'] ) ) {
				if ( 'custom_templates' === $template['category'] ) {
					self::add_data( 'Template _custom - ' . $template['title'], self::get_templates_usage_count( $template['title'] ) );
				} else {
					self::add_data( 'Template _native - ' . $template['title'], self::get_templates_usage_count( $template['title'] ) );
				}
			}
		}
		foreach ( $template_categories as $template ) {
			if ( false !== self::get_templates_category_usage( $template ) ) {
				self::add_data( 'Template Category - ' . $template, self::get_templates_category_usage( $template ) );
			}
		}
	}

	public static function get_user_plan() {
		return defined( 'CCB_PRO' ) && defined( 'CCB_PRO_PATH' ) && defined( 'CCB_PRO_URL' ) ? 'Pro' : 'Free';
	}

	public static function pdf_entries_used() {
		$general_settings = CCBSettingsData::get_calc_global_settings();
		return $general_settings['invoice']['use_in_all'] ?? '';
	}

	public static function contact_form_used() {
		$general_settings      = CCBSettingsData::get_calc_global_settings();
		$contact_form_settings = $general_settings['form_fields'] ?? array();

		return ( ! empty( $contact_form_settings['use_in_all'] ) && ! empty( $contact_form_settings['adminEmailAddress'] ) );
	}

	protected static function get_general_payment_settings() {
		$general_settings = CCBSettingsData::get_calc_global_settings();
		return $general_settings['payment_gateway'] ?? array();
	}

	public static function paypal_used() {

		$payment_gateway = self::get_general_payment_settings();
		if ( isset( $payment_gateway['paypal'] ) ) {
			$paypal_settings = $payment_gateway['paypal'] ?? array();
			return ( ! empty( $paypal_settings['use_in_all'] ) && ! empty( $paypal_settings['paypal_mode'] ) && 'live' === $paypal_settings['paypal_mode'] && ! empty( $paypal_settings['paypal_email'] ) );
		}

		return false;
	}

	public static function stripe_used() {
		$payment_gateway = self::get_general_payment_settings();
		if ( isset( $payment_gateway['cards']['card_payments']['stripe'] ) ) {
			$stripe_settings = $payment_gateway['cards']['card_payments']['stripe'];
			return ( ! empty( $payment_gateway['cards']['use_in_all'] ) && ! empty( $stripe_settings['enable'] ) && ! empty( $stripe_settings['secretKey'] ) && ! empty( $stripe_settings['publishKey'] ) );
		}

		return false;
	}

	public static function razorpay_used() {
		$payment_gateway = self::get_general_payment_settings();
		if ( isset( $payment_gateway['cards']['card_payments']['razorpay'] ) ) {
			$razorpay_settings = $payment_gateway['cards']['card_payments']['razorpay'];
			return ( ! empty( $payment_gateway['cards']['use_in_all'] ) && ! empty( $razorpay_settings['enable'] ) && ! empty( $razorpay_settings['keyId'] ) && ! empty( $razorpay_settings['secretKey'] ) );
		}

		return false;
	}

	public static function cash_payment_used() {
		$payment_gateway = self::get_general_payment_settings();
		if ( isset( $payment_gateway['cash_payment'] ) ) {
			$cash_payment = $payment_gateway['cash_payment'];
			return ( ! empty( $cash_payment['use_in_all'] ) );
		}

		return false;
	}

	public static function captcha_used() {
		$general_settings = CCBSettingsData::get_calc_global_settings();
		$captcha_settings = $general_settings['recaptcha'] ?? array();
		if ( isset( $captcha_settings['type'] ) && isset( $captcha_settings[ $captcha_settings['type'] ] ) ) {
			$current_captcha_settings = $captcha_settings[ $captcha_settings['type'] ];
			return ( ! empty( $current_captcha_settings['siteKey'] ) && ! empty( $current_captcha_settings['secretKey'] ) );
		}

		return false;
	}

	public static function global_currency() {
		$general_settings  = CCBSettingsData::get_calc_global_settings();
		$currency_settings = $general_settings['currency'] ?? array();
		return ( isset( $currency_settings['use_in_all'] ) && true === $currency_settings['use_in_all'] ) ? $currency_settings['currency'] : false;
	}

	public static function get_calculator_titles() {
		$calculator_names = array_column( \cBuilder\Classes\CCBUpdatesCallbacks::get_calculators(), 'post_title' );
		return implode( ', ', $calculator_names );
	}

	public static function get_backup_settings_status() {
		$general_settings = CCBSettingsData::get_calc_global_settings();
		return ! empty( $general_settings['backup_settings']['auto_backup'] );
	}

	public static function get_templates_usage_count( $template_name ) {
		$templates_array = array();
		$template_titles = array();
		$templates_count = array();

		foreach ( self::$calculators_id as $calculator ) {
			$templates_array[] = get_post_field( 'post_parent', $calculator );
		}

		$templates_array = array_filter( $templates_array );

		foreach ( $templates_array as $template ) {
			$template_titles[] = get_post_field( 'post_title', $template );
			if ( in_array( $template_name, $template_titles, true ) ) {
				$templates_count = array_count_values( $template_titles );
			}
		}

		return array_key_exists( $template_name, $templates_count ) ? $templates_count[ $template_name ] : false;
	}

	public static function get_templates_category_usage( $template_category ) {
		$templates_id    = array();
		$templates_array = array();
		$templates_count = array();

		foreach ( self::$calculators_id as $calculator_id ) {
			$templates_id[] = get_post_field( 'post_parent', $calculator_id );
		}

		foreach ( $templates_id as $template_id ) {
			$templates_array[] = get_post_meta( $template_id, 'category', true );
		}

		$templates_array = array_filter( $templates_array, 'strlen' );

		$templates_count = ! empty( $templates_array ) ? array_count_values( $templates_array ) : 0;

		return $templates_count[ $template_category ] ?? false;
	}
}

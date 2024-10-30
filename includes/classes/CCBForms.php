<?php
namespace cBuilder\Classes;

use cBuilder\Classes\Database\Forms;
use cBuilder\Classes\Database\FormFields;

class CCBForms {
	public static function get_all_forms() {
		check_ajax_referer( 'ccb_get_forms', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		$result['data']    = Forms::get_all_forms();
		$result['success'] = true;
		$result['message'] = 'Forms list';

		wp_send_json( $result );
	}

	public static function get_active_form_fields() {
		check_ajax_referer( 'ccb_get_active_form_fields', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( isset( $_GET['form_id'] ) ) {
			$result['data']    = FormFields::get_active_fields( intval( $_GET['form_id'] ) );
			$result['success'] = true;
			$result['message'] = 'Active Form Fields';
		}

		wp_send_json( $result );

	}

	public static function apply_form_id() {
		check_ajax_referer( 'ccb_apply_form_id', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( $_GET['form_id'] && $_GET['calc_id'] ) {
			$form_id = intval( $_GET['form_id'] );
			$calc_id = intval( $_GET['calc_id'] );

			$status = update_post_meta( $calc_id, 'form_id', $form_id );

			$order_form_fields = FormFields::get_active_fields( intval( $_GET['form_id'] ) );

			if ( $status && is_array( $order_form_fields ) ) {
				$result['success']           = true;
				$result['order_form_fields'] = $order_form_fields;
				$result['message']           = 'The form has been applied to calculator';
			}
		}

		wp_send_json( $result );

	}

	public static function create_default_form() {
		check_ajax_referer( 'ccb_create_default_form', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		$forms_count = ! empty( Forms::get_all_forms() ) ? count( Forms::get_all_forms() ) : 0;
		$form_id     = Forms::create_default_form( $forms_count );

		if ( isset( $form_id ) && $form_id > 0 ) {
			$result['success']               = true;
			$result['data']['forms']         = Forms::get_all_forms();
			$result['data']['active_fields'] = FormFields::get_active_fields( intval( $form_id ) );
			$result['data']['form_id']       = $form_id;
			$result['message']               = 'The default form has been created';
		}

		wp_send_json( $result );

	}

	public static function delete_form() {
		check_ajax_referer( 'ccb_delete_form', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( $_GET['form_id'] ) {
			$form_id = intval( $_GET['form_id'] );
			Forms::delete_form( $form_id );
			$result['success'] = true;
			$result['data']    = Forms::get_all_forms();
			$result['form_id'] = $form_id;
			$result['message'] = 'The form has been deleted';
		}

		wp_send_json( $result );

	}


	public static function update_form() {
		check_ajax_referer( 'ccb_update_form', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( isset( $_POST['form_id'] ) && isset( $_POST['form_builder_data'] ) ) {
			$data = apply_filters( 'stm_ccb_sanitize_array', $_POST );

			$builder_data_pre = wp_unslash( $data['form_builder_data'] );
			$builder_data     = json_decode( $builder_data_pre, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$result['message'] = 'JSON Decode Error: ' . json_last_error_msg();
				wp_send_json( $result );
				return;
			}
			$form_id                       = intval( $data['form_id'] );
			$form_name                     = $data['form_name'];
			$form_data                     = Forms::update_form( $form_id, $form_name, $builder_data );
			$result['success']             = true;
			$result['data']['form_fields'] = $form_data;
			$result['form_id']             = $form_id;
			$result['message']             = 'The form has been updated';
		}

		wp_send_json( $result );
	}

	public static function duplicate_form() {
		check_ajax_referer( 'ccb_duplicate_form', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( isset( $_GET['form_id'] ) ) {
			$form_id                         = $_GET['form_id'];
			$new_form_id                     = Forms::duplicate_form( $form_id );
			$result['success']               = true;
			$result['data']['forms']         = Forms::get_all_forms();
			$result['data']['active_fields'] = FormFields::get_active_fields( intval( $new_form_id ) );
			$result['data']['form_id']       = $new_form_id;
			$result['message']               = 'The form has been duplicated';
		}

		wp_send_json( $result );
	}

}

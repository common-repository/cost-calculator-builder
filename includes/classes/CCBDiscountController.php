<?php

namespace cBuilder\Classes;

use cBuilder\Classes\Database\Condition;
use cBuilder\Classes\Database\Discounts;
use cBuilder\Classes\Database\Promocodes;

class CCBDiscountController {
	public static function create() {
		check_ajax_referer( 'ccb_create_discount', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		self::maybe_create();

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( ! empty( $_POST['content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$data    = apply_filters( 'stm_ccb_sanitize_array', $_POST );
			$content = str_replace( '\"', '"', $data['content'] );
			$content = str_replace( "\'", "'", $content );
			$content = str_replace( '\\\\', "\\", $content ); //phpcs:ignore
			$content = json_decode( $content, true );

			$discount = array();
			$calc_id  = null;

			if ( isset( $content['discount'] ) ) {
				$discount = $content['discount'];
			}

			if ( isset( $content['calc_id'] ) ) {
				$calc_id = $content['calc_id'];
			}

			$period  = $discount['schedule']['period'];
			$periods = array( 'period', 'single_day', 'permanently' );
			if ( empty( $discount ) || empty( $calc_id ) || ! in_array( $period, $periods, true ) ) {
				$result['message'] = 'Invalid input params';
				wp_send_json( $result );
			}

			$is_promo      = filter_var( $discount['is_promo'], FILTER_VALIDATE_BOOLEAN ) ?? false;
			$discount_data = array(
				'title'      => $discount['title'] ?? 'Untitled',
				'calc_id'    => $calc_id,
				'view_type'  => $discount['schedule']['view_type'],
				'is_promo'   => $is_promo,
				'period'     => $period,
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
				'updated_at' => wp_date( 'Y-m-d H:i:s' ),
			);

			if ( 'period' === $period ) {
				$date_start                         = date( 'Y-m-d', strtotime( str_replace( '/', '-', $discount['schedule']['period_date']['start'] ) ) );
				$date_end                           = date( 'Y-m-d', strtotime( str_replace( '/', '-', $discount['schedule']['period_date']['end'] ) ) );
				$discount_data['period_start_date'] = $date_start;
				$discount_data['period_end_date']   = $date_end;
			} elseif ( 'single_day' === $period ) {
				$date                         = date( 'Y-m-d', strtotime( str_replace( '/', '-', $discount['schedule']['single_date'] ) ) );
				$discount_data['single_date'] = $date;
			}

			$promocode_data = array();
			if ( $is_promo && isset( $discount['promocode'] ) ) {
				$promocode_data['promocode_count'] = $discount['promocode']['promocode_count'];
				$promocode_data['promocode']       = $discount['promocode']['promocode'];
				$promocode_data['created_at']      = wp_date( 'Y-m-d H:i:s' );
				$promocode_data['updated_at']      = wp_date( 'Y-m-d H:i:s' );
			}

			$condition_data = array();
			if ( ! empty( $discount['conditions'] ) ) {
				foreach ( $discount['conditions'] as $condition ) {
					$total_aliases = array();
					foreach ( $condition['totals'] as $total ) {
						$total_aliases[] = $total['alias'];
					}

					$condition_types = array( 'free', 'fixed_amount', 'percent_of_amount' );
					$discount_type   = $condition['discount_type'];
					$aliases         = implode( ',', $total_aliases );

					if ( ! in_array( $discount_type, $condition_types, true ) ) {
						$result['message'] = 'Invalid input params';
						wp_send_json( $result );
					}

					$condition_data[] = array(
						'field_alias'      => $aliases,
						'over_price'       => $condition['over_price'],
						'discount_amount'  => $condition['discount_amount'],
						'discount_type'    => $discount_type,
						'condition_symbol' => $condition['condition'],
						'created_at'       => wp_date( 'Y-m-d H:i:s' ),
						'updated_at'       => wp_date( 'Y-m-d H:i:s' ),
					);
				}
			}

			Discounts::create_discount( $discount_data, $promocode_data, $condition_data );
			$discount_params = array(
				'calc_id' => $calc_id,
			);

			if ( ! empty( $content['pagination'] ) ) {
				$pagination      = self::get_filter_data( $content['pagination'] );
				$discount_params = array_merge( $discount_params, $pagination );
			}

			$result['message']           = 'Deleted successfully';
			$result['success']           = true;
			$result['data']['discounts'] = array(
				'discounts'       => Discounts::get_all_discounts( $discount_params ),
				'discounts_count' => Discounts::get_total_discounts( $discount_params ),
			);
		}

		wp_send_json( $result );
	}

	public static function update() {
		check_ajax_referer( 'ccb_update_discount', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		self::maybe_create();

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( ! empty( $_POST['content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$data    = apply_filters( 'stm_ccb_sanitize_array', $_POST );
			$content = str_replace( '\"', '"', $data['content'] );
			$content = str_replace( "\'", "'", $content );
			$content = str_replace( '\\\\', "\\", $content ); //phpcs:ignore
			$content = json_decode( $content, true );

			$discount    = array();
			$calc_id     = null;
			$discount_id = null;

			if ( isset( $content['discount'] ) ) {
				$discount = $content['discount'];
			}

			if ( isset( $content['calc_id'] ) ) {
				$calc_id = $content['calc_id'];
			}

			if ( isset( $content['discount_id'] ) ) {
				$discount_id = $content['discount_id'];
			}

			$period  = $discount['schedule']['period'];
			$periods = array( 'period', 'single_day', 'permanently' );
			if ( empty( $discount_id ) && empty( $discount ) || empty( $calc_id ) || ! in_array( $period, $periods, true ) ) {
				$result['message'] = 'Invalid input params';
				wp_send_json( $result );
			}

			$is_promo      = filter_var( $discount['is_promo'], FILTER_VALIDATE_BOOLEAN ) ?? false;
			$discount_data = array(
				'title'      => $discount['title'] ?? 'Untitled',
				'calc_id'    => $calc_id,
				'is_promo'   => $is_promo,
				'view_type'  => $discount['schedule']['view_type'],
				'period'     => $period,
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
				'updated_at' => wp_date( 'Y-m-d H:i:s' ),
			);

			if ( 'period' === $period ) {
				$date_start                         = date( 'Y-m-d', strtotime( str_replace( '/', '-', $discount['schedule']['period_date']['start'] ) ) );
				$date_end                           = date( 'Y-m-d', strtotime( str_replace( '/', '-', $discount['schedule']['period_date']['end'] ) ) );
				$discount_data['period_start_date'] = $date_start;
				$discount_data['period_end_date']   = $date_end;
				$discount_data['single_date']       = null;
			} elseif ( 'single_day' === $period ) {
				$date                               = date( 'Y-m-d', strtotime( str_replace( '/', '-', $discount['schedule']['single_date'] ) ) );
				$discount_data['single_date']       = $date;
				$discount_data['period_start_date'] = null;
				$discount_data['period_end_date']   = null;
			}

			$promocode_data = array();
			if ( $is_promo && isset( $discount['promocode'] ) ) {
				$promocode_data['promocode_count'] = $discount['promocode']['promocode_count'];
				$promocode_data['promocode']       = $discount['promocode']['promocode'];
				$promocode_data['created_at']      = wp_date( 'Y-m-d H:i:s' );
				$promocode_data['updated_at']      = wp_date( 'Y-m-d H:i:s' );
				$promocode_data['promocode_used']  = isset( $discount['promocode']['promocode_used'] ) ? intval( $discount['promocode']['promocode_used'] ) : 0;
			}

			$condition_data = array();
			if ( ! empty( $discount['conditions'] ) ) {
				foreach ( $discount['conditions'] as $condition ) {
					$total_aliases = array();
					foreach ( $condition['totals'] as $total ) {
						$total_aliases[] = $total['alias'];
					}

					$condition_types = array( 'free', 'fixed_amount', 'percent_of_amount' );
					$discount_type   = $condition['discount_type'];
					$aliases         = implode( ',', $total_aliases );

					if ( ! in_array( $discount_type, $condition_types, true ) ) {
						$result['message'] = 'Invalid input params';
						wp_send_json( $result );
					}

					$condition_data[] = array(
						'field_alias'      => $aliases,
						'over_price'       => $condition['over_price'],
						'discount_amount'  => $condition['discount_amount'],
						'discount_type'    => $discount_type,
						'condition_symbol' => $condition['condition'],
						'created_at'       => wp_date( 'Y-m-d H:i:s' ),
						'updated_at'       => wp_date( 'Y-m-d H:i:s' ),
					);
				}
			}

			Discounts::update_discount( $discount_id, $discount_data, $promocode_data, $condition_data );

			$discount_params = array(
				'calc_id' => $calc_id,
			);

			if ( ! empty( $content['pagination'] ) ) {
				$pagination      = self::get_filter_data( $content['pagination'] );
				$discount_params = array_merge( $discount_params, $pagination );
			}

			$result['message']           = 'Deleted successfully';
			$result['success']           = true;
			$result['data']['discounts'] = array(
				'discounts'       => Discounts::get_all_discounts( $discount_params ),
				'discounts_count' => Discounts::get_total_discounts( $discount_params ),
			);
		}

		wp_send_json( $result );
	}

	public static function delete() {
		check_ajax_referer( 'ccb_delete_discount', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		self::maybe_create();

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( ! empty( $_POST['content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$data    = apply_filters( 'stm_ccb_sanitize_array', $_POST );
			$content = str_replace( '\"', '"', $data['content'] );
			$content = str_replace( "\'", "'", $content );
			$content = str_replace( '\\\\', "\\", $content ); //phpcs:ignore
			$content = json_decode( $content, true );

			$calc_id      = null;
			$discount_id  = null;
			$discount_ids = array();

			if ( isset( $content['discount_id'] ) ) {
				$discount_id = $content['discount_id'];
			}

			if ( isset( $content['discount_ids'] ) ) {
				$discount_ids = $content['discount_ids'];
			}

			if ( isset( $content['calc_id'] ) ) {
				$calc_id = $content['calc_id'];
			}

			$delete_ids = ! empty( $discount_ids ) ? $discount_ids : array( $discount_id );
			Discounts::delete_discounts( $delete_ids );

			$discount_params = array(
				'calc_id' => $calc_id,
			);

			if ( ! empty( $content['pagination'] ) ) {
				$pagination      = self::get_filter_data( $content['pagination'] );
				$discount_params = array_merge( $discount_params, $pagination );
			}

			$result['message']           = 'Deleted successfully';
			$result['success']           = true;
			$result['data']['discounts'] = array(
				'discounts'       => Discounts::get_all_discounts( $discount_params ),
				'discounts_count' => Discounts::get_total_discounts( $discount_params ),
			);
		}

		wp_send_json( $result );
	}

	public static function duplicate() {
		check_ajax_referer( 'ccb_duplicate_discount', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		self::maybe_create();

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( ! empty( $_POST['content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$data    = apply_filters( 'stm_ccb_sanitize_array', $_POST );
			$content = str_replace( '\"', '"', $data['content'] );
			$content = str_replace( "\'", "'", $content );
			$content = str_replace( '\\\\', "\\", $content ); //phpcs:ignore
			$content = json_decode( $content, true );

			$calc_id      = null;
			$discount_id  = null;
			$discount_ids = array();

			if ( isset( $content['discount_id'] ) ) {
				$discount_id = $content['discount_id'];
			}

			if ( isset( $content['discount_ids'] ) ) {
				$discount_ids = $content['discount_ids'];
			}

			if ( isset( $content['calc_id'] ) ) {
				$calc_id = $content['calc_id'];
			}

			$duplicate_ids = ! empty( $discount_ids ) ? $discount_ids : array( $discount_id );
			Discounts::duplicate_discounts( $duplicate_ids, $calc_id );

			$discount_params = array(
				'calc_id' => $calc_id,
			);

			if ( ! empty( $content['pagination'] ) ) {
				$pagination      = self::get_filter_data( $content['pagination'] );
				$discount_params = array_merge( $discount_params, $pagination );
			}

			$result['message']           = 'Deleted successfully';
			$result['success']           = true;
			$result['data']['discounts'] = array(
				'discounts'       => Discounts::get_all_discounts( $discount_params ),
				'discounts_count' => Discounts::get_total_discounts( $discount_params ),
			);
		}

		wp_send_json( $result );
	}

	public static function discount_preview_list() {
		check_ajax_referer( 'ccb_get_preview_discounts', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		self::maybe_create();

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( isset( $_GET['calc_id'] ) ) {
			$calc_id = $_GET['calc_id'];

			$result['data'] = array(
				'discounts'     => Discounts::get_calc_active_discounts( $calc_id ),
				'has_promocode' => Discounts::has_active_promocode( $calc_id ),
			);

			$result['success'] = true;
			$result['message'] = 'Discount Preview list';
		}

		wp_send_json( $result );
	}
	public static function discount_list() {
		check_ajax_referer( 'ccb_get_discounts', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to run this action', 'cost-calculator-builder' ) );
		}

		self::maybe_create();

		$result = array(
			'success' => false,
			'message' => 'Something went wrong',
			'data'    => array(),
		);

		if ( isset( $_GET['calc_id'] ) ) {
			$params            = self::get_filter_data( $_GET );
			$params['calc_id'] = $_GET['calc_id'];
			$discount_params   = array(
				'calc_id' => $_GET['calc_id'],
			);

			$result['data']['discounts'] = array(
				'discounts'       => Discounts::get_all_discounts( $params ),
				'discounts_count' => Discounts::get_total_discounts( $discount_params ),
			);

			$result['success'] = true;
			$result['message'] = 'Discount list';
		}

		wp_send_json( $result );
	}

	/**
	 * @param $data
	 * @return array
	 */
	private static function get_filter_data( $data ) {
		$sort_by   = ! empty( $data['sortBy'] ) ? sanitize_text_field( $data['sortBy'] ) : 'discount_id';
		$direction = ! empty( $data['direction'] ) ? sanitize_text_field( $data['direction'] ) : 'desc';
		$page      = ! empty( $data['page'] ) ? (int) sanitize_text_field( $data['page'] ) : 1;
		$limit     = ! empty( $data['limit'] ) ? sanitize_text_field( $data['limit'] ) : 5;
		$offset    = 1 === $page ? 0 : ( $page - 1 ) * $limit;

		return array(
			'page'      => $page,
			'limit'     => $limit,
			'offset'    => $offset,
			'sort_by'   => $sort_by,
			'direction' => $direction,
		);
	}

	private static function maybe_create() {
		Discounts::create_table();
		Promocodes::create_table();
		Condition::create_table();
	}
}

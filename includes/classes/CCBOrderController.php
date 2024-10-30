<?php

namespace cBuilder\Classes;

use cBuilder\Classes\Database\Discounts;
use cBuilder\Classes\Database\Orders;
use cBuilder\Classes\Database\Payments;
use cBuilder\Classes\Database\Promocodes;
use cBuilder\Helpers\CCBCleanHelper;

class CCBOrderController {

	public static $numAfterInteger = 2;
	protected static $errors       = array();

	/**
	 * Validation
	 *
	 * @param $data
	 */
	public static function validate( $data ) {
		if ( ! array_key_exists( 'id', $data ) || ! $data['id'] || empty( $data['id'] ) ) {
			self::$errors['id'] = __( 'No calculator id' );
		}
	}

	protected static function validateFile( $file, $field_id, $calc_id ) {
		if ( empty( $file ) ) {
			return false;
		}

		$file_upload_field = null;
		$calc_fields       = get_post_meta( $calc_id, 'stm-fields', true );
		preg_match( '/(_\d+)$/', $field_id, $key );

		foreach ( $calc_fields as $field ) {
			if ( ! empty( $field['alias'] ) && $field_id === $field['alias'] ) {
				$file_upload_field = $field;
			} elseif ( ! empty( $field['alias'] ) && ! empty( $field['groupElements'] ) ) {
				foreach ( $field['groupElements'] as $_field ) {
					if ( ! empty( $_field['alias'] ) ) {
						$possible_aliases = array( $_field['alias'], $_field['alias'] . $key[0] );
						if ( in_array( $field_id, $possible_aliases, true ) ) {
							$file_upload_field = $_field;
						}
					}

					if ( ! empty( $_field['groupElements'] ) ) {
						foreach ( $_field['groupElements'] as $__field ) {
							if ( ! empty( $__field['alias'] ) ) {
								$possible_aliases = array( $__field['alias'], $__field['alias'] . $key[0] );
								if ( in_array( $field_id, $possible_aliases, true ) ) {
									$file_upload_field = $__field;
								}
							}
						}
					}
				}
			}
		}

		/** get file field settings */
		$extension       = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		$allowed_formats = array();

		if ( isset( $file_upload_field['fileFormats'] ) ) {
			foreach ( $file_upload_field['fileFormats'] as $format ) {
				$allowed_formats = array_merge( $allowed_formats, explode( '/', $format ) );
			}
		}

		/** check file extension */
		if ( ! in_array( $extension, $allowed_formats, true ) ) {
			return false;
		}

		/** check file size */
		if ( $file_upload_field['max_file_size'] < round( $file['size'] / 1024 / 1024, 1 ) ) {
			return false;
		}

		return true;
	}


	public static function create() {
		check_ajax_referer( 'ccb_add_order', 'nonce' );

		$result = array(
			'status'  => 'error',
			'success' => false,
			'message' => 'Invalid data',
		);

		$data = null;
		if ( ! empty( $_POST['data'] ) ) {
			$data = ccb_convert_from_btoa( $_POST['data'] );

			if ( ! ccb_is_convert_correct( $data ) ) {
				wp_send_json( $result );
			}
		}

		/**  sanitize POST data  */
		$data = CCBCleanHelper::cleanData( (array) json_decode( stripslashes( $data ) ) );

		self::validate( $data );

		if ( empty( $data['id'] ) && empty( $data['totals'] ) && empty( $data['orderDetails'] ) ) {
			wp_send_json( $result );
		}

		if ( ccb_pro_active() ) {
			$data = CCBCalculator::validate_totals( $data );
		}

		/**
		 *  if  order Id exist not create new one.
		 *  Used just for stripe if card error was found
		 */
		if ( ! empty( $data['orderId'] ) ) {

			$meta_data = array(
				'converted'   => $data['converted'] ?? array(),
				'totals'      => isset( $data['totals'] ) ? wp_json_encode( $data['totals'] ) : array(),
				'otherTotals' => isset( $data['otherTotals'] ) ? wp_json_encode( $data['otherTotals'] ) : array(),
			);

			update_option( 'calc_meta_data_order_' . $data['orderId'], $meta_data );

			$order = Orders::get( 'id', $data['orderId'] );
			if ( null !== $order ) {
				wp_send_json_success(
					array(
						'status'   => 'success',
						'order_id' => $data['orderId'],
					)
				);
				die();
			}
		}

		if ( ! empty( $data['promocodes'] ) ) {
			$promocodes = Discounts::get_promocodes_by_promocode( $data['id'], $data['promocodes'] );
			foreach ( $promocodes as $promocode ) {
				if ( ! empty( $promocode['promocode_count'] ) && $promocode['promocode_count'] > 0 ) {
					$promocode_count = intval( $promocode['promocode_count'] );
					$promocode_used  = ! empty( $promocode['promocode_used'] ) ? intval( $promocode['promocode_used'] ) : 0;
					if ( $promocode_count > $promocode_used ) {
						$update_data = array( 'promocode_used' => $promocode_used + 1 );
						Promocodes::update_discount_condition( $update_data, $promocode['promocode_id'] );
					} else {
						wp_send_json_error(
							array(
								'status'  => 'error',
								'message' => $promocode['promocode'] . ' is out of stock',
							)
						);
						die();
					}
				}
			}
		}

		if ( empty( self::$errors ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$settings = CCBSettingsData::get_calc_single_settings( $data['id'] );
			if ( array_key_exists( 'num_after_integer', $settings['currency'] ) ) {
				self::$numAfterInteger = (int) $settings['currency']['num_after_integer'];
			}

			/** upload files if exist */
			if ( is_array( $_FILES ) ) {

				if ( ! function_exists( 'wp_handle_upload' ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
				}

				$order_details = array();
				$file_url      = array();

				foreach ( $data['orderDetails'] as $detail ) {
					if ( ! empty( $detail['alias'] ) && str_contains( $detail['alias'], 'repeater' ) ) {
						foreach ( $detail['groupElements'] as $item ) {
							$order_details[] = $item;
						}
					} else {
						$order_details[] = $detail;
					}
				}

				/** upload all files, create array for fields */
				foreach ( $_FILES as $file_key => $file ) {
					$field_id          = preg_replace( '/_ccb_.*/', '', $file_key );
					$file_upload_field = null;
					preg_match( '/(_\d+)$/', $field_id, $key );

					foreach ( $order_details as $field ) {
						if ( isset( $field['alias'] ) && ! empty( $field['alias'] ) ) {
							$possible_aliases = array( $field['alias'], $field['alias'] . $key[0] );

							if ( in_array( $field_id, $possible_aliases, true ) ) {
								$file_upload_field = $field;
							}
						}
					}

					/** if field not found continue */
					if ( is_null( $file_upload_field ) ) {
						continue;
					}

					/** validate file by settings */
					$is_valid = self::validateFile( $file, $field_id, $data['id'] );

					if ( ! $is_valid ) {
						continue;
					}

					if ( ! array_key_exists( $field_id, $file_url ) ) {
						$file_url[ $field_id ] = array();
					}

					$file_info = wp_handle_upload( $file, array( 'test_form' => false ) );

					if ( ! empty( $file_info['file'] ) && str_contains( $file['type'], 'svg' ) ) {
						$svg_sanitizer = new \enshrined\svgSanitize\Sanitizer();
						$dirty_svg     = file_get_contents( $file_info['file'] ); //phpcs:ignore
						$clean_svg     = $svg_sanitizer->sanitize( $dirty_svg );
						file_put_contents( $file_info['file'], $clean_svg ); //phpcs:ignore
					}

					if ( $file_info && empty( $file_info['error'] ) ) {
						array_push( $file_url[ $field_id ], $file_info );
					}
				}

				foreach ( $order_details as $field_key => $field ) {
					if ( ! empty( $field['alias'] ) && preg_replace( '/_field_id.*/', '', $field['alias'] ) === 'file_upload' ) {
						$file_key = isset( $file_url[ $field['alias'] ] )
							? $field['alias']
							: ( isset( $file_url[ $field['alias'] . '_' . $field_key ] )
								? $field['alias'] . '_' . $field_key
								: null );

						if ( $file_key ) {
							$order_details[ $field_key ]['options'] = wp_json_encode( $file_url[ $file_key ] );
						}
					}
				}

				$data['orderDetails'] = $order_details;
			}

			foreach ( $data['orderDetails'] as $key => $field ) {
				if ( isset( $field['alias'] ) && str_contains( $field['alias'], 'text_area' ) ) {
					$field[ $key ]['value'] = sanitize_text_field( $field[ $key ]['value'] );
				}
			}

			$order_data = array(
				'calc_id'       => $data['id'],
				'calc_title'    => get_post_meta( $data['id'], 'stm-name', true ),
				'status'        => ! empty( $data['status'] ) ? $data['status'] : Orders::$pending,
				'order_details' => wp_json_encode( $data['orderDetails'] ),
				'form_details'  => wp_json_encode( $data['formDetails'] ),
				'promocodes'    => wp_json_encode( $data['promocodes'] ?? array() ),
				'created_at'    => wp_date( 'Y-m-d H:i:s' ),
				'updated_at'    => wp_date( 'Y-m-d H:i:s' ),
			);

			$total = number_format( (float) $data['total'], self::$numAfterInteger, '.', '' );

			if ( empty( $total ) ) {
				$total = 0;
			}

			$currency = array_key_exists( 'currency', $settings['currency'] ) ? $settings['currency']['currency'] : null;
			if ( 'paypal' === $data['paymentMethod'] ) {
				$general_settings = CCBSettingsData::get_calc_global_settings();
				if ( ! empty( $general_settings['payment_gateway']['paypal']['use_in_all'] ) ) {
					$currency = $general_settings['payment_gateway']['paypal']['currency_code'];
				} else {
					$currency = $settings['payment_gateway']['paypal']['currency_code'];
				}
			}

			$payment_data = array(
				'type'       => ! empty( $data['paymentMethod'] ) ? $data['paymentMethod'] : Payments::$defaultType,
				'currency'   => $currency,
				'status'     => Payments::$defaultStatus,
				'total'      => $total,
				'created_at' => wp_date( 'Y-m-d H:i:s' ),
				'updated_at' => wp_date( 'Y-m-d H:i:s' ),
			);

			$before_data = array(
				'payment_data' => $payment_data,
				'order_data'   => $order_data,
			);

			apply_filters( 'ccb_orders_before_create', $before_data );

			$id = Orders::create_order( $order_data, $payment_data );

			do_action( 'ccb_after_create_order', $order_data, $payment_data );
			$meta_data = array(
				'converted'   => $data['converted'] ?? array(),
				'totals'      => isset( $data['totals'] ) ? wp_json_encode( $data['totals'] ) : array(),
				'otherTotals' => isset( $data['otherTotals'] ) ? wp_json_encode( $data['otherTotals'] ) : array(),
			);

			update_option( 'calc_meta_data_order_' . $id, $meta_data, false );

			do_action( 'ccb_order_created', $order_data, $payment_data );

			wp_send_json_success(
				array(
					'status'   => 'success',
					'order_id' => $id,
				)
			);
		}

		wp_send_json( $result );
	}

	public static function update() {
		check_ajax_referer( 'ccb_update_order', 'nonce' );

		if ( empty( $_POST['data'] ) ) {
			wp_send_json(
				array(
					'status'  => 'error',
					'success' => false,
					'message' => 'Invalid data',
				)
			);
		}

		$data = null;
		if ( isset( $_POST['data'] ) ) {
			$data = ccb_convert_from_btoa( $_POST['data'], true );
		}

		if ( ! empty( $data['ids'] ) ) {
			$ids    = sanitize_text_field( $data['ids'] );
			$status = ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : null;

			$ids  = explode( ',', $ids );
			$d    = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$args = $ids;
			array_unshift( $args, $status );

			try {
				Orders::update_orders( $d, $args );
				Payments::update_payment_status_by_order_ids( $ids, $status );

				wp_send_json(
					array(
						'status'  => 200,
						'message' => 'Success',
					)
				);
				throw new Exception( 'Error' );
			} catch ( Exception $e ) {
				header( 'Status: 500 Server Error' );
			}
		}
	}

	protected static function deleteOrdersFiles( $ids ) {

		$orders = Orders::get_by_ids( $ids );

		foreach ( $orders as $order ) {
			$details = json_decode( $order['order_details'] );
			foreach ( $details as $detail ) {
				if ( preg_replace( '/_field_id.*/', '', $detail->alias ) === 'file_upload' && isset( $detail->options ) && 'null' !== $detail->options ) {
					$file_list      = json_decode( $detail->options );
					$file_path_list = array_column( $file_list, 'file' );
					array_walk(
						$file_path_list,
						function ( $path ) {
							wp_delete_file( $path );
						}
					);
				}
			}
		}
	}

	public static function delete() {
		check_ajax_referer( 'ccb_delete_order', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$ids = ! empty( $_POST['ids'] ) ? sanitize_text_field( $_POST['ids'] ) : null;
		$ids = explode( ',', $ids );
		$d   = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		try {
			/** Delete order files if exist */
			self::deleteOrdersFiles( $ids );

			/** Delete orders */
			Orders::delete_orders( $d, $ids );
			Payments::delete_payments_by_order_ids( $ids );

			foreach ( $ids as $id ) {
				delete_option( 'calc_meta_data_order_' . $id );
			}

			do_action( 'ccb_after_delete_order', $ids );
			wp_send_json(
				array(
					'status'  => 200,
					'message' => 'success',
				)
			);
		} catch ( Exception $e ) {
			$an_error = true;
			header( 'Status: 500 Server Error' );
		}
	}

	public static function completeOrderById( $id ) {
		$id = sanitize_text_field( $id );

		try {
			Orders::complete_order_by_id( $id );
			wp_send_json(
				array(
					'status'  => 200,
					'message' => 'Success',
				)
			);
			throw new Exception( 'Error' );
		} catch ( Exception $e ) {
			header( 'Status: 500 Server Error' );
		}
	}

	public static function orders() {
		check_ajax_referer( 'ccb_orders', 'nonce' );

		$calc_list = CCBCalculators::get_calculator_list();

		$calc_id_list = array_map(
			function ( $item ) {
				return $item['id'];
			},
			$calc_list['existing']
		);

		$calculators = Orders::existing_calcs();

		if ( empty( $calculators ) ) {
			wp_send_json(
				array(
					'data'        => array(),
					'total_count' => 0,
					'calc_list'   => $calculators,
				)
			);
			exit();
		}

		$default_payment_types  = '';
		$default_payment_status = array();
		$default_calc_ids       = array_map(
			function ( $cal ) {
				return $cal['calc_id'];
			},
			$calculators
		);

		if ( ! empty( $_GET['status'] ) && 'all' !== $_GET['status'] ) {
			$default_payment_status = sanitize_text_field( $_GET['status'] );
		}

		if ( ! empty( $_GET['calc_id'] ) && 'all' !== $_GET['calc_id'] ) {
			$default_calc_ids = (int) $_GET['calc_id'];
		}

		if ( ! empty( $_GET['payment'] ) && 'all' !== $_GET['payment'] ) {
			$default_payment_types = sanitize_text_field( $_GET['payment'] );
		}

		$email     = '';
		$calc_name = '';
		$order_id  = '';

		if ( ! empty( $_GET['searchType'] ) && ! empty( $_GET['searchInput'] ) ) {
			$search_type = $_GET['searchType'];
			if ( 'email' === $search_type ) {
				$email = $_GET['searchInput'];
			} elseif ( 'calc_name' === $search_type ) {
				$calc_name = $_GET['searchInput'];
			} else {
				$order_id = $_GET['searchInput'];
			}
		}

		$page      = ! empty( $_GET['page'] ) ? (int) sanitize_text_field( $_GET['page'] ) : 1;
		$limit     = ! empty( $_GET['limit'] ) ? sanitize_text_field( $_GET['limit'] ) : 5;
		$order_by  = ! empty( $_GET['sortBy'] ) ? sanitize_sql_orderby( $_GET['sortBy'] ) : sanitize_sql_orderby( 'total' );
		$sorting   = ! empty( $_GET['direction'] ) ? sanitize_sql_orderby( strtoupper( $_GET['direction'] ) ) : sanitize_sql_orderby( 'ASC' );
		$offset    = 1 === $page ? 0 : ( $page - 1 ) * $limit;
		$start     = ! empty( $_GET['startDate'] ) ? sanitize_text_field( $_GET['startDate'] ) : '';
		$end       = ! empty( $_GET['endDate'] ) ? sanitize_text_field( $_GET['endDate'] ) : '';
		$limit_off = ! empty( $_GET['limit_off'] ) ? true : false;

		try {
			$query_args = array(
				'payment_method' => $default_payment_types,
				'payment_status' => $default_payment_status,
				'calc_ids'       => $default_calc_ids,
				'orderBy'        => $order_by,
				'sorting'        => $sorting,
				'email'          => $email,
				'calc_name'      => $calc_name,
				'order_id'       => $order_id,
				'limit'          => (int) $limit,
				'offset'         => (int) $offset,
				'start'          => $start,
				'end'            => $end,
				'limit_off'      => $limit_off,
			);

			$query_args = apply_filters( 'ccb_orders_list_query', $query_args );

			$total  = Orders::get_total_orders( $query_args );
			$orders = Orders::get_all_orders( $query_args );

			$result = array();
			foreach ( $orders as $order ) {
				$form_details          = json_decode( $order['form_details'] )->fields;
				$order['calc_deleted'] = false;

				if ( ! in_array( $order['calc_id'], $calc_id_list ) ) { //phpcs:ignore
					$order['calc_deleted'] = true;
				}

				foreach ( $form_details as $detail ) {
					if ( 'email' === $detail->name || 'your-email' === $detail->name ) {
						$order['user_email'] = $detail->value;
					}
				}

				if ( isset( $order['promocodes'] ) ) {
					$order['promocodes'] = json_decode( $order['promocodes'] );
				}

				$order['order_details'] = json_decode( $order['order_details'] );
				$order['order_details'] = array_map(
					function( $detail ) {
						if ( ! empty( $detail->options ) && preg_replace( '/_field_id.*/', '', $detail->alias ) === 'file_upload' ) {
							$detail->options = json_decode( $detail->options );
						}
						return $detail;
					},
					$order['order_details']
				);

				$order['decimal_separator']   = '';
				$order['thousands_separator'] = '';
				$order['num_after_integer']   = '';

				$order['wc_link']           = '';
				$order['paymentMethodType'] = esc_html__( 'No Payment', 'cost-calculator-builder' );

				if ( 'stripe' === $order['paymentMethod'] ) {
					$order['paymentMethodType'] = '<img class="ccb-logo ccb-logo-stripe" src="' . esc_url( CALC_URL . '/frontend/dist/img/stripe.svg' ) . '">';
				}

				if ( 'twoCheckout' === $order['paymentMethod'] ) {
					$order['paymentMethodType'] = '<img class="ccb-logo ccb-logo-twoCheckout" style="max-width: 60px" src="' . esc_url( CALC_URL . '/frontend/dist/img/twoCheckout.png' ) . '">';
				}

				if ( 'razorpay' === $order['paymentMethod'] ) {
					$order['paymentMethodType'] = 'Razorpay';
				}

				if ( 'paypal' === $order['paymentMethod'] ) {
					$order['paymentMethodType'] = '<img class="ccb-logo ccb-logo-paypal" src="' . esc_url( CALC_URL . '/frontend/dist/img/paypal.svg' ) . '">';
				}

				if ( 'cash_payment' === $order['paymentMethod'] ) {
					$order['paymentMethodType'] = esc_html__( 'Cash Payment', 'cost-calculator-builder' );
				}

				if ( 'woocommerce' === $order['paymentMethod'] && ! empty( $order['transaction'] ) ) {
					$order['wc_link'] = get_edit_post_link( $order['transaction'] );
				}

				$settings         = CCBSettingsData::get_calc_single_settings( $order['calc_id'] );
				$general_settings = CCBSettingsData::get_calc_global_settings();
				if ( isset( $general_settings['currency'] ) && ! empty( $general_settings['currency']['use_in_all'] ) ) {
					$order['decimal_separator']   = $general_settings['currency']['decimal_separator'];
					$order['thousands_separator'] = $general_settings['currency']['thousands_separator'];
					$order['num_after_integer']   = $general_settings['currency']['num_after_integer'];
					$order['currency_position']   = $general_settings['currency']['currencyPosition'];
					$order['paymentCurrency']     = $general_settings['currency']['currency'];
				} else {
					if ( ! empty( $settings['currency'] ) ) {
						if ( array_key_exists( 'decimal_separator', $settings['currency'] ) ) {
							$order['decimal_separator'] = $settings['currency']['decimal_separator'];
						}
						if ( array_key_exists( 'thousands_separator', $settings['currency'] ) ) {
							$order['thousands_separator'] = $settings['currency']['thousands_separator'];
						}
						if ( array_key_exists( 'num_after_integer', $settings['currency'] ) ) {
							$order['num_after_integer'] = $settings['currency']['num_after_integer'];
						}
						if ( array_key_exists( 'currencyPosition', $settings['currency'] ) ) {
							$order['currency_position'] = $settings['currency']['currencyPosition'];
						}
					}
				}

				$order['date_formatted'] = date( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $order['created_at'] ) );
				$order['totals']         = array();
				$order_meta_id           = 'calc_meta_data_order_' . $order['id'];
				$meta_data               = get_option( $order_meta_id, array() );

				$totals = $meta_data['totals'];
				if ( isset( $meta_data['totals'] ) && is_string( $meta_data['totals'] ) ) {
					$totals = json_decode( $meta_data['totals'] );
				}

				if ( isset( $meta_data['otherTotals'] ) && is_string( $meta_data['otherTotals'] ) ) {
					$otherTotals          = json_decode( $meta_data['otherTotals'] );
					$order['otherTotals'] = $otherTotals;
				}

				$order['totals'] = $totals;

				$order['form_details'] = json_decode( $order['form_details'] );

				$order['show_delete']        = apply_filters( 'ccb_show_delete_order', true );
				$order['show_change_status'] = apply_filters( 'ccb_show_change_status', true );
				$result[]                    = $order;

			}
			$show_bulk_actions = apply_filters( 'ccb_show_bulk_actions', true );

			wp_send_json(
				array(
					'data'              => $result,
					'total_count'       => $total,
					'calc_list'         => $calculators,
					'show_bulk_actions' => $show_bulk_actions,
				)
			);

			throw new Exception( 'Error' );
		} catch ( Exception $e ) {
			header( 'Status: 500 Server Error' );
		}
	}

	public static function get_orders_by_id( $id ) {
		$calc_list    = CCBCalculators::get_calculator_list();
		$calc_id_list = array_map(
			function ( $item ) {
				return $item['id'];
			},
			$calc_list['existing']
		);

		$calculators = Orders::existing_calcs();

		if ( empty( $calculators ) ) {
			return array();
		}

		$orders = Orders::get_order_by_id(
			array( 'id' => $id )
		);

		$result = array();
		foreach ( $orders as $order ) {
			$form_details          = json_decode( $order['form_details'] )->fields;
			$order['calc_deleted'] = false;

			if ( ! in_array( $order['calc_id'], $calc_id_list ) ) { //phpcs:ignore
				$order['calc_deleted'] = true;
			}

			foreach ( $form_details as $detail ) {
				if ( 'email' === $detail->name || 'your-email' === $detail->name ) {
					$order['user_email'] = $detail->value;
				}
			}

			$order['order_details'] = json_decode( $order['order_details'] );
			$order['order_details'] = array_map(
				function( $detail ) {
					if ( preg_replace( '/_field_id.*/', '', $detail->alias ) === 'file_upload' && isset( $detail->options ) ) {
						$detail->options = json_decode( $detail->options );
					}
					return $detail;
				},
				$order['order_details']
			);

			$order['decimal_separator']   = '';
			$order['thousands_separator'] = '';
			$order['num_after_integer']   = '';

			$order['wc_link']           = '';
			$order['paymentMethodType'] = esc_html__( 'No Payment', 'cost-calculator-builder' );

			if ( 'stripe' === $order['paymentMethod'] ) {
				$order['paymentMethodType'] = '<img class="ccb-logo ccb-logo-stripe" src="' . esc_url( CALC_URL . '/frontend/dist/img/stripe.svg' ) . '">';
			}

			if ( 'twoCheckout' === $order['paymentMethod'] ) {
				$order['paymentMethodType'] = '<img class="ccb-logo ccb-logo-twoCheckout" style="max-width: 60px" src="' . esc_url( CALC_URL . '/frontend/dist/img/twoCheckout.png' ) . '">';
			}

			if ( 'twoCheckout' === $order['paymentMethod'] ) {
				$order['paymentMethodType'] = 'Razorpay';
			}

			if ( 'paypal' === $order['paymentMethod'] ) {
				$order['paymentMethodType'] = '<img class="ccb-logo ccb-logo-paypal" src="' . esc_url( CALC_URL . '/frontend/dist/img/paypal.svg' ) . '">';
			}

			if ( 'cash_payment' === $order['paymentMethod'] ) {
				$order['paymentMethodType'] = esc_html__( 'Cash Payment', 'cost-calculator-builder' );
			}

			if ( 'woocommerce' === $order['paymentMethod'] && ! empty( $order['transaction'] ) ) {
				$order['wc_link'] = get_edit_post_link( $order['transaction'] );
			}

			$settings = CCBSettingsData::get_calc_single_settings( $order['calc_id'] );
			if ( array_key_exists( 'decimal_separator', $settings['currency'] ) ) {
				$order['decimal_separator'] = $settings['currency']['decimal_separator'];
			}
			if ( array_key_exists( 'thousands_separator', $settings['currency'] ) ) {
				$order['thousands_separator'] = $settings['currency']['thousands_separator'];
			}
			if ( array_key_exists( 'num_after_integer', $settings['currency'] ) ) {
				$order['num_after_integer'] = $settings['currency']['num_after_integer'];
			}

			$order['date_formatted'] = date( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), strtotime( $order['created_at'] ) );

			$order['form_details'] = json_decode( $order['form_details'] );
			$result[]              = $order;

		}

		if ( ! empty( $result ) ) {
			return convertStdClassToArray( $result[0] );
		}

		return array();
	}
}

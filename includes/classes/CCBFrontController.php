<?php

namespace cBuilder\Classes;

use cBuilder\Classes\Appearance\CCBAppearanceHelper;
use cBuilder\Helpers\CCBFieldsHelper;
use function Clue\StreamFilter\fun;

$template_variables = array();

class CCBFrontController {
	public static function init() {
		add_action(
			'wp_enqueue_scripts',
			function () {
				wp_enqueue_script( 'jquery' );
			}
		);
		add_shortcode( 'stm-calc', array( self::class, 'render_calculator' ) );
		add_shortcode( 'stm-thank-you-page', array( self::class, 'render_thank_you_page' ) );
		add_shortcode( 'stm-sticky-calc', array( self::class, 'ccb_render_sticky_calc' ) );
		add_filter( 'ccb_order_data_by_id', array( self::class, 'get_order_data_by_id' ) );
		add_filter( 'wp_footer', array( self::class, 'ccb_render_sticky_calc' ) );
	}

	public static function ccb_render_sticky_calc() {
		echo self::ccb_sticky_calc_handler(); // phpcs:ignore
	}

	public static function ccb_sticky_calc_handler() {
		global $post;
		$content = '';
		if ( $post ) {
			$content = $post->post_content;
		}

		$is_woocommerce = false;
		if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			$is_woocommerce = true;
		}

		if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
			return '';
		}

		$calculators   = CCBUpdatesCallbacks::get_calculators();
		$sticky_calc   = '';
		$has_sticky    = false;
		$calc_sticky   = '';
		$sticky_banner = '';

		$positions = array(
			'top_left'      => 0,
			'center_left'   => 0,
			'bottom_left'   => 0,
			'top_center'    => 0,
			'bottom_center' => 0,
			'top_right'     => 0,
			'center_right'  => 0,
			'bottom_right'  => 0,
		);

		$extra_content   = '';
		$global_settings = CCBSettingsData::get_calc_global_settings();
		foreach ( array_reverse( $calculators ) as $calculator ) {
			$calc_settings = CCBSettingsData::get_calc_single_settings( $calculator->ID );
			if ( isset( $calc_settings['sticky_calc'] ) && ! empty( $calc_settings['sticky_calc']['enable'] ) && ccb_pro_active() ) {
				if ( ! $has_sticky ) {
					$has_sticky  = true;
					$calc_sticky = '<div id="ccb-sticky-floating-wrapper">';

					wp_enqueue_style( 'ccb-sticky-css', CALC_URL . '/frontend/dist/css/sticky.css', array(), CALC_VERSION );
					wp_enqueue_style( 'ccb-bootstrap-css', CALC_URL . '/frontend/dist/css/modal.bootstrap.css', array(), CALC_VERSION );
					wp_enqueue_script( 'ccb-velocity-ui-js', CALC_URL . '/frontend/dist/libs/velocity.ui.min.js', array(), CALC_VERSION, true );
					wp_enqueue_script( 'ccb-velocity-ui-js', CALC_URL . '/frontend/dist/libs/velocity.ui.min.js', array(), CALC_VERSION, true );
					wp_enqueue_script( 'ccb-sticky-js', CALC_URL . '/frontend/dist/sticky.js', array(), CALC_VERSION, true );
				}

				$page_id           = get_the_ID();
				$not_allowed_pages = array();
				$sticky_settings   = $calc_settings['sticky_calc'];

				$position_type = '';
				if ( 'btn' === $sticky_settings['display_type'] ) {
					$position_type = $sticky_settings['btn_position'];
				}

				if ( ( isset( $positions[ $position_type ] ) && $positions[ $position_type ] < 2 ) || empty( $position_type ) ) {

					foreach ( $sticky_settings['pages'] as $page ) {
						$not_allowed_pages[] = intval( $page['id'] );
					}

					if ( ! in_array( intval( $page_id ), $not_allowed_pages, true ) ) {
						$action  = self::check_action( $sticky_settings['one_click_action'] ?? '', $calc_settings, $global_settings );
						$actions = array( 'open_modal', 'woo_product_as_modal', 'pro_features' );

						$title                           = get_post_meta( $calculator->ID, 'stm-name', true );
						$sticky_settings['calc_title']   = $title;
						$sticky_settings['woo_checkout'] = $calc_settings['woo_checkout'];
						$sticky_settings['woo_products'] = $calc_settings['woo_products'];
						$sticky_settings['invoice']      = $global_settings['invoice'];
						$sticky_settings['post_id']      = $post->ID ?? null;

						wp_localize_script(
							'ccb-sticky-js',
							'ccb_sticky_data',
							array(
								'title'        => $title,
								'the_id'       => get_the_ID(),
								'calc_id'      => $calculator->ID,
								'sticky_calc'  => $sticky_settings,
								'translations' => CCBTranslations::get_frontend_translations(),
								'currency'     => ccb_parse_settings( $calc_settings ),
							)
						);

						if ( isset( $sticky_settings['one_click_action'] ) ) {
							$sticky_settings['one_click_action'] = $action;
						}

						$sticky_content = \cBuilder\Classes\CCBTemplate::load(
							'/frontend/sticky',
							array(
								'calc_id'      => $calculator->ID,
								'the_id'       => get_the_ID(),
								'translations' => CCBTranslations::get_frontend_translations(),
								'sticky_calc'  => $sticky_settings,
								'position'     => $positions[ $position_type ] ?? '',
							)
						);

						$calc_content = '';

						if ( in_array( $action, $actions, true ) ) {
							$calc_content = \cBuilder\Classes\CCBTemplate::load(
								'/frontend/partials/sticky-modal',
								array(
									'calc_id' => $calculator->ID,
									'the_id'  => get_the_ID(),
									'action'  => $action,
								)
							);
						}

						if ( ! $is_woocommerce && in_array( $action, array( 'pdf', 'invoice' ), true ) && ! str_contains( $content, 'stm-calc id="' . $calculator->ID . '"' ) ) {
							$sticky_calc .= do_shortcode( "[stm-calc id='" . esc_attr( $calculator->ID ) . "' custom='1' hidden='1']" );
						} elseif ( ! $is_woocommerce && 'woo_checkout' === $action && ! str_contains( $content, 'stm-calc id="' . $calculator->ID . '"' ) ) {
							$sticky_calc .= do_shortcode( "[stm-calc id='" . esc_attr( $calculator->ID ) . "' custom='1' hidden='1']" );
						}

						$actions     = array( 'open_modal', 'scroll_to', 'woo_checkout', 'pdf', 'invoice', 'pro_features' );
						$woo_actions = array( 'woo_product_as_modal', 'woo_product_with_redirect' );

						if ( ! ( ( $is_woocommerce && in_array( $action, $actions, true ) ) || ( ! $is_woocommerce && in_array( $action, $woo_actions, true ) ) ) && 'banner' === $sticky_settings['display_type'] ) {
							$sticky_banner  = $sticky_content;
							$sticky_content = '';
						}

						if ( ( $is_woocommerce && in_array( $action, $actions, true ) ) || ( ! $is_woocommerce && in_array( $action, $woo_actions, true ) ) ) {
							$calc_content = '';
						} else {
							$calc_sticky = $calc_sticky . $sticky_content;
						}

						$extra_content .= $calc_content;
					}

					if ( isset( $positions[ $position_type ] ) ) {
						$positions[ $position_type ]++;
					}
				}
			}
		}

		$close_tag = '';
		if ( $has_sticky ) {
			$close_tag = '</div>';
		}

		$calc_sticky .= $sticky_banner . $extra_content . $close_tag;

		return $sticky_calc . $calc_sticky;
	}

	/**
	 * todo all template params must be here in controller
	 */
	public static function render_calculator( $attr ) {
		$data   = array( 'id' => null, 'sticky' => null, 'custom' => null, 'action' => '', 'hidden' => null ); //phpcs:ignore
		$params = shortcode_atts( $data, $attr );
		return self::render_calculator_handler( $params['id'], $params['sticky'], $params['custom'], $params['action'], $params['hidden'] );
	}

	/**
	 * todo all template params must be here in controller
	 */
	public static function render_calculator_handler( $calc_id, $sticky = null, $custom = null, $custom_action = null, $hidden = null ) {
		wp_enqueue_script( 'cbb-phone-js', CALC_URL . '/frontend/dist/libs/vue/phone/vue-phone-number-input.umd.js', array(), CALC_VERSION, true );
		wp_enqueue_script( 'cbb-resize-sensor-js', CALC_URL . '/frontend/dist/sticky/ResizeSensor.js', array(), CALC_VERSION, true );
		wp_enqueue_script( 'cbb-sticky-sidebar-js', CALC_URL . '/frontend/dist/sticky/sticky-sidebar.js', array( 'cbb-resize-sensor-js' ), CALC_VERSION, true );

		wp_enqueue_style( 'ccb-icons-list', CALC_URL . '/frontend/dist/css/icon/style.css', array(), CALC_VERSION );
		wp_enqueue_style( 'calc-builder-app', CALC_URL . '/frontend/dist/css/style.css', array(), CALC_VERSION );

		if ( ! empty( $calc_id ) && get_post( $calc_id ) ) {
			$id            = apply_filters( 'wpml_object_id', $calc_id, 'cost-calc', true );
			$calc_settings = CCBSettingsData::get_calculator_settings( $calc_id );
			$fields        = self::getCalculatorFields( $calc_id, $calc_settings );
			$settings      = $calc_settings['settings'];
			$language      = substr( get_bloginfo( 'language' ), 0, 2 );
			$extra_style   = '';

			$ccb_sync             = ccb_sync_settings_from_general_settings( $settings, $calc_settings['general_settings'], true );
			$settings             = $ccb_sync['settings'];
			$general_settings     = $ccb_sync['general_settings'];
			$sticky_calc_settings = $settings['sticky_calc'] ?? array();

			$is_woocommerce = false;
			if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
				$is_woocommerce = true;
			}

			$action = self::check_action( $sticky_calc_settings['one_click_action'] ?? '', $settings, $general_settings );
			if ( isset( $settings['sticky_calc']['one_click_action'] ) ) {
				$settings['sticky_calc']['one_click_action'] = $action;
			}

			if ( ! $is_woocommerce && ccb_pro_active() && ! empty( $sticky_calc_settings['enable'] ) ) {
				$sticky_calc_actions = array( 'open_modal' );

				if ( in_array( $action, $sticky_calc_actions, true ) && is_null( $sticky ) ) {
					return '';
				}

				$sticky_calc_actions = array( 'woo_checkout', 'pdf', 'invoice', 'pro_features' );
				if ( $hidden || ( empty( $sticky_calc_settings['show_calculator'] ) && in_array( $action, $sticky_calc_actions, true ) && is_null( $sticky ) ) ) {
					$extra_style = 'ccb-calc-hidden';
				}
			}

			if ( $is_woocommerce && ! empty( $settings['woo_products']['enable'] ) && function_exists( 'is_product' ) && is_product() ) {
				if ( ccb_pro_active() && ! empty( $sticky_calc_settings['enable'] ) && CCBWooProducts::is_category_included_or_is_product_included( $settings['woo_products'] ) && ! CCBWooProducts::product_is_in_out_of_stock() ) {
					$sticky_calc_actions = array( 'woo_product_as_modal' );

					if ( in_array( $action, $sticky_calc_actions, true ) && is_null( $sticky ) ) {
						return '';
					}

					$sticky_calc_actions = array( 'woo_product_with_redirect' );
					if ( empty( $sticky_calc_settings['show_calculator'] ) && in_array( $action, $sticky_calc_actions, true ) && is_null( $sticky ) ) {
						$extra_style = 'ccb-calc-hidden';
					}
				}
			}

			$custom_sticky = is_null( $sticky ) ? $custom : $sticky;
			self::ccb_add_custom_data( $calc_id, $custom_sticky, $settings, $general_settings, $custom_action );

			$templates       = \cBuilder\Helpers\CCBFieldsHelper::get_fields_templates( $settings, $general_settings );
			$order_templates = \cBuilder\Helpers\CCBFieldsHelper::get_order_fields_templates( $settings, $general_settings );
			$payments        = array();

			if ( ccb_pro_active() ) {
				$payments = \cBuilder\Classes\CCBProSettings::get_payments();
			}

			wp_enqueue_script( 'calc-builder-main-js', CALC_URL . '/frontend/dist/bundle.js', array( 'cbb-sticky-sidebar-js' ), CALC_VERSION, true );
			wp_localize_script(
				'calc-builder-main-js',
				'ajax_window',
				array(
					'ajax_url'         => admin_url( 'admin-ajax.php' ),
					'language'         => $language,
					'templates'        => $templates,
					'order_templates'  => $order_templates,
					'pro_active'       => ccb_pro_active(),
					'the_id'           => get_the_ID(),
					'payments'         => $payments,
					'general_settings' => $general_settings,
				)
			);

			return \cBuilder\Classes\CCBTemplate::load(
				'/frontend/render',
				array(
					'sticky'           => $sticky,
					'calc_id'          => $id,
					'language'         => $language,
					'translations'     => CCBTranslations::get_frontend_translations(),
					'settings'         => $settings,
					'general_settings' => $calc_settings['general_settings'],
					'fields'           => $fields,
					'extra_style'      => $extra_style,
				)
			);
		}

		return '<p style="text-align: center">' . __( 'No selected calculator', 'cost-calculator-builder' ) . '</p>';
	}

	private static function getCalculatorFields( $calc_id, $calc_settings ) {
		$fields = get_post_meta( $calc_id, 'stm-fields', true ) ?? array();

		if ( ! empty( $fields ) ) {
			array_walk(
				$fields,
				function ( &$field_value, $k ) use ( $calc_settings ) {

					/** set wc prooduct meta type */
					$field_value['wc_product_meta_link'] = '';
					if ( $calc_settings['settings']['woo_products']['enable'] && array_key_exists( 'alias', $field_value ) ) {
						$key = array_search( $field_value['alias'], array_column( $calc_settings['settings']['woo_products']['meta_links'], 'calc_field' ), true );
						if ( false !== $key ) {
							$field_value['wc_product_meta_link'] = $calc_settings['settings']['woo_products']['meta_links'][ $key ]['woo_meta'];
						}
					}
					/** set wc prooduct meta type |end */

					if ( array_key_exists( 'required', $field_value ) ) {
						$field_value['required'] = $field_value['required'] ? 'true' : 'false';
					}
				}
			);
		}
		return $fields;
	}

	public static function render_thank_you_page( $attr ) {

		wp_enqueue_script( 'cbb-resize-sensor-js', CALC_URL . '/frontend/dist/sticky/ResizeSensor.js', array(), CALC_VERSION, true );
		wp_enqueue_script( 'cbb-sticky-sidebar-js', CALC_URL . '/frontend/dist/sticky/sticky-sidebar.js', array( 'cbb-resize-sensor-js' ), CALC_VERSION, true );

		wp_enqueue_style( 'ccb-icons-list', CALC_URL . '/frontend/dist/css/icon/style.css', array(), CALC_VERSION );
		wp_enqueue_style( 'calc-builder-app', CALC_URL . '/frontend/dist/css/style.css', array(), CALC_VERSION );
		wp_enqueue_script( 'ccb-lodash-js', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js', array(), CALC_VERSION, true );
		wp_add_inline_script( 'ccb-lodash-js', 'window.ccb_lodash = window.ccb_lodash ? window.ccb_lodash : _.noConflict();' );

		$params   = shortcode_atts( array( 'id' => null ), $attr );
		$language = substr( get_bloginfo( 'language' ), 0, 2 );

		wp_enqueue_script( 'calc-builder-main-js', CALC_URL . '/frontend/dist/bundle.js', array( 'ccb-lodash-js', 'cbb-sticky-sidebar-js' ), CALC_VERSION, true );
		wp_localize_script(
			'calc-builder-main-js',
			'ajax_window',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'language'   => $language,
				'templates'  => CCBFieldsHelper::get_fields_templates( array(), array() ),
				'pro_active' => ccb_pro_active(),
			)
		);

		if ( isset( $params['id'] ) && get_post( $params['id'] ) ) {
			$order_data = array();
			$order_id   = isset( $_GET['order_id'] ) ? (int) $_GET['order_id'] : null;
			$calc_id    = null;
			if ( ! is_null( $order_id ) ) {
				$order_data = apply_filters( 'ccb_order_data_by_id', $order_id );
				if ( isset( $order_data['calc_id'] ) ) {
					$calc_id = $order_data['calc_id'];
				}
			}

			$id = apply_filters( 'wpml_object_id', $calc_id, 'cost-calc', true );

			if ( defined( 'CCB_PRO' ) ) {
				return \cBuilder\Classes\CCBProTemplate::load(
					'/frontend/partials/custom-thank-you-page',
					array(
						'calc_id'      => $id,
						'order_id'     => $order_id,
						'language'     => $language,
						'order_data'   => $order_data,
						'translations' => CCBTranslations::get_frontend_translations(),
					)
				);
			}
		}

		return '';
	}

	public static function get_order_data_by_id( $id ) {
		if ( $id ) {
			$meta_data = get_option( 'calc_meta_data_order_' . $id, array() );
			$ccb_order = CCBOrderController::get_orders_by_id( $id );

			$totals    = json_decode( $meta_data['totals'], true );
			$discounts = array();
			if ( is_array( $totals ) && count( $totals ) > 0 ) {
				foreach ( $totals as $total ) {
					if ( ! empty( $total['hasDiscount'] ) ) {
						$discounts[] = $total['discount'];
					}
				}
			}

			return array(
				'id'            => $ccb_order['id'],
				'calc_id'       => $ccb_order['calc_id'],
				'orderDetails'  => $ccb_order['order_details'],
				'formDetails'   => $ccb_order['form_details'],
				'paymentMethod' => $ccb_order['paymentMethod'],
				'orderDate'     => $ccb_order['date_formatted'],
				'converted'     => $meta_data['converted'],
				'totals'        => $totals,
				'promocodes'    => $ccb_order['promocodes'] ?? array(),
				'discounts'     => $discounts,
			);
		}

		return array();
	}

	public static function ccb_add_custom_data( $calc_id, $sticky, $settings, $general_settings, $action ) {
		$preset_key = get_post_meta( $calc_id, 'ccb_calc_preset_idx', true );
		$preset_key = empty( $preset_key ) ? 0 : $preset_key;
		$appearance = CCBAppearanceHelper::get_appearance_data( $preset_key );
		$loader_idx = 0;

		if ( ! empty( $appearance ) ) {
			$appearance = $appearance['data'];

			if ( isset( $appearance['desktop']['others']['data']['calc_preloader']['value'] ) ) {
				$loader_idx = $appearance['desktop']['others']['data']['calc_preloader']['value'];
			}
		}

		$settings['calc_id'] = $calc_id;
		$settings['title']   = get_post_meta( $calc_id, 'stm-name', true );

		$template_params = array(
			'calc_id'          => $calc_id,
			'loader_idx'       => $loader_idx,
			'settings'         => $settings,
			'general_settings' => $general_settings,
			'action'           => $action,
		);

		$template_variables['template'] = \cBuilder\Classes\CCBTemplate::load( 'frontend/partials/calc-builder', $template_params ); // phpcs:ignore

		if ( ! empty( $settings['formFields']['contactFormId'] ) ) {
			$template_variables['cf7_form'] = do_shortcode( '[contact-form-7 id="' . $settings['formFields']['contactFormId'] . '"]' );
		}

		$is_elementor = false;
		if ( class_exists( '\Elementor\Plugin' ) ) {
			$document = \Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $document && $document->is_built_with_elementor() ) {
				$is_elementor = true;
			}
		}

		if ( ! empty( $sticky ) || $is_elementor || defined( '__BREAKDANCE_VERSION' ) ) {
			echo '<script type="text/javascript">window["ccb_front_template_' . $calc_id . '"] = ' . json_encode( $template_variables ) . ';</script>'; // phpcs:ignore
		} else {
			add_action( 'wp_footer', function () use ( $calc_id, $template_params, $template_variables ) { // phpcs:ignore
				echo ( '<script type="text/javascript">window["ccb_front_template_' . $calc_id . '"] = ' . json_encode( $template_variables ) . ';</script>' ); //phpcs:ignore
			}); // phpcs:ignore
		}
	}

	private static function check_action( $action, $settings, $global_settings ) {
		if ( 'pro_features' === $action ) {
			$access_email = empty( $settings['formFields']['accessEmail'] );
			$woo_action   = ( empty( $settings['woo_checkout']['enable'] ) || empty( $settings['woo_products']['enable'] ) );

			$payment_gateway = $settings['payment_gateway'];
			$paypal          = empty( $payment_gateway['paypal']['enable'] );
			$cash_payment    = empty( $payment_gateway['cash_payment']['enable'] );
			$card_payments   = ( empty( $payment_gateway['cards']['card_payments']['stripe']['enable'] ) || empty( $payment_gateway['cards']['card_payments']['razorpay']['enable'] ) );

			if ( $access_email && $woo_action && $paypal && $cash_payment && $card_payments ) {
				$action = 'open_modal';
			}
		}

		if ( in_array( $action, array( 'pdf', 'invoice' ), true ) && ( empty( $global_settings['invoice']['use_in_all'] ) || ( 'invoice' === $action && empty( $global_settings['invoice']['emailButton'] ) ) ) ) {
			$action = 'open_modal';
		}

		if ( in_array( $action, array( 'woo_checkout', 'woo_product_with_redirect' ), true ) && empty( $settings['woo_checkout']['enable'] ) ) {
			$action = 'open_modal';
		}

		return $action;
	}
}

<?php
// TODO mv all logic to controller
use cBuilder\Classes\Appearance\CCBAppearanceHelper;
use cBuilder\Classes\CCBSettingsData;

if ( ! isset( $calc_id ) ) {
	return;
}

if ( ! empty( $_GET['token'] ) && ccb_pro_active() ) {
	\cBuilder\Classes\Payments\CCBPayPal::captureOrder( $_GET['token'], $calc_id );
}

/** if language not set, use en as default */
if ( ! isset( $language ) ) {
	$language = 'en';
}

if ( ! isset( $translations ) ) {
	$translations = array();
}

$container_style = 'v-container';

if ( ! isset( $settings ) ) {
	$settings = CCBSettingsData::get_calc_single_settings( $calc_id );
}

if ( ! isset( $general_settings ) ) {
	$general_settings = CCBSettingsData::get_calc_global_settings();
}

$ccb_sync         = ccb_sync_settings_from_general_settings( $settings, $general_settings, true );
$settings         = $ccb_sync['settings'];
$general_settings = $ccb_sync['general_settings'];

if ( ! empty( $settings ) && isset( $settings[0] ) && isset( $settings[0]['general'] ) ) {
	$settings = $settings[0];
}

if ( empty( $settings['general'] ) ) {
	$settings = \cBuilder\Classes\CCBSettingsData::settings_data();
}

$settings['calc_id'] = $calc_id;
$settings['title']   = get_post_meta( $calc_id, 'stm-name', true );

if ( ! empty( $general_settings['payment_gateway']['cards']['use_in_all'] ) && ! empty( $general_settings['payment_gateway']['cards']['card_payments']['razorpay']['enable'] ) ) {
	$settings['payment_gateway']['cards']['card_payments']['razorpay']['keyId']     = $general_settings['payment_gateway']['cards']['card_payments']['razorpay']['keyId'];
	$settings['payment_gateway']['cards']['card_payments']['razorpay']['secretKey'] = $general_settings['payment_gateway']['cards']['card_payments']['razorpay']['secretKey'];
}

if ( ! empty( $general_settings['payment_gateway']['cards']['use_in_all'] ) && ! empty( $general_settings['payment_gateway']['cards']['card_payments']['stripe']['enable'] ) ) {
	$settings['payment_gateway']['cards']['card_payments']['stripe']['publishKey'] = $general_settings['payment_gateway']['cards']['card_payments']['stripe']['publishKey'];
	$settings['payment_gateway']['cards']['card_payments']['stripe']['secretKey']  = $general_settings['payment_gateway']['cards']['card_payments']['stripe']['secretKey'];
}

if ( ! empty( $settings['formFields']['body'] ) ) {
	$settings['formFields']['body'] = str_replace( '<br>', PHP_EOL, $settings['formFields']['body'] );
}

if ( ! empty( $settings['thankYouPage']['page_id'] ) ) {
	$page_id = $settings['thankYouPage']['page_id'];
	$page    = get_post( $page_id );

	$pos = strpos( $page->post_content ?? '', 'stm-thank-you-page' );
	if ( false === $pos ) {
		$content      = $page->post_content ?? '';
		$updated_page = array(
			'ID'           => $page_id,
			'post_content' => $content . '[stm-thank-you-page id="' . $calc_id . '"]',
		);

		wp_update_post( $updated_page );
	}


	$settings['thankYouPage']['page_url'] = get_permalink( $settings['thankYouPage']['page_id'] );
}

if ( ! empty( $settings['formFields'] ) && ! empty( $settings['texts']['form_fields'] ) ) {
	$settings['texts']['form_fields'] = apply_filters( 'ccb_contact_form_add_text_form_fields', $settings['texts']['form_fields'] );
}

if ( ! empty( $general_settings['form_fields']['use_in_all'] ) && ! empty( $general_settings['form_fields']['summary_display']['use_in_all'] ) ) {
	$settings['formFields']['summary_display']           = $general_settings['form_fields']['summary_display'];
	$settings['formFields']['summary_display']['enable'] = true;
}

if ( ! empty( $settings['formFields']['accessEmail'] ) && ! empty( $settings['formFields']['contactFormId'] ) ) {
	$settings['formFields']['summary_display']['enable'] = '';
}

if ( ! empty( $settings['formFields']['submitBtnText'] ) ) {
	$settings['formFields']['submitBtnText'] = apply_filters( 'ccb_contact_form_submit_label', $settings['formFields']['submitBtnText'], $calc_id );
}

$settings['thankYouPage'] = apply_filters( 'ccb_customize_confirmation_page', $settings['thankYouPage'], $calc_id );
$preset_key               = get_post_meta( $calc_id, 'ccb_calc_preset_idx', true );
$preset_key               = empty( $preset_key ) ? 0 : $preset_key;
$appearance               = CCBAppearanceHelper::get_appearance_data( $preset_key );

if ( ! empty( $appearance ) ) {
	$appearance = $appearance['data'];
}

$fields = get_post_meta( $calc_id, 'stm-fields', true ) ?? array();
if ( ! empty( $fields ) ) {
	array_walk(
		$fields,
		function ( &$field_value, $k ) {
			if ( array_key_exists( 'required', $field_value ) ) {
				$field_value['required'] = $field_value['required'] ? 'true' : 'false';
			}
		}
	);
}

$form_id     = get_post_meta( $calc_id, 'form_id', true );
$form_fields = array();
$form_data   = array();
if ( '' !== $form_id ) {
	$form_fields = \cBuilder\Classes\Database\FormFields::get_active_fields( $form_id );
	$form_data   = array(
		'form_id'   => $form_id,
		'form_name' => \cBuilder\Classes\Database\Forms::get_form_name( $form_id ),
	);
}


$geolocation = isset( $general_settings['geolocation'] ) ? $general_settings['geolocation'] : array();

if ( isset( $general_settings['invoice'] ) ) {
	$settings['invoice'] = array(
		'showAfterPayment' => $general_settings['invoice']['showAfterPayment'],
		'emailButton'      => $general_settings['invoice']['emailButton'],
	);
}

$data = array(
	'id'            => $calc_id,
	'settings'      => $settings,
	'currency'      => ccb_parse_settings( $settings ),
	'geolocation'   => $geolocation,
	'fields'        => $fields,
	'form_fields'   => $form_fields,
	'form_data'     => $form_data,
	'formula'       => get_post_meta( $calc_id, 'stm-formula', true ),
	'conditions'    => apply_filters( 'calc_render_conditions', array(), $calc_id ), // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	'language'      => $language,
	'appearance'    => $appearance,
	'dateFormat'    => get_option( 'date_format' ),
	'pro_active'    => ccb_pro_active(),
	'default_img'   => CALC_URL . '/frontend/dist/img/default.png',
	'error_img'     => CALC_URL . '/frontend/dist/img/error.png',
	'success_img'   => CALC_URL . '/frontend/dist/img/success.png',
	'translations'  => $translations,
	'discounts'     => \cBuilder\Classes\Database\Discounts::get_calc_active_discounts( $calc_id ),
	'has_promocode' => \cBuilder\Classes\Database\Discounts::has_active_promocode( $calc_id ),
);

$custom_defined = false;
if ( isset( $is_preview ) ) {
	$custom_defined = true;
}

if ( ( isset( $general_settings['payment_gateway']['cards']['card_payments'] ) && ! empty( $general_settings['payment_gateway']['cards']['card_payments']['use_in_all'] ) ) || ( isset( $settings['payment_gateway']['cards']['card_payments'] ) && ! empty( $settings['payment_gateway']['cards']['card_payments']['stripe']['enable'] ) ) ) {
	wp_enqueue_script( 'calc-stripe', 'https://js.stripe.com/v3/', array(), CALC_VERSION, false );
}

if ( ( isset( $general_settings['payment_gateway']['cards']['twoCheckout'] ) && ! empty( $general_settings['payment_gateway']['cards']['card_payments']['use_in_all'] ) ) || ( isset( $settings['payment_gateway']['cards']['card_payments'] ) && ! empty( $settings['payment_gateway']['cards']['card_payments']['twoCheckout']['enable'] ) ) ) {
	wp_enqueue_script( 'calc-twoCheckout', CALC_URL . '/frontend/dist/libs/2out.min.js', array(), CALC_VERSION, false );
}

if ( ( isset( $general_settings['payment_gateway']['cards']['razorpay'] ) && ! empty( $general_settings['payment_gateway']['cards']['card_payments']['use_in_all'] ) ) || ( isset( $settings['payment_gateway']['cards']['card_payments'] ) && ! empty( $settings['payment_gateway']['cards']['card_payments']['razorpay']['enable'] ) ) ) {
	wp_enqueue_script( 'calc-razorpay', 'https://checkout.razorpay.com/v1/checkout.js', null, null ); // phpcs:ignore
}

wp_localize_script( 'calc-builder-main-js', 'calc_data_' . $calc_id, $data );
?>

<div class="calculator-settings ccb-front ccb-wrapper-<?php echo esc_attr( $calc_id . ' ' . $extra_style ); ?>" data-calc-id="<?php echo esc_attr( $calc_id ); ?>">
	<calc-builder-front v-cloak custom="<?php echo esc_attr( $custom_defined ); ?>" :content="<?php echo esc_attr( wp_json_encode( $data, 0, JSON_UNESCAPED_UNICODE ) ); ?>"></calc-builder-front>
</div>

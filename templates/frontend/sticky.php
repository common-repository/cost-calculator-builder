<?php
$additional_classes = isset( $sticky_calc['classes'] ) ? strval( $sticky_calc['classes'] ) : '';
$positions          = isset( $sticky_calc['btn_position'] ) ? strval( $sticky_calc['btn_position'] ) : '';

if ( 0 === $position ) {
	$additional_classes = $additional_classes . ' allow-on-mobile';
}

if ( 'banner' === $sticky_calc['display_type'] ) {
	$additional_classes = $additional_classes . ' is-banner';
	$positions          = $sticky_calc['banner_position'] ?? '';
}
?>

<div class="ccb-sticky-calc ccb-calc-id-<?php echo esc_attr( $calc_id . ' ' . $additional_classes ); ?>" data-calc-id="<?php echo esc_attr( $calc_id ); ?>" data-position="<?php echo ! empty( $positions ) ? esc_attr( $positions ) : 'hidden'; ?>">
	<sticky-wrapper :settings="<?php echo esc_attr( wp_json_encode( $sticky_calc, 0, JSON_UNESCAPED_UNICODE ) ); ?>" :id="id" v-if="loaded"></sticky-wrapper>
</div>

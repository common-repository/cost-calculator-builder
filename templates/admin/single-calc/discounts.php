<?php
$modal_types = array(
	'preview' => array(
		'type' => 'preview',
		'path' => CALC_PATH . '/templates/admin/single-calc/modals/modal-preview.php',
	),
);
?>
<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
<div class="ccb-discount-available-in-pro calc-quick-tour-discounts calc-quick-tour-no-discounts">
	<div class="ccb-discount-in-pro-content ccb-content-text-large">
		<span class="ccb-discount-in-pro-title ccb-default-title large ccb-bold"><?php esc_html_e( 'Add Promo Codes and Give Discounts', 'cost-calculator-builder' ); ?></span>
		<span class="ccb-discount-in-pro-description ccb-default-title ccb-bold"><?php esc_html_e( '🔒 Available in PRO version', 'cost-calculator-builder' ); ?></span>
		<a href="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=wpadmin&utm_medium=promo_calc&utm_campaign=promo-codes-and-discounts" target="_blank" class="ccb-button ccb-href success">
			<?php esc_html_e( 'Upgrade Now', 'cost-calculator-builder' ); ?>
		</a>
	</div>
	<div class="ccb-discount-in-pro-image-wrapper">
		<img src="<?php echo esc_url( CALC_URL . '/frontend/dist/img/discounts/discounts-bg.webp' ); ?>" alt="discounts-banner">
	</div>
	<ccb-modal-window>
		<template v-slot:content>
			<?php foreach ( $modal_types as $m_type ) : ?>
				<template v-if="$store.getters.getModalType === '<?php echo esc_attr( $m_type['type'] ); ?>'">
					<?php require $m_type['path']; ?>
				</template>
			<?php endforeach; ?>
		</template>
	</ccb-modal-window>
</div>
<?php else : ?>
	<?php do_action( 'render-discounts' ); //phpcs:ignore ?>
<?php endif; ?>

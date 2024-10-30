<div class="ccb-tab-container">
	<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
		<settings-pro-banner
			title="<?php esc_html_e( 'Geolocation', 'cost-calculator-builder' ); ?>"
			subtitle="<?php esc_html_e( 'Available in PRO version', 'cost-calculator-builder' ); ?>"
			text="<?php esc_html_e( 'Let your users share their location or choose the pickup location', 'cost-calculator-builder' ); ?>"
			link="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=geolocation"
			img="<?php echo esc_attr( CALC_URL . '/frontend/dist/img/pro-features/map.webp' ); ?>"
			img-height="258px"
		/>
	<?php else : ?>
		<?php do_action( 'render-general-geolocation' ); //phpcs:ignore ?>
	<?php endif; ?>
</div>

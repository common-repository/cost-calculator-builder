<div class="ccb-tab-container">
	<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
		<settings-pro-banner
			title="<?php esc_html_e( 'AI Formula', 'cost-calculator-builder' ); ?>"
			subtitle="<?php esc_html_e( 'Available in PRO version', 'cost-calculator-builder' ); ?>"
			text="<?php esc_html_e( 'You can generate formula for different calculators with AI helper.', 'cost-calculator-builder' ); ?>"
			link="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcadmin&utm_medium=upgradenow&utm_campaign=aiformula"
			img="<?php echo esc_url( CALC_URL . '/frontend/dist/img/pro-features/ai.webp' ); ?>"
			img-height="443px"
		/>
	<?php else : ?>
		<?php do_action( 'render-ai' ); //phpcs:ignore ?>
	<?php endif; ?>
</div>

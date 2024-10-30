<div class="ccb-tab-container">
	<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
		<?php
		$check_list = array(
			'Stays on the screen while user scroll',
			'Choose how it looks like',
			'Pick where it shows up',
			'With one click, the calculator opens up',
		);
		?>
		<div class="ccb-grid-box">
			<div class="container">
				<span class="ccb-tab-title" style="font-size: 18px; margin-bottom: 18px;">Order form</span>
				<single-pro-banner
					link="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=sticky_calculator"
					img="<?php echo esc_attr( esc_url( CALC_URL . '/frontend/dist/img/pro-features/sticky-calc.webp' ) ); ?>"
					width="657px"
					list='<?php echo wp_json_encode( $check_list ); ?>'
				/>
			</div>
		</div>
	<?php else : ?>
		<?php do_action( 'render-sticky-calculator' ); //phpcs:ignore ?>
	<?php endif; ?>
</div>

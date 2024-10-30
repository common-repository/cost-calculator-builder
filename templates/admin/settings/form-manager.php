<div class="ccb-tab-container">
	<?php if ( ! defined( 'CCB_PRO' ) ) : ?>
		<?php
			$title          = __( 'Custom Order Form', 'cost-calculator-builder' );
			$pro_title      = __( 'Available in PRO version', 'cost-calculator-builder' );
			$paragraph_list = array(
				__(
					'Order form for the calculator helps you collect and process customer orders. Add fields for contact information, shipping addresses, reward cards, and biometric data. Choose custom elements like text area, dropdown, radio button and more. You can adjust the length and layout and change the appearance of the fields.',
					'cost-calculator-builder'
				),
				__( 'Create a custom order form that perfectly fits your business needs.', 'cost-calculator-builder' ),
			);
			$img_list       = array(
				esc_url( CALC_URL . '/frontend/dist/img/pro-features/order-form-3.webp' ),
				esc_url( CALC_URL . '/frontend/dist/img/pro-features/order-form-2.webp' ),
				esc_url( CALC_URL . '/frontend/dist/img/pro-features/order-form-1.webp' ),
			);
			?>
		<div class="ccb-grid-box">
			<div class="container">
				<custom-pro-banner
					link="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcwpadmin&utm_medium=freetoprobutton&utm_campaign=send_form"
					:img-list='<?php echo wp_json_encode( $img_list ); ?>' 
					img="<?php echo esc_attr( esc_url( CALC_URL . '/frontend/dist/img/pro-features/img-orderform.webp' ) ); ?>"
					width="657px"
					title="<?php echo esc_attr( $title ); ?>"
					pro-title="<?php echo esc_attr( $pro_title ); ?>"
					list='<?php echo wp_json_encode( $paragraph_list ); ?>'
					video="https://youtu.be/0nP4aIX6-HI"
				/>
			</div>
		</div>
	<?php else : ?>
		<?php do_action( 'render-form-manager' ); //phpcs:ignore ?>
	<?php endif; ?>
</div>

<div class="ccb-tab-container">
	<div class="ccb-grid-box">
		<div class="container">
			<div class="row">
				<div class="col">
					<span class="ccb-tab-title"><?php esc_html_e( 'Error message in calculator', 'cost-calculator-builder' ); ?></span>
				</div>
			</div>
			<div class="row <?php echo esc_attr( defined( 'CCB_PRO_VERSION' ) ? 'ccb-p-t-15' : '' ); ?>">
				<div class="col col-6">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Required field notice', 'cost-calculator-builder' ); ?></span>
						<input type="text" placeholder="<?php esc_attr_e( 'Enter notice', 'cost-calculator-builder' ); ?>" v-model="settingsField.texts.required_msg">
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php if ( ! defined( 'CCB_PRO_VERSION' ) ) : ?>
		<div class="ccb-grid-box">
			<div class="container">
				<div class="row">
					<div class="col">
						<span class="ccb-tab-title"><?php esc_html_e( 'Error message in order forms', 'cost-calculator-builder' ); ?></span>
					</div>
				</div>
				<div class="ccb-single-pro-feature" style="max-width:400px; margin-top: 15px;">
					<div class="ccb-single-pro-feature__header" style="border:none;">
						<div class="ccb-single-pro-feature__image-box">
							<span class="ccb-single-pro-feature--icon-box">
								<i class="ccb-icon-Lock"></i>
							</span>
							<span style="font-size:14px; font-weight:500;">This feature is available in the Pro version</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( defined( 'CCB_PRO_VERSION' ) ) : ?>
		<div class="ccb-grid-box">
			<div class="container">
				<div class="row">
					<div class="col">
						<span class="ccb-tab-title"><?php esc_html_e( 'Error message in order forms', 'cost-calculator-builder' ); ?></span>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col col-3">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Email format notice', 'cost-calculator-builder' ); ?></span>
							<input type="text" placeholder="<?php esc_attr_e( 'Enter notice', 'cost-calculator-builder' ); ?>" v-model="settingsField.texts.form_fields.email_format">
						</div>
					</div>
					<div class="col col-3">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Email required notice', 'cost-calculator-builder' ); ?></span>
							<input type="text" placeholder="<?php esc_attr_e( 'Enter notice', 'cost-calculator-builder' ); ?>" v-model="settingsField.texts.form_fields.email_field">
						</div>
					</div>
					<div class="col col-3">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Name required notice', 'cost-calculator-builder' ); ?></span>
							<input type="text" placeholder="<?php esc_attr_e( 'Enter notice', 'cost-calculator-builder' ); ?>" v-model="settingsField.texts.form_fields.name_field">
						</div>
					</div>
					<div class="col col-3">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Phone required notice', 'cost-calculator-builder' ); ?></span>
							<input type="text" placeholder="<?php esc_attr_e( 'Enter notice', 'cost-calculator-builder' ); ?>" v-model="settingsField.texts.form_fields.phone_field">
						</div>
					</div>
					<div class="col col-3">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Terms required notice', 'cost-calculator-builder' ); ?></span>
							<input type="text" placeholder="<?php esc_attr_e( 'Enter notice', 'cost-calculator-builder' ); ?>" v-model="settingsField.texts.form_fields.terms_and_conditions_field">
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

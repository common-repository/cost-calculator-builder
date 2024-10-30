<div class="cbb-edit-field-container">
	<div class="ccb-edit-field-header">
		<span class="ccb-edit-field-title ccb-heading-3 ccb-bold"><?php esc_html_e( 'Basic slider', 'cost-calculator-builder' ); ?></span>
		<div class="ccb-field-actions">
			<button class="ccb-button default" @click="$emit( 'cancel' )"><?php esc_html_e( 'Cancel', 'cost-calculator-builder' ); ?></button>
			<button class="ccb-button success" @click.prevent="save(rangeField, id, index, rangeField.alias)"><?php esc_html_e( 'Save', 'cost-calculator-builder' ); ?></button>
		</div>
	</div>
	<div class="ccb-grid-box">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="ccb-edit-field-switch">
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'main'}" @click="tab = 'main'">
							<?php esc_html_e( 'Element', 'cost-calculator-builder' ); ?>
							<span class="ccb-fields-required" v-if="errorsCount > 0">{{ errorsCount }}</span>
						</div>
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'options'}" @click="tab = 'options'">
							<?php esc_html_e( 'Settings', 'cost-calculator-builder' ); ?>
						</div>
					</div>
				</div>
			</div>
			<template v-if="tab === 'main'">
				<div class="row ccb-p-t-15">
					<div class="col-12">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Name', 'cost-calculator-builder' ); ?></span>
							<input type="text" class="ccb-heading-5 ccb-light" v-model.trim="rangeField.label" placeholder="<?php esc_attr_e( 'Enter field name', 'cost-calculator-builder' ); ?>">
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-12">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Description', 'cost-calculator-builder' ); ?></span>
							<input type="text" class="ccb-heading-5 ccb-light" v-model.trim="rangeField.description" placeholder="<?php esc_attr_e( 'Enter field description', 'cost-calculator-builder' ); ?>">
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-6">
						<div class="ccb-input-wrapper number">
							<span class="ccb-input-label"><?php esc_html_e( 'Minimum Range Value', 'cost-calculator-builder' ); ?></span>
							<div class="ccb-input-box">
								<input type="text" class="ccb-heading-5 ccb-light" :class="{'ccb-input-required': isObjectHasPath(errors, ['minValue'] ) && errors.minValue}" name="minValue" min="0" step="1" @input="() => fixErrorByKey('minValue')" v-model="rangeField.minValue" placeholder="<?php esc_attr_e( 'Enter min range', 'cost-calculator-builder' ); ?>">
								<span @click="numberCounterAction('minValue')" class="input-number-counter up"></span>
								<span @click="numberCounterAction('minValue', '-')" class="input-number-counter down"></span>
							</div>
							<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['minValue'] ) && errors.minValue" v-html="errors.minValue"></span>
						</div>
					</div>
					<div class="col-6">
						<div class="ccb-input-wrapper number">
							<span class="ccb-input-label"><?php esc_html_e( 'Maximum Range Value', 'cost-calculator-builder' ); ?></span>
							<div class="ccb-input-box">
								<input type="text" class="ccb-heading-5 ccb-light" :class="{'ccb-input-required': isObjectHasPath(errors, ['maxValue'] ) && errors.maxValue}" name="maxValue" min="0"  step="1" @input="() => fixErrorByKey('maxValue')" v-model="rangeField.maxValue" placeholder="<?php esc_attr_e( 'Enter max range', 'cost-calculator-builder' ); ?>">
								<span @click="numberCounterAction('maxValue')" class="input-number-counter up"></span>
								<span @click="numberCounterAction('maxValue', '-')" class="input-number-counter down"></span>
							</div>
							<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['maxValue'] ) && errors.maxValue" v-html="errors.maxValue"></span>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-6">
						<div class="ccb-input-wrapper number">
							<span class="ccb-input-label"><?php esc_html_e( 'Range Step', 'cost-calculator-builder' ); ?></span>
							<div class="ccb-input-box">
								<input type="text" class="ccb-heading-5 ccb-light" :class="{'ccb-input-required': isObjectHasPath(errors, ['step'] ) && errors.step}" name="step" min="0" step="1" @input="() => fixErrorByKey('step')" v-model="rangeField.step" placeholder="<?php esc_attr_e( 'Enter step', 'cost-calculator-builder' ); ?>">
								<span @click="numberCounterAction('step')" class="input-number-counter up"></span>
								<span @click="numberCounterAction('step', '-')" class="input-number-counter down"></span>
							</div>
							<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['step'] ) && errors.step" v-html="errors.step"></span>
						</div>
					</div>
					<div class="col-6">
						<div class="ccb-input-wrapper number">
							<span class="ccb-input-label"><?php esc_html_e( 'Range Default Value', 'cost-calculator-builder' ); ?></span>
							<div class="ccb-input-box">
								<input type="text" class="ccb-heading-5 ccb-light" name="default" min="0" step="1" @input="errors.default=false" v-model="rangeField.default" placeholder="<?php esc_attr_e( 'Enter default value', 'cost-calculator-builder' ); ?>">
								<span @click="numberCounterAction('default')" class="input-number-counter up"></span>
								<span @click="numberCounterAction('default', '-')" class="input-number-counter down"></span>
							</div>
							<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['default'] ) && errors.default" v-html="errors.default"></span>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-6">
						<div class="ccb-input-wrapper" :class="{ 'disabled': !rangeField.multiply && rangeField.allowCurrency || rangeField.fieldCurrency }">
							<span class="ccb-input-label"><?php esc_html_e( 'Name of value (kg, gr, pcs)', 'cost-calculator-builder' ); ?></span>
							<input type="text" maxlength="18" class="ccb-heading-5 ccb-light" v-model.trim="rangeField.sign" placeholder="<?php esc_attr_e( 'Enter unit symbol', 'cost-calculator-builder' ); ?>">
						</div>
					</div>
					<div class="col-6">
						<div class="ccb-disable-msg" v-if="!rangeField.multiply && rangeField.allowCurrency">
							<span><?php esc_html_e( 'Currency sign is ON', 'cost-calculator-builder' ); ?></span>
						</div>
						<div class="ccb-select-box" style="padding-top: 27px;" v-else>
							<div class="ccb-select-wrapper">
								<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
								<select class="ccb-select" v-model="rangeField.unitPosition">
									<option value="right" selected><?php esc_html_e( 'On the right', 'cost-calculator-builder' ); ?></option>
									<option value="left"><?php esc_html_e( 'On the left', 'cost-calculator-builder' ); ?></option>
								</select>
							</div>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-6">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.multiply"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Multiply (cost per value)', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15" v-if="rangeField.multiply">
					<div class="col-12">
						<div class="list-header">
							<div class="ccb-multiply">
								<span class="ccb-multiply__bg">=</span>
								<span class="ccb-multiply__bg"><?php esc_html_e( 'Selected value', 'cost-calculator-builder' ); ?></span>
								<span class="ccb-multiply__icon"><i class="ccb-icon-close"></i></span>
								<div class="ccb-input-wrapper number">
									<div class="ccb-input-box">
										<input type="text" class="ccb-heading-5 ccb-light" name="unit" min="1" step="1" @keypress="unitMinValue" v-model="rangeField.unit" placeholder="<?php esc_attr_e( 'Enter unit', 'cost-calculator-builder' ); ?>">
										<span @click="numberCounterAction('unit')" class="input-number-counter up"></span>
										<span @click="numberCounterAction('unit', '-')" class="input-number-counter down"></span>
									</div>
									<span class="ccb-error-tip default" v-if="isObjectHasPath(errors, ['unit'] ) && errors.unit" v-html="errors.unit"></span>
								</div>
								<div class="ccb-input-wrapper" style="margin-left: 10px; width: 160px;" :class="{'disabled': rangeField.fieldCurrency || rangeField.allowCurrency }">
									<input type="text" maxlength="18" class="ccb-heading-5 ccb-light" v-model.trim="rangeField.unitSymbol" placeholder="<?php esc_attr_e( 'Unit (kg, cm,...)', 'cost-calculator-builder' ); ?>">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15" v-if="rangeField.multiply">
					<div class="col-12">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.multipliedTotal"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Show multiplied value instead of subtotal', 'cost-calculator-builder' ); ?></h6>
							<span class="ccb-help-tip-block" style="margin-top: 2px;">
								<span class="ccb-help-label ccb-help-label-range" ><?php esc_html_e( 'Preview', 'cost-calculator-builder' ); ?></span>
								<span class="ccb-help ccb-help-settings" style="left: -50%; bottom: 20px;">
									<span class="ccb-help-content">
										<img src="<?php echo esc_url( CALC_URL . '/frontend/dist/img/preview/range.png' ); ?>" alt="Show multiplied value instead of subtotal">
									</span>
								</span>
							</span>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15" v-if="errorsCount > 0">
					<div class="col-12">
						<div class="ccb-notice ccb-error">
							<span class="ccb-notice-title"><?php esc_html_e( 'Not Saved!', 'cost-calculator-builder' ); ?></span>
							<span class="ccn-notice-description"><?php esc_html_e( 'Options tab contains errors, check the fields!', 'cost-calculator-builder' ); ?></span>
						</div>
					</div>
				</div>
			</template>
			<template v-else>
				<div class="row ccb-p-t-15">
					<div class="col-6 ccb-p-t-10">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.allowCurrency"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Currency Sign', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
					<div class="col-6 ccb-p-t-10">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.allowRound"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Round Value', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
					<div class="col-6 ccb-p-t-10" v-if="!disableFieldHiddenByDefault(rangeField)">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.hidden"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Hidden by Default', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
					<div class="col-6 ccb-p-t-10" v-if="!disableFieldHiddenByDefault(rangeField)">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.calculateHidden"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Calculate hidden by default', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
					<div class="col-6 ccb-p-t-10">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.addToSummary"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Show in Grand Total', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
					<?php if ( ccb_pro_active() ) : ?>
						<div class="col-6 ccb-p-t-10">
							<div class="list-header">
								<div class="ccb-switch">
									<input type="checkbox" v-model="rangeField.required"/>
									<label></label>
								</div>
								<h6 class="ccb-heading-5"><?php esc_html_e( 'Required', 'cost-calculator-builder' ); ?></h6>
							</div>
						</div>
					<?php endif; ?>
					<div class="col-6 ccb-p-t-10">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="rangeField.fieldCurrency"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Add a measuring unit', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
				</div>
				<div class="row row-currency" :class="{'disabled': !rangeField.fieldCurrency}">
				<div class="col-4">
					<div class="ccb-input-wrapper">
						<span class="ccb-input-label"><?php esc_html_e( 'Unit Symbol', 'cost-calculator-builder' ); ?></span>
						<input type="text" maxlength="18" v-model="fieldCurrency.currency" placeholder="<?php esc_attr_e( 'Enter unit symbol', 'cost-calculator-builder' ); ?>">
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-select-box">
						<span class="ccb-select-label"><?php esc_html_e( 'Position', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-select-wrapper">
							<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
							<select class="ccb-select" v-model="fieldCurrency.currencyPosition">
								<option value="left"><?php esc_html_e( 'Left', 'cost-calculator-builder' ); ?></option>
								<option value="right"><?php esc_html_e( 'Right', 'cost-calculator-builder' ); ?></option>
								<option value="left_with_space"><?php esc_html_e( 'Left with space', 'cost-calculator-builder' ); ?></option>
								<option value="right_with_space"><?php esc_html_e( 'Right with space', 'cost-calculator-builder' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-select-box">
						<span class="ccb-select-label"><?php esc_html_e( 'Thousands separator', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-select-wrapper">
							<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
							<select class="ccb-select" v-model="fieldCurrency.thousands_separator">
								<option value=","><?php esc_html_e( ' Comma ', 'cost-calculator-builder' ); ?></option>
								<option value="."><?php esc_html_e( ' Dot ', 'cost-calculator-builder' ); ?></option>
								<option value="'"><?php esc_html_e( ' Apostrophe ', 'cost-calculator-builder' ); ?></option>
								<option value=" "><?php esc_html_e( ' Space ', 'cost-calculator-builder' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-input-wrapper number">
						<span class="ccb-input-label"><?php esc_html_e( 'Number of decimals', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-input-box">
							<input type="number" name="option_num_after_integer" v-model="fieldCurrency.num_after_integer" min="1" max="8" placeholder="<?php esc_attr_e( 'Enter decimals', 'cost-calculator-builder' ); ?>">
							<span class="input-number-counter up" @click="numberCounterAction('num_after_integer')"></span>
							<span class="input-number-counter down" @click="numberCounterAction('num_after_integer', '-')"></span>
						</div>
					</div>
				</div>
				<div class="col-4">
					<div class="ccb-select-box">
						<span class="ccb-select-label"><?php esc_html_e( 'Decimal separator', 'cost-calculator-builder' ); ?></span>
						<div class="ccb-select-wrapper">
							<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
							<select class="ccb-select" v-model="fieldCurrency.decimal_separator">
								<option value=","><?php esc_html_e( ' Comma ', 'cost-calculator-builder' ); ?></option>
								<option value="."><?php esc_html_e( ' Dot ', 'cost-calculator-builder' ); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
				<div class="row ccb-p-t-15">
					<div class="col-12">
						<div class="ccb-input-wrapper">
							<span class="ccb-input-label"><?php esc_html_e( 'Additional Classes', 'cost-calculator-builder' ); ?></span>
							<textarea class="ccb-heading-5 ccb-light" v-model="rangeField.additionalStyles" placeholder="<?php esc_attr_e( 'Set Additional Classes', 'cost-calculator-builder' ); ?>"></textarea>
						</div>
					</div>
				</div>
			</template>
		</div>
	</div>
</div>

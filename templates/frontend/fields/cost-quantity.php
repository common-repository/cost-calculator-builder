<?php
/**
 * @file
 * Cost-quantity component's template
 */
?>

<div :style="additionalCss" class="calc-item ccb-field ccb-field-quantity ccb-fields-tooltip" :class="{required: requiredWrapperActive, [quantityField.additionalStyles]: quantityField.additionalStyles}" :data-id="quantityField.alias" :data-repeater="repeater">
	<div class="calc-item__title">
		<span v-if="quantityField.required" :class="{active: requiredActive}" class="ccb-error-tip front default" style="bottom: 47px">{{ $store.getters.getSettings.texts.required_msg }}</span>
		<span v-if="Object.keys(errors).length > 0" class="ccb-error-tip front default active" style="bottom: 47px">
			<span v-if="errors.hasOwnProperty('max')"><?php esc_html_e( 'Value can\'t be greater than ', 'cost-calculator-builder' ); ?> {{ quantityField.max }}</span>
			<span v-if="errors.hasOwnProperty('min')"><?php esc_html_e( 'Value can\'t be less than ', 'cost-calculator-builder' ); ?> {{ quantityField.min }}</span>
			<span v-if="errors.hasOwnProperty('min_max')"><?php esc_html_e( 'Value must be between ', 'cost-calculator-builder' ); ?>{{ quantityField.min }} - {{ quantityField.max }}</span>
		</span>
		<span> {{ quantityField.label }} </span>
		<span class="ccb-required-mark" v-if="quantityField.required">*</span>
	</div>

	<div class="calc-item__description before">
		<span v-text="quantityField.description"></span>
	</div>

	<div :class="['calc-input-wrapper ccb-field', 'calc_' + quantityField.alias]">
		<input @focusout="parseField" @keypress="intValueFilter($event)" name="quantityField" type="text" v-model="quantityValue" @focus="$event.target.select()" :placeholder="quantityField.placeholder" class="calc-input number ccb-field ccb-appearance-field">
		<span @click="increment" class="input-number-counter up">
			<i class="ccb-icon-Path-3486"></i>
		</span>
		<span @click="decrement" class="input-number-counter down">
			<i class="ccb-icon-Path-3485"></i>
		</span>
	</div>
	<div v-if="showMinMaxQuantity" class="calc-item__description after">
		<span v-if="hasMinValue">
			<?php esc_html_e( 'Min', 'cost-calculator-builder' ); ?>: {{ minText }}
		</span>
		<span v-if="hasMinMaxValues"> - </span>
		<span v-if="hasMaxValue">
			<?php esc_html_e( 'Max', 'cost-calculator-builder' ); ?>: {{ maxText }}
		</span>
	</div>

	<div class="calc-item__description after">
		<span v-text="quantityField.description"></span>
	</div>
</div>

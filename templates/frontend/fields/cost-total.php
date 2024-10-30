<?php
/**
 * @file
 * Cost-total component's template
 */
?>


<div :class="[totalField?.additionalStyles, 'sub-list-item total']" :id="field?.alias" v-show="isFieldVisible(field)" :style="getWrapperStyles">
	<template v-if="isFieldWithoutDiscount(field)">
		<span class="sub-item-title">{{ field.label === 'Total' ? '<?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?>' : field.label }}</span>
		<span class="sub-item-value" style="white-space: nowrap">{{ field.converted }}</span>
	</template>
	<template v-else-if="shouldApplyDiscount(field, discount)">
		<template v-if="getDiscountViewType === 'show_without_title'">
			<span class="sub-item-title">
				<span class="ccb-discount-label">{{ field.label === 'Total' ? '<?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?>' : field.label }}:</span>
				<span class="ccb-discount-off">{{ getDiscountAmount }} <?php esc_html_e( 'off', 'cost-calculator-builder' ); ?></span>
			</span>
			<span class="ccb-discount-wrapper">
				<span class="sub-item-value ccb-discount" style="white-space: nowrap">{{ discount.original_converted }}</span>
				<span class="sub-item-value" style="white-space: nowrap">{{ field.converted }}</span>
			</span>
		</template>
		<template v-else-if="getDiscountViewType === 'show_with_title'">
			<div class="sub-item-inner">
				<span class="sub-item-title">
					<span class="ccb-discounts-inner-label"><?php esc_html_e( 'Discount', 'cost-calculator-builder' ); ?>: </span>
					<span class="ccb-discount-title">{{ getDiscountTitle }}</span>
				</span>
				<span class="sub-item-value" style="white-space: nowrap">{{ getDiscountValue }}</span>
			</div>

			<div class="sub-item-inner">
				<span class="sub-item-title">{{ field.label === 'Total' ? '<?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?>' : field.label }}</span>
				<span class="sub-item-value" style="white-space: nowrap">{{ field.converted }}</span>
			</div>
		</template>
	</template>
</div>

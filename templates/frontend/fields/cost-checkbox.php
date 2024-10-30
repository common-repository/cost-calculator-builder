<?php
/**
 * @file
 * Cost-checkbox component's template
 */
?>
<div :style="additionalCss" class="calc-item ccb-field ccb-fields-tooltip" :class="{required: requiredActive, [checkboxField.additionalStyles]: checkboxField.additionalStyles}" :data-id="checkboxField.alias" :data-repeater="repeater">
	<component :is="getStyle" :repeater="repeater" :field="checkboxField" @update="updateValue" :value="value"></component>
</div>

<?php
/**
 * @file
 * Cost-quantity component's template
 */
?>
<div :style="additionalCss" class="calc-item ccb-field ccb-fields-tooltip" :class="{required: requiredActive, [radioField.additionalStyles]: radioField.additionalStyles}" :data-id="radioField.alias" :data-repeater="repeater">
	<component :is="getStyle" :repeater="repeater" :field="radioField" :value="value" @update="updateValue"></component>
</div>

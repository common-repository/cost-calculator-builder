<?php
/**
 * @file
 * Cost-toggle component's template
 */
?>

<div :style="additionalCss" class="calc-item ccb-field ccb-fields-tooltip" :class="{required: requiredActive, [toggleField.additionalStyles]: toggleField.additionalStyles}" :data-id="toggleField.alias" :data-repeater="repeater">
	<component :is="getStyle" :repeater="repeater" :field="toggleField" @update="updateValue" :value="value"></component>
</div>

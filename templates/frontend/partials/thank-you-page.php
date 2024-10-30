<component :is="getWrapper" :order="getOrder" :settings="getSettings">
	<?php echo \cBuilder\Classes\CCBProTemplate::load( 'frontend/partials/thank-you-page', array( 'invoice' => $general_settings['invoice'] ) ); // phpcs:ignore ?>
</component>

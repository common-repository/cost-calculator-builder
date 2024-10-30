<div class="ccb-sticky-modal modal fade modal-lg <?php echo esc_attr( $action ?? '' ); ?>" id="ccb-sticky-calc-modal-<?php echo esc_attr( $calc_id ?? '' ); ?>" role="dialog" tabindex="-1" data-easein="slideLeftBigIn" aria-labelledby="calcModal" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content" style="overflow-y: auto">
			<div class="modal-body">
				<?php echo do_shortcode( "[stm-calc id='" . esc_attr( $calc_id ?? '' ) . "' sticky='true' action='" . esc_attr( $action ?? '' ) . "']" ); ?>
			</div>
		</div>
	</div>
</div>

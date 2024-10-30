<?php

namespace cBuilder\Classes;

class CCBTranslations {

	/**
	 * Frontend Translation Data
	 *
	 * @return array
	 */
	public static function get_frontend_translations() {

		$translations = array(
			'empty_end_date_error'   => esc_html__( 'Please select the second date', 'cost-calculator-builder' ),
			'wrong_date_range_error' => esc_html__( 'Please select correct date range values', 'cost-calculator-builder' ),
			'empty_end_time_error'   => esc_html__( 'Please select the second time', 'cost-calculator-builder' ),
			'required_field'         => esc_html__( 'This field is required', 'cost-calculator-builder' ),
			'select_date_range'      => esc_html__( 'Select Date Range', 'cost-calculator-builder' ),
			'select_date'            => esc_html__( 'Select Date', 'cost-calculator-builder' ),
			'select_all'             => esc_html__( 'All dates', 'cost-calculator-builder' ),
			'select_discount_range'  => esc_html__( 'Set discount period', 'cost-calculator-builder' ),
			'select_discount_single' => esc_html__( 'Choose the day', 'cost-calculator-builder' ),
			'high_end_date_error'    => esc_html__( 'To date must be greater than from date', 'cost-calculator-builder' ),
			'high_end_multi_range'   => esc_html__( 'To value must be greater than from value', 'cost-calculator-builder' ),
			'wrong_file_url'         => esc_html__( 'Wrong file url', 'cost-calculator-builder' ),
			'big_file_size'          => esc_html__( 'File size is too big', 'cost-calculator-builder' ),
			'wrong_file_format'      => esc_html__( 'Wrong file format', 'cost-calculator-builder' ),
			'form_no_payment'        => esc_html__( 'No Payment', 'cost-calculator-builder' ),
			'min_higher_max'         => esc_html__( 'Max value must be greater than min value', 'cost-calculator-builder' ),
			'must_be_between'        => esc_html__( 'Value must be between min and max values', 'cost-calculator-builder' ),
			'must_be_greater_min'    => esc_html__( 'Value can\'t be less than min value', 'cost-calculator-builder' ),
			'must_be_less_max'       => esc_html__( 'Value can\'t be greater than max value', 'cost-calculator-builder' ),
			'days'                   => esc_html__( 'days', 'cost-calculator-builder' ),
			'files'                  => esc_html__( 'file(s)', 'cost-calculator-builder' ),
			'order_created'          => esc_html__( 'Order created', 'cost-calculator-builder' ),
			'formula'                => array(
				'addition'              => esc_html__( 'Addition (+)', 'cost-calculator-builder' ),
				'subtraction'           => esc_html__( 'Subtraction (-)', 'cost-calculator-builder' ),
				'division'              => esc_html__( 'Division (/)', 'cost-calculator-builder' ),
				'remainder'             => esc_html__( 'Remainder (%)', 'cost-calculator-builder' ),
				'multiplication'        => esc_html__( 'Multiplication (*)', 'cost-calculator-builder' ),
				'open_bracket'          => esc_html__( 'Open bracket (', 'cost-calculator-builder' ),
				'close_bracket'         => esc_html__( 'Close bracket )', 'cost-calculator-builder' ),
				'math_pow'              => esc_html__( 'Math.pow(x, y) returns the value of x to the power of y:', 'cost-calculator-builder' ),
				'math_sqrt'             => esc_html__( 'Math.sqrt(x) returns the square root of x:', 'cost-calculator-builder' ),
				'math_abs'              => esc_html__( 'Math.abs(x)', 'cost-calculator-builder' ),
				'math_ceil'             => esc_html__( 'Math.ceil(x) returns the value of x rounded up to its nearest integer:', 'cost-calculator-builder' ),
				'math_min'              => esc_html__( 'Math.min(x, y) returns the value of x rounded down to its nearest integer:', 'cost-calculator-builder' ),
				'math_max'              => esc_html__( 'Math.max(x, y) returns the value of x rounded down to its nearest integer:', 'cost-calculator-builder' ),
				'math_floor'            => esc_html__( 'Math.floor(x) returns the value of x rounded down to its nearest integer:', 'cost-calculator-builder' ),
				'math_round'            => esc_html__( 'Math.round(x) returns the value of x rounded to its nearest integer:', 'cost-calculator-builder' ),
				'if_operator'           => esc_html__( 'If operator', 'cost-calculator-builder' ),
				'if_else_operator'      => esc_html__( 'If else operator', 'cost-calculator-builder' ),
				'boolean_and'           => esc_html__( 'Boolean operator ', 'cost-calculator-builder' ),
				'boolean_or'            => esc_html__( 'Boolean operator ||', 'cost-calculator-builder' ),
				'operator_more'         => esc_html__( 'Operator more than', 'cost-calculator-builder' ),
				'operator_less'         => esc_html__( 'Operator less than', 'cost-calculator-builder' ),
				'operator_less_equal'   => esc_html__( 'Operator less than equal', 'cost-calculator-builder' ),
				'operator_more_equal'   => esc_html__( 'Operator more than equal', 'cost-calculator-builder' ),
				'operator_not_equal'    => esc_html__( 'Operator not equal', 'cost-calculator-builder' ),
				'operator_strict_equal' => esc_html__( 'Operator strict equal', 'cost-calculator-builder' ),
			),
			'order_form'             => array(
				'no_form_to_delete' => esc_html__( 'There is only one form left and it can\'t be deleted', 'cost-calculator-builder' ),
				'delete_primary'    => esc_html__( 'To delete this field, enable the \'Primary mean of communication\' option in another email field.', 'cost-calculator-builder' ),
			),
			'phone_example'          => esc_html__( 'Example: ', 'cost-calculator-builder' ),
			'country_code'           => esc_html__( 'Country code ', 'cost-calculator-builder' ),
			'invalid_email'          => esc_html__( 'Invalid email', 'cost-calculator-builder' ),
			'invalid_url'            => esc_html__( 'Invalid url', 'cost-calculator-builder' ),
			'invalid_phone'          => esc_html__( 'Invalid phone number', 'cost-calculator-builder' ),
		);

		return $translations;
	}

	public static function get_backend_translations() {
		$translations = array(
			'bulk_action_attention'          => esc_html__( 'Are you sure to "%s" choosen Calculators?', 'cost-calculator-builder' ),
			'copied'                         => esc_html__( 'Copied', 'cost-calculator-builder' ),
			'not_selected_calculators'       => esc_html__( 'No calculators were selected', 'cost-calculator-builder' ),
			'select_bulk'                    => esc_html__( 'Select bulk action', 'cost-calculator-builder' ),
			'changes_saved'                  => esc_html__( 'Changes Saved', 'cost-calculator-builder' ),
			'calculator_deleted'             => esc_html__( 'Calculator Deleted', 'cost-calculator-builder' ),
			'calculator_duplicated'          => esc_html__( 'Calculator Duplicated', 'cost-calculator-builder' ),
			'condition_link_saved'           => esc_html__( 'Condition Link Saved', 'cost-calculator-builder' ),
			'required_field'                 => esc_html__( 'This field is required', 'cost-calculator-builder' ),
			'delete_order_info'              => esc_html__( 'You are going to delete order', 'cost-calculator-builder' ),
			'success_deleted'                => esc_html__( 'Items successfully deleted', 'cost-calculator-builder' ),
			'not_selected'                   => esc_html__( 'Please choose at least one value', 'cost-calculator-builder' ),
			'select_image'                   => esc_html__( 'Select an image', 'cost-calculator-builder' ),
			'find_element'                   => esc_html__( 'Find Element', 'cost-calculator-builder' ),
			'enter_title'                    => esc_html__( 'Enter title', 'cost-calculator-builder' ),
			'no_element'                     => esc_html__( 'No elements on  canvas', 'cost-calculator-builder' ),
			'all_in_canvas'                  => esc_html__( 'All', 'cost-calculator-builder' ),
			'triggers_other_field'           => esc_html__( 'Impact other fields', 'cost-calculator-builder' ),
			'affects_by_other_field'         => esc_html__( 'Affected by other fields', 'cost-calculator-builder' ),
			'format_error'                   => sprintf( '%s <br> %s', __( 'File format is not supported.', 'cost-calculator-builder' ), __( 'Supported file formats: JPG, PNG', 'cost-calculator-builder' ) ),
			'primary_means_of_communication' => esc_html__( 'Primary mean of communication', 'cost-calculator-builder' ),
			'order_no_payment'               => esc_html__( 'No Payment', 'cost-calculator-builder' ),
			'order_cash_payment'             => esc_html__( 'Cash Payment', 'cost-calculator-builder' ),
			'order_status_pending'           => esc_html__( 'Pending', 'cost-calculator-builder' ),
			'order_status_complete'          => esc_html__( 'Complete', 'cost-calculator-builder' ),
		);

		return $translations;
	}
}

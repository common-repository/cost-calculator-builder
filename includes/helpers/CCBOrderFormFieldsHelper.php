<?php
namespace cBuilder\Helpers;

class CCBOrderFormFieldsHelper {
	public static function order_form_fields() {
		return array(
			array(
				'name'        => __( 'Name', 'cost-calculator-builder-pro' ),
				'type'        => 'name',
				'icon'        => 'ccb-icon-Subtraction-7',
				'field_width' => '12',
				'attributes'  => array(
					'required'    => '0',
					'label'       => 'Name',
					'placeholder' => 'Type your name',
				),
			),
			array(
				'name'        => __( 'Email', 'cost-calculator-builder-pro' ),
				'type'        => 'email',
				'icon'        => 'ccb-icon-account-solid',
				'field_width' => '12',
				'attributes'  => array(
					'required'                 => '1',
					'label'                    => 'Email',
					'placeholder'              => 'Type your email',
					'confirmation'             => '0',
					'primary'                  => '0',
					'position'                 => 'Vertical',
					'confirmation_label'       => 'Confirm your email',
					'confirmation_placeholder' => '',
				),
			),
			array(
				'name'        => __( 'Phone', 'cost-calculator-builder-pro' ),
				'type'        => 'phone',
				'icon'        => 'ccb-icon-Icon-element',
				'field_width' => '12',
				'attributes'  => array(
					'required'    => '0',
					'label'       => 'Phone',
					'placeholder' => 'Type your phone',
				),
			),
			array(
				'name'        => __( 'Input textbox', 'cost-calculator-builder-pro' ),
				'type'        => 'input-textbox',
				'icon'        => 'ccb-icon-Subtraction-7',
				'field_width' => '12',
				'attributes'  => array(
					'required'        => '0',
					'label'           => 'Input textbox',
					'placeholder'     => 'Type your text',
					'character_limit' => '80',
				),
			),
			array(
				'name'        => __( 'Text area', 'cost-calculator-builder-pro' ),
				'type'        => 'textarea',
				'icon'        => 'ccb-icon-textarea',
				'field_width' => '12',
				'attributes'  => array(
					'required'        => '0',
					'label'           => 'Text area',
					'placeholder'     => 'Type your text',
					'character_limit' => '400',
				),
			),
			array(
				'name'        => __( 'Number', 'cost-calculator-builder-pro' ),
				'type'        => 'number',
				'icon'        => 'ccb-icon-Subtraction-6',
				'field_width' => '12',
				'attributes'  => array(
					'required'    => '0',
					'label'       => 'Number',
					'placeholder' => 'Type your number',
				),
			),
			array(
				'name'        => __( 'Dropdown', 'cost-calculator-builder-pro' ),
				'type'        => 'dropdown',
				'icon'        => 'ccb-icon-dropdown-2',
				'field_width' => '12',
				'attributes'  => array(
					'required'    => '0',
					'label'       => 'Dropdown',
					'placeholder' => 'Select an option',
					'options'     => array(
						array(
							'optionText' => 'Option 1',
						),
						array(
							'optionText' => 'Option 2',
						),
						array(
							'optionText' => 'Option 3',
						),
					),
					'multiselect' => '1',
				),
			),
			array(
				'name'        => __( 'Radio', 'cost-calculator-builder-pro' ),
				'type'        => 'radio',
				'icon'        => 'ccb-icon-Path-3511',
				'field_width' => '12',
				'attributes'  => array(
					'required' => '0',
					'label'    => 'This is the title of radio group',
					'options'  => array(
						array(
							'optionText' => 'Radio 1',
						),
						array(
							'optionText' => 'Radio 2',
						),
						array(
							'optionText' => 'Radio 3',
						),
					),
					'display'  => 'Vertical',
				),
			),
			array(
				'name'        => __( 'Checkbox', 'cost-calculator-builder-pro' ),
				'type'        => 'checkbox',
				'icon'        => 'ccb-icon-Path-3512',
				'field_width' => '12',
				'attributes'  => array(
					'required' => '0',
					'label'    => 'This is the title of checkbox group',
					'options'  => array(
						array(
							'optionText' => 'Checkbox 1',
						),
						array(
							'optionText' => 'Checkbox 2',
						),
						array(
							'optionText' => 'Checkbox 3',
						),
					),
					'display'  => 'Vertical',
				),
			),
			array(
				'name'        => __( 'Formatted text', 'cost-calculator-builder-pro' ),
				'type'        => 'formatted-text',
				'icon'        => 'ccb-icon-Path-3601',
				'field_width' => '12',
				'attributes'  => array(
					'label' => 'This is the title of formatted text',
					'text'  => 'Lorem ipsum',
				),
			),
			array(
				'name'        => __( 'Space', 'cost-calculator-builder-pro' ),
				'type'        => 'space',
				'icon'        => 'ccb-icon-space',
				'field_width' => '12',
				'attributes'  => array(
					'label' => 'Space',
				),
			),
		);
	}

	public static function get_active_form_id( $calc_id ) {
		return get_post_meta( $calc_id, 'form_id', true );
	}
}

<?php
$invoice_texts = array(
	'order'          => esc_html__( 'Order', 'cost-calculator-builder' ),
	'total_title'    => esc_html__( 'Total Summary', 'cost-calculator-builder' ),
	'payment_method' => esc_html__( 'Payment method:', 'cost-calculator-builder' ),
	'contact_title'  => esc_html__( 'Contact Information', 'cost-calculator-builder' ),
	'contact_form'   => array(
		'name'    => esc_html__( 'Name', 'cost-calculator-builder' ),
		'email'   => esc_html__( 'Email', 'cost-calculator-builder' ),
		'phone'   => esc_html__( 'Phone', 'cost-calculator-builder' ),
		'message' => esc_html__( 'Message', 'cost-calculator-builder' ),
	),
	'total_header'   => array(
		'name'  => esc_html__( 'Name', 'cost-calculator-builder' ),
		'unit'  => esc_html__( 'Composition', 'cost-calculator-builder' ),
		'total' => esc_html__( 'Total', 'cost-calculator-builder' ),
	),
);

$send_pdf_texts = array(
	'title'          => esc_html__( 'Email Quote', 'cost-calculator-builder' ),
	'name'           => esc_html__( 'Name', 'cost-calculator-builder' ),
	'name_holder'    => esc_html__( 'Enter name', 'cost-calculator-builder' ),
	'email'          => esc_html__( 'Email', 'cost-calculator-builder' ),
	'email_holder'   => esc_html__( 'Enter Email', 'cost-calculator-builder' ),
	'message'        => esc_html__( 'Message', 'cost-calculator-builder' ),
	'message_holder' => esc_html__( 'Enter message', 'cost-calculator-builder' ),
	'submit'         => isset( $general_settings['invoice']['submitBtnText'] ) ? $general_settings['invoice']['submitBtnText'] : esc_html__( 'Send', 'cost-calculator-builder' ),
	'close'          => isset( $general_settings['invoice']['closeBtn'] ) ? $general_settings['invoice']['closeBtn'] : esc_html__( 'Close', 'cost-calculator-builder' ),
	'success_text'   => isset( $general_settings['invoice']['successText'] ) ? $general_settings['invoice']['successText'] : esc_html__( 'Email Quote Successfully Sent!', 'cost-calculator-builder' ),
	'error_message'  => isset( $general_settings['invoice']['errorText'] ) ? $general_settings['invoice']['errorText'] : esc_html__( 'Fill in the required fields correctly.', 'cost-calculator-builder' ),
);

$styles = array(
	array(
		'label' => __( 'Two columns', 'cost-calculator-builder' ),
		'icon'  => 'ccb-icon-Union-27',
		'key'   => 'two_column',
	),
	array(
		'label' => __( 'Vertical', 'cost-calculator-builder' ),
		'icon'  => 'ccb-icon-Union-26',
		'key'   => 'vertical',
	),
	array(
		'label' => __( 'Horizontal', 'cost-calculator-builder' ),
		'icon'  => 'ccb-icon-Union-25',
		'key'   => 'horizontal',
	),
);
?>

<div class="calc-container-wrapper">
	<?php if ( defined( 'CCB_PRO_PATH' ) ) : ?>
		<calc-thank-you-page :class="['calc-hidden', {'calc-loaded': loader}]" v-if="hideThankYouPage" @invoice="getInvoice" @send-pdf="showSendPdf" @reset="resetCalc" :invoice_status="false"></calc-thank-you-page>
	<?php endif; ?>

	<div v-show="hideCalculator" ref="calc" class="calc-container" data-calc-id="<?php echo esc_attr( $calc_id ); ?>" :class="[boxStyle, {demoSite: showDemoBoxStyle}, {'has-title': showMultiStepCalcTitle}]" :style="fullWithStepCalc">
		<loader-wrapper v-if="loader" idx="<?php echo esc_attr( $loader_idx ); ?>" width="60px" height="60px" scale="0.9" :front="true"></loader-wrapper>
		<div class="ccb-demo-box-styles" :class="{active: showDemoBoxStyle}">
			<div class="ccb-box-styles">
				<?php foreach ( $styles as $style ) : ?>
					<div class="ccb-box-style-inner" :class="{'ccb-style-active': boxStyle === '<?php echo esc_attr( $style['key'] ); ?>'}" @click="changeBoxStyle('<?php echo esc_html( $style['key'] ); ?>')">
						<i class="<?php echo esc_attr( $style['icon'] ); ?>"></i>
						<span><?php echo esc_html( $style['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="calc-multi-step-title" v-if="showMultiStepTitle" v-text="getTheTitle"></div>

		<div class="calc-fields calc-list calc-list__indexed" :class="{loaded: !loader, 'payment' : getHideCalc}">
			<?php if ( is_user_logged_in() && current_user_can( 'administrator' ) ) : ?>
				<a href="<?php echo htmlspecialchars_decode( esc_url( admin_url( 'admin.php?page=cost_calculator_builder&action=edit&id=' . $calc_id ) ) ); //phpcs:ignore ?>"
				target="_blank" class="ccb-calc-edit">
					<span><i class="ccb-icon-Path-3483"></i></span>
					<span class="ccb-calc-edit__text"><?php esc_html_e( 'Edit', 'cost-calculator-builder' ); ?></span>
				</a>
			<?php endif; ?>
			<calc-page-navigation
				:count="totalPages"
				:index="activePageIndex"
				:pages="getPages"
				v-if="pageBreakEnabled"
			>
			</calc-page-navigation>

			<template v-if="pageBreakEnabled">
				<?php echo \cBuilder\Classes\CCBTemplate::load( 'frontend/partials/multi-step' ); //phpcs:ignore?>
			</template>
			<template v-else>
				<?php echo \cBuilder\Classes\CCBTemplate::load( 'frontend/partials/default' ); //phpcs:ignore?>
			</template>

			<div class="calc-page-navigation" :class="{'show-totals': totalsInPages}" v-if="pageBreakEnabled">
				<div class="calc-page-navigation__total" v-if="totalsInPages">
					<div class="totals">
						<div class="totals-item">
							<template v-for="item in pageBreakRepeaterFormula">
								<cost-total :value="item.total" :discount="item.discount" :field="item.data" :id="calc_data.id" @condition-apply="renderCondition"></cost-total>
							</template>
							<template v-for="item in pageBreakTotals">
								<div v-if="showFormulaConst" style="display: flex" class="sub-list-item total">
									<span class="sub-item-title"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
									<span class="sub-item-value" style="white-space: nowrap">{{ item.data.converted }}</span>
								</div>
								{{ item.discount }}
								<cost-total v-else :value="item.total" :discount="item.discount" :field="item.data" :id="calc_data.id" @condition-apply="renderCondition"></cost-total>
							</template>
						</div>
					</div>
					<div class="summary">
						<span @click="showSummaryPopup">Show summary</span>
					</div>
				</div>
				<div class="calc-page-navigation__actions">
					<button class="back" @click="prevPage" v-if="activePageIndex !== 0 ">
						<i class="ccb-icon-Arrow-Previous"></i>
						<span>{{ currentPagePrevBtn }}</span>
					</button>
					<button class="next" :class="{'disable': !pageConditionResult}" @click="nextPage" v-if="activePageIndex + 1 !== totalPages">
						<span>{{ currentPageNextBtn }}</span>
						<i class="ccb-icon-Arrow-Previous" style="transform: rotate(180deg);"></i>
					</button>
				</div>
			</div>
		</div>
		<div class="calc-subtotal calc-list" :id="getTotalStickyId" :class="{loaded: !loader}" v-if="!summaryInLastPage || !pageBreakEnabled">
			<div class="calc-subtotal-wrapper" :class="{ 'calc-page-break-subtotal-wrapper': isPageBreakAllowed}">
				<div class="calc-list-inner">
					<div class="calc-item-title calc-accordion" v-show="!summaryDisplay || showAfterSubmit">
						<div class="ccb-calc-heading">
							<?php echo isset( $settings['general']['header_title'] ) ? esc_html( $settings['general']['header_title'] ) : ''; ?>
						</div>
						<?php if ( isset( $settings['general']['descriptions'] ) ? esc_html( $settings['general']['descriptions'] ) : '' ) : ?>
							<span class="calc-accordion-btn" ref="calcAccordionToggle" @click="toggleAccordionAction">
								<i class="ccb-icon-Path-3485" :style="{top: '1px', transform: !accordionState ? 'rotate(0)' : 'rotate(180deg)'}"></i>
							</span>
						<?php endif; ?>
					</div>

					<div class="calc-item-title calc-accordion" style="margin: 0 !important;" v-show="shouldDisplaySummary">
						<div class="ccb-calc-heading" style="text-transform: none !important; padding-bottom: 15px; word-break: break-word;" v-text="summaryDisplaySettings?.form_title"></div>
					</div>

					<div class="calc-subtotal-list" :class="{ 'show-unit': showUnitInSummary }" v-show="!summaryDisplay || showAfterSubmit">
						<transition>
							<div class="calc-subtotal-list-accordion" :class="{hidden: !accordionState}">
								<div class="calc-subtotal-list-header" v-if="showUnitInSummary">
									<span class="calc-subtotal-list-header__name"><?php esc_html_e( 'Name', 'cost-calculator-builder' ); ?></span>
									<span class="calc-subtotal-list-header__value"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
								</div>

								<template v-for="(field) in getTotalSummaryFields" v-if="shouldProcessFieldWithSettings(field)">
									<template v-if="field.alias.includes('repeater')">
										<div class="calc-repeater-subtotal" v-for="(f, idx) in Object.values(field?.resultGrouped)">
											<template v-if="getRepeaterFields(f)?.length">
												<div class="calc-repeater-subtotal-header" :data-index="idx" @click="toggleRepeater(idx)">
													<i class="ccb-icon-Path-3514"></i>
													<span>{{field.label}}</span>
													<span class="ccb-repeater-field-number">#{{idx + 1}}</span>
												</div>
												<div class="calc-repeater-subtotal-fields" :data-index="idx">
													<template v-for="(innerField) in getRepeaterFields(f)">
														<calc-total-summary :field="innerField" :style="{'padding-top': '6px'}"></calc-total-summary>
														<calc-total-summary :field="innerField" :unit="true" v-if="innerField.option_unit" :style="{'padding-top': '5px'}"></calc-total-summary>
														<calc-total-summary :field="innerField" :style="{'padding-top': '5px'}" :multi="true" v-if="isCheckableFieldWithOptions(innerField)"></calc-total-summary>
													</template>
												</div>
											</template>
										</div>
									</template>
									<template v-else>
										<calc-total-summary :field="field"></calc-total-summary>
										<template v-if="field.option_unit">
											<template v-if="field.hasOwnProperty('allowPrice')">
												<calc-total-summary :field="field" :unit="true" v-if="field.allowPrice"></calc-total-summary>
											</template>
											<template v-else>
												<calc-total-summary :field="field" :unit="true"></calc-total-summary>
											</template>
										</template>
										<calc-total-summary :field="field" :multi="true" v-if="isCheckableFieldWithOptions(field)"></calc-total-summary>
									</template>
								</template>
							</div>
						</transition>
					</div>

					<div class="calc-subtotal-list totals" style="margin-top: 20px; padding-top: 10px;" ref="calcTotals" :class="{'unit-enable': showUnitInSummary}" v-show="shouldShowSummaryOrTotals">
						<template v-for="item in getRepeaterTotals">
							<cost-total :value="item.total" :discount="item.discount" :field="item.data" :id="calc_data.id" @condition-apply="renderCondition"></cost-total>
						</template>
						<template v-for="item in formulaConst">
							<div v-if="isFormulaConstInvalid" style="display: flex" class="sub-list-item total">
								<span class="sub-item-title"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
								<span class="sub-item-value" style="white-space: nowrap">{{ item.data.converted }}</span>
							</div>
							{{ item.discount }}
							<cost-total v-else :value="item.total" :discount="item.discount" :field="item.data" :id="calc_data.id" @condition-apply="renderCondition"></cost-total>
						</template>
					</div>

					<div class="calc-subtotal-list" v-if="getWooProductName" v-show="!summaryDisplay || showAfterSubmit">
						<div class="calc-woo-product">
							<div class="calc-woo-product__info">
								"{{getWooProductName}}"<?php echo esc_html__( ' has been added to your cart', 'cost-calculator-builder' ); ?>
							</div>
							<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
								<button class="calc-woo-product__btn">
									<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><span><?php echo esc_html__( 'View cart', 'cost-calculator-builder' ); ?></span></a>
								</button>
							<?php endif; ?>
						</div>
					</div>

					<div class="calc-promocode-wrapper" v-if="hasPromocode" v-show="!summaryDisplay || showAfterSubmit">
						<div class="promocode-header"></div>
						<div class="promocode-body ccb-promo-body">
							<div class="calc-have-promocode">
								<span><?php esc_html_e( 'Have a promo code?', 'cost-calculator-builder' ); ?></span>
							</div>
							<div class="calc-item ccb-field ccb-field-quantity">
								<div class="calc-item__title" style="display: flex; align-items: center; column-gap: 10px">
									<div class="promocode-error-tip" v-if="showPromocode" :class="{active: discountError !== ''}">
										<span v-if="discountError === 'invalid'" :class="{active: discountError !== ''}" class="ccb-error-tip front default"><?php esc_html_e( 'Invalid promo code', 'cost-calculator-builder' ); ?></span>
										<span v-if="discountError === 'not_exist'" :class="{active: discountError !== ''}" class="ccb-error-tip front default"><?php esc_html_e( 'Promo code does not exist', 'cost-calculator-builder' ); ?></span>
										<span v-if="discountError === 'used'" :class="{active: discountError !== ''}" class="ccb-error-tip front default"><?php esc_html_e( 'Promo code is already applied', 'cost-calculator-builder' ); ?></span>
									</div>
									<div class="promocode-label">
										<span><?php esc_html_e( 'Promo code', 'cost-calculator-builder' ); ?></span>
										<span class="ccb-promocode-hint" v-if="showPromocode" @click.stop="showPromocode = !showPromocode"><?php esc_html_e( 'Hide', 'cost-calculator-builder' ); ?></span>
										<span class="ccb-promocode-hint" v-if="!showPromocode" @click.stop="showPromocode = !showPromocode"><?php esc_html_e( 'Show', 'cost-calculator-builder' ); ?></span>
									</div>
								</div>
								<template v-if="showPromocode">
									<div class="calc-promocode-container">
										<div class="calc-input-wrapper ccb-field" :class="{required: discountError !== ''}">
											<input type="text" v-model="promocode" @input="discountError = ''" class="calc-input ccb-field ccb-appearance-field">
										</div>
										<button class="calc-btn-action ispro-wrapper success" @click.stop="applyPromocode">
											<span><?php esc_html_e( 'Apply', 'cost-calculator-builder' ); ?></span>
										</button>
									</div>

									<div class="calc-applied" v-if="usedPromocodes?.length">
										<span class="calc-applied-title"><?php esc_html_e( 'Applied promo codes:', 'cost-calculator-builder' ); ?></span>
										<div class="ccb-promocodes-list">
											<span v-for="promocode in usedPromocodes" class="ccb-promocode">
												{{ promocode }}
												<i @click.stop="removePromocode(promocode)" class="remove ccb-icon-close"></i>
											</span>
										</div>
									</div>
								</template>
							</div>
						</div>
					</div>

					<div class="calc-subtotal-list calc-buttons">
						<?php if ( ccb_pro_active() ) : ?>
							<cost-pro-features :settings="content.settings" @submit="submitForm" @reset="resetForm"></cost-pro-features>
						<?php endif; ?>
					</div>
				</div>

				<div class="calc-list-inner calc-notice" :class="noticeData.type" v-show="shouldHideThankYouPage" style="margin-top: 10px !important;">
					<calc-notices :notice="noticeData"/>
				</div>
			</div>
		</div>
		<calc-invoice
					ref="invoice"
					company-name="<?php echo isset( $general_settings['invoice']['companyName'] ) ? esc_attr( $general_settings['invoice']['companyName'] ) : ''; ?>"
					company-info="<?php echo isset( $general_settings['invoice']['companyInfo'] ) ? esc_attr( $general_settings['invoice']['companyInfo'] ) : ''; ?>"
					company-logo='<?php echo esc_attr( $general_settings['invoice']['companyLogo'] ); ?>'
					date-format="<?php echo isset( $general_settings['invoice']['dateFormat'] ) ? esc_attr( $general_settings['invoice']['dateFormat'] ) : ''; ?>"
					static-texts='<?php echo esc_attr( wp_json_encode( $invoice_texts ) ); ?>'
					send-email-texts='<?php echo esc_attr( wp_json_encode( $send_pdf_texts ) ); ?>'
					send-pdf-from="<?php echo isset( $general_settings['invoice']['fromEmail'] ) ? esc_attr( $general_settings['invoice']['fromEmail'] ) : ''; ?>"
					send-pdf-fromname="<?php echo isset( $general_settings['invoice']['fromName'] ) ? esc_attr( $general_settings['invoice']['fromName'] ) : ''; ?>"
					:summary-fields="getTotalSummaryFields"
					site-lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>"
			/>
	</div>
</div>

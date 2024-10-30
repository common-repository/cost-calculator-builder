<?php $get_date_format = get_option( 'date_format' ); ?>
<div class="calc-list-inner calc-multi-step" :style="pageNavigationStyle">
	<div class="calc-item-title">
		<div class="ccb-calc-heading" v-text="currentPageTitle" v-if="!this.$store.getters.getPageBreakStatus"></div>
		<div class="ccb-calc-heading" v-text="getTheTitle" v-if="this.$store.getters.getPageBreakStatus"></div>
	</div>
	<div v-if="calc_data" class="calc-fields-container">
		<div class="calc-pages">
			<div class="calc-page" v-for="( page, index ) in getPages" v-show="index == activePageIndex"
				 :class="page.boxStyle">
				<template v-for="field in page.groupElements">
					<template v-if="shouldDisplayField(field)">
						<component
								format="<?php esc_attr( $get_date_format ); ?>"
								text-days="<?php esc_attr_e( 'days', 'cost-calculator-builder' ); ?>"
								v-if="checkField(field)"
								:is="field._tag"
								:id="calc_data.id"
								:field="field"
								:converter="currencyFormat"
								:disabled="fields[field.alias].disabled"
								v-model="fields[field.alias].value"
								v-on:change="change"
								v-on:[field._event]="change"
								v-on:condition-apply="renderCondition"
								@delete-repeater="deleteRepeater"
								@add-repeater="deleteRepeater"
								:key="!field.hasNextTick ? field.alias : field.alias + '_' + fields[field.alias].nextTickCount"
						>
						</component>
					</template>
					<template v-if="isGroupField(field)">
						<component
								format="<?php esc_attr( $get_date_format ); ?>"
								text-days="<?php esc_attr_e( 'days', 'cost-calculator-builder' ); ?>"
								v-if="checkField(field)"
								:is="field._tag"
								:id="calc_data.id"
								:field="field"
								:converter="currencyFormat"
								:disabled="fields[field.alias].disabled"
								v-model="fields[field.alias].value"
								v-on:change="change"
								v-on:[field._event]="change"
								v-on:condition-apply="renderCondition"
								@delete-repeater="deleteRepeater"
								@add-repeater="deleteRepeater"
								:key="!field.hasNextTick ? field.alias : field.alias + '_' + fields[field.alias].nextTickCount"
						>
							<slot>
								<template v-for="element in field.groupElements">
									<component
											format="<?php esc_attr( $get_date_format ); ?>"
											text-days="<?php esc_attr_e( 'days', 'cost-calculator-builder' ); ?>"
											v-if="checkFieldElement(element)"
											:is="element._tag"
											:id="calc_data.id"
											:field="element"
											:converter="currencyFormat"
											:disabled="fields[element.alias].disabled"
											v-model="fields[element.alias].value"
											v-on:change="change"
											v-on:[element._event]="change"
											v-on:condition-apply="renderCondition"
											@delete-repeater="deleteRepeater"
											@add-repeater="deleteRepeater"
											:key="!element.hasNextTick ? element.alias : element.alias + '_' + fields[element.alias].nextTickCount"
									>
									</component>
								</template>
							</slot>
						</component>
					</template>
					<template v-else-if="isValidField(field)">
						<component
								:id="calc_data.id"
								style="boxStyle"
								:is="field._tag"
								:field="field"
						>
						</component>
					</template>
				</template>
			</div>
			<div class="calc-summary-page" v-if="shouldShowSummaryOnLastPage">
				<div class="calc-subtotal calc-list" :id="getTotalStickyId" :class="{loaded: !loader}">
					<div class="calc-subtotal-wrapper">
						<div class="calc-list-inner">
							<div class="calc-item-title calc-accordion" v-show="!summaryDisplay || showAfterSubmit">
								<div class="ccb-calc-heading">
									{{ getHeaderTitle }}
								</div>
								<span class="calc-accordion-btn" ref="calcAccordionToggle" @click="toggleAccordionAction">
									<i class="ccb-icon-Path-3485" :style="{top: '1px', transform: !accordionState ? 'rotate(0)' : 'rotate(180deg)'}"></i>
								</span>
							</div>

							<div class="calc-item-title calc-accordion" style="margin: 0 !important;"
								 v-show="shouldDisplaySummary">
								<div class="ccb-calc-heading"
									 style="text-transform: none !important; padding-bottom: 15px"
									 v-text="summaryDisplaySettings?.form_title"></div>
							</div>

							<div class="calc-subtotal-list" :class="{ 'show-unit': showUnitInSummary }"
								 v-show="!summaryDisplay || showAfterSubmit">
								<transition>
									<div class="calc-subtotal-list-accordion" :class="{hidden: !accordionState}">
										<div class="calc-subtotal-list-header" v-if="showUnitInSummary">
											<span class="calc-subtotal-list-header__name"><?php esc_html_e( 'Name', 'cost-calculator-builder' ); ?></span>
											<span class="calc-subtotal-list-header__value"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
										</div>

										<template v-for="(field) in getTotalSummaryFields"
												  v-if="shouldApplyDescriptionsToField(field)">
											<template v-if="field.alias.includes('repeater')">
												<div class="calc-repeater-subtotal"
													 v-for="(f, idx) in Object.values(field?.resultGrouped)">
													<template v-if="getRepeaterFields(f)?.length">
														<div class="calc-repeater-subtotal-header" :data-index="idx"
															 @click="toggleRepeater(idx)">
															<i class="ccb-icon-Path-3514"></i>
															<span>{{field.label}}</span>
															<span class="ccb-repeater-field-number">#{{idx + 1}}</span>
														</div>
														<div class="calc-repeater-subtotal-fields" :data-index="idx">
															<template v-for="(innerField) in getRepeaterFields(f)">
																<calc-total-summary :field="innerField"
																					:style="{'padding-top': '6px'}"></calc-total-summary>
																<calc-total-summary :field="innerField" :unit="true"
																					v-if="innerField.option_unit"
																					:style="{'padding-top': '5px'}"></calc-total-summary>
																<calc-total-summary :field="innerField"
																					:style="{'padding-top': '5px'}"
																					:multi="true"
																					v-if="isCheckableFieldWithOptions(innerField)"></calc-total-summary>
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
							</div>f

							<div class="calc-subtotal-list totals" style="margin-top: 20px; padding-top: 10px;"
								 ref="calcTotals" :class="{'unit-enable': showUnitInSummary}"
								 v-show="!summaryDisplay || showAfterSubmit">
								<template v-for="item in getRepeaterTotals">
									<cost-total :value="item.total" :discount="item.discount" :field="item.data"
												:id="calc_data.id" @condition-apply="renderCondition"></cost-total>
								</template>
								<template v-for="item in formulaConst">
									<div v-if="isFormulaConstInvalid"
										 style="display: flex" class="sub-list-item total">
										<span class="sub-item-title"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
										<span class="sub-item-value" style="white-space: nowrap">{{ item.data.converted }}</span>
									</div>
									{{ item.discount }}
									<cost-total v-else :value="item.total" :discount="item.discount" :field="item.data"
												:id="calc_data.id" @condition-apply="renderCondition"></cost-total>
								</template>
							</div>

							<div class="calc-subtotal-list" v-if="getWooProductName"
								 v-show="!summaryDisplay || showAfterSubmit">
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

							<div class="calc-promocode-wrapper" v-if="hasPromocode"
								 v-show="!summaryDisplay || showAfterSubmit">
								<div class="promocode-header"></div>
								<div class="promocode-body">
									<div class="calc-have-promocode">
										<span><?php esc_html_e( 'Have a promocode?', 'cost-calculator-builder' ); ?></span>
									</div>
									<div class="calc-item ccb-field ccb-field-quantity">
										<div class="calc-item__title"
											 style="display: flex; align-items: center; column-gap: 10px">
											<span><?php esc_html_e( 'Promocode', 'cost-calculator-builder' ); ?></span>
											<span class="ccb-promocode-hint" v-if="showPromocode"
												  @click.stop="showPromocode = !showPromocode"><?php esc_html_e( 'Hide', 'cost-calculator-builder' ); ?></span>
											<span class="ccb-promocode-hint" v-if="!showPromocode"
												  @click.stop="showPromocode = !showPromocode"><?php esc_html_e( 'Show', 'cost-calculator-builder' ); ?></span>
										</div>
										<template v-if="showPromocode">
											<div class="calc-promocode-container">
												<div class="calc-input-wrapper ccb-field"
													 :class="{required: discountError !== ''}">
													<span v-if="discountError === 'invalid'" :class="{active: discountError !== ''}" class="ccb-error-tip front default">
														<?php esc_html_e( 'Invalid promocode', 'cost-calculator-builder' ); ?>
													</span>
													<span v-if="discountError === 'not_exist'" :class="{active: discountError !== ''}" class="ccb-error-tip front default">
														<?php esc_html_e( 'Promocode not exists', 'cost-calculator-builder' ); ?>
													</span>
													<span v-if="discountError === 'used'" :class="{active: discountError !== ''}" class="ccb-error-tip front default">
														<?php esc_html_e( 'Promo code is already applied', 'cost-calculator-builder' ); ?>
													</span>
													<input type="text" v-model="promocode" @input="discountError = ''" class="calc-input ccb-field ccb-appearance-field">
												</div>
												<button class="calc-btn-action ispro-wrapper success"
														@click.stop="applyPromocode">
													<span><?php esc_html_e( ' Apply', 'cost-calculator-builder' ); ?></span>
												</button>
											</div>

											<div class="calc-applied" v-if="usedPromocodes?.length">
												<span class="calc-applied-title"><?php esc_html_e( 'Applied promocodes:', 'cost-calculator-builder' ); ?></span>
												<div class="ccb-promocodes-list">
													<span v-for="promocode in usedPromocodes" class="ccb-promocode">
														{{ promocode }}
														<i @click.stop="removePromocode(promocode)"
														class="remove ccb-icon-close"></i>
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

						<div class="calc-list-inner calc-notice" :class="noticeData.type"
							 v-show="shouldHideThankYouPage"
							 style="margin-top: 10px !important;">
							<calc-notices :notice="noticeData"/>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="calc-summary-popup" v-if="popupStatus">
	<div class="calc-summary-popup__overlay">
		<div class="calc-summary-popup-window" :class="{active: activeClass}">
			<div class="calc-summary-popup__header">
				<div class="calc-summary-popup__title">
					{{ getHeaderTitle }}
				</div>
				<div class="calc-summary-popup__close">
					<span span @click="closeSummaryPopup">
						<i class="ccb-icon-close"></i>
					</span>
				</div>
			</div>
			<div class="calc-summary-popup__body">
				<div class="calc-subtotal-list" :class="{ 'show-unit': showUnitInSummary }">
					<transition>
						<div class="calc-subtotal-list-accordion" :class="{hidden: !accordionState}" >
							<div class="calc-subtotal-list-header" v-if="showUnitInSummary">
								<span class="calc-subtotal-list-header__name"><?php esc_html_e( 'Name', 'cost-calculator-builder' ); ?></span>
								<span class="calc-subtotal-list-header__value"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
							</div>

							<template v-for="(field) in getPageTotalSummaryFields"
									  v-if="shouldProcessFieldWithSettings(field)">
								<template v-if="field.alias.includes('repeater')">
									<div class="calc-repeater-subtotal"
										 v-for="(f, idx) in Object.values(field?.resultGrouped)">
										<template v-if="getRepeaterFields(f)?.length">
											<div class="calc-repeater-subtotal-header" :data-index="idx"
												 @click="toggleRepeater(idx)">
												<i class="ccb-icon-Path-3514"></i>
												<span>{{field.label}}</span>
												<span class="ccb-repeater-field-number">#{{idx + 1}}</span>
											</div>
											<div class="calc-repeater-subtotal-fields" :data-index="idx">
												<template v-for="(innerField) in getRepeaterFields(f)">
													<calc-total-summary :field="innerField"
																		:style="{'padding-top': '6px'}"></calc-total-summary>
													<calc-total-summary :field="innerField" :unit="true"
																		v-if="innerField.option_unit"
																		:style="{'padding-top': '5px'}"></calc-total-summary>
													<calc-total-summary :field="innerField"
																		:style="{'padding-top': '5px'}" :multi="true"
																		v-if="isCheckableFieldWithOptions(innerField)"></calc-total-summary>
												</template>
											</div>
										</template>
									</div>
								</template>
								<template v-else>
									<calc-total-summary :field="field"></calc-total-summary>
									<template v-if="field.option_unit">
										<template v-if="field.hasOwnProperty('allowPrice')">
											<calc-total-summary :field="field" :unit="true"
																v-if="field.allowPrice"></calc-total-summary>
										</template>
										<template v-else>
											<calc-total-summary :field="field" :unit="true"></calc-total-summary>
										</template>
									</template>
									<calc-total-summary :field="field" :multi="true"
														v-if="isCheckableFieldWithOptions(field)"></calc-total-summary>
								</template>
							</template>
						</div>
					</transition>
				</div>

				<div class="calc-subtotal-list totals" style="margin-top: 20px; padding-top: 10px;" ref="calcTotals" :class="{'unit-enable': showUnitInSummary}" v-show="!summaryDisplay || showAfterSubmit">
					<template v-for="item in getRepeaterTotals">
						<cost-total :value="item.total" :discount="item.discount" :field="item.data" :id="calc_data.id"
									@condition-apply="renderCondition"></cost-total>
					</template>
					<template v-for="item in formulaConst">
						<div v-if="isFormulaConstInvalid" style="display: flex" class="sub-list-item total">
							<span class="sub-item-title"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
							<span class="sub-item-value" style="white-space: nowrap">{{ item.data.converted }}</span>
						</div>
						{{ item.discount }}
						<cost-total v-else :value="item.total" :discount="item.discount" :field="item.data"
									:id="calc_data.id" @condition-apply="renderCondition"></cost-total>
					</template>
				</div>
			</div>
		</div>
	</div>
</div>

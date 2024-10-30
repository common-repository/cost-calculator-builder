<div class="cbb-edit-field-container">
	<div class="ccb-edit-field-header">
		<span class="ccb-edit-field-title ccb-heading-3 ccb-bold"><?php esc_html_e( 'Page breaker settings', 'cost-calculator-builder' ); ?></span>
		<div class="ccb-field-actions">
			<button class="ccb-button default" @click="$emit( 'cancel' )"><?php esc_html_e( 'Cancel', 'cost-calculator-builder' ); ?></button>
			<button class="ccb-button success" @click="save()"><?php esc_html_e( 'Save', 'cost-calculator-builder' ); ?></button>
		</div>
	</div>
	<div class="ccb-grid-box">
		<div class="container">
			<div class="row">
				<div class="col-12">
					<div class="ccb-edit-field-switch">
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'main'}" @click="tab = 'main'">
							<?php esc_html_e( 'Element', 'cost-calculator-builder' ); ?>
						</div>
						<div class="ccb-edit-field-switch-item ccb-default-title" :class="{active: tab === 'settings'}" @click="tab = 'settings'">
							<?php esc_html_e( 'Settings', 'cost-calculator-builder' ); ?>
						</div>
					</div>
				</div>
			</div>
			
			<template v-if="tab === 'main'">
				<div class="row ccb-p-t-15">
					<div class="col-6">
						<div class="ccb-select-box">
							<span class="ccb-select-label"><?php esc_html_e( 'Navigation style', 'cost-calculator-builder' ); ?></span>
							<div class="ccb-select-wrapper">
								<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
								<select class="ccb-select" v-model="pageNavigation.pagination_type" @change="typeSelected">
									<option value="circle_with_line"><?php esc_html_e( 'Circle steps with line', 'cost-calculator-builder' ); ?></option>
									<option value="circle_tabs"><?php esc_html_e( 'Circle tabs', 'cost-calculator-builder' ); ?></option>
									<option value="rectangle_steps_with_line"><?php esc_html_e( 'Rectangle steps with line', 'cost-calculator-builder' ); ?></option>
									<option value="rectangle_tabs"><?php esc_html_e( 'Rectangle tabs', 'cost-calculator-builder' ); ?></option>
									<option value="progress_with_circle"><?php esc_html_e( 'Progress with circle', 'cost-calculator-builder' ); ?></option>
									<option value="progress_with_bar"><?php esc_html_e( 'Progress with bar', 'cost-calculator-builder' ); ?></option>
									<option value="hidden"><?php esc_html_e( 'Hidden', 'cost-calculator-builder' ); ?></option>
								</select>
							</div>
						</div>
					</div>
					<div class="col-6">
						<div class="list-header" :class="{disable: disableHiddenSelect}" style="padding-top: 26px;">
							<div class="ccb-switch">
								<input type="checkbox" v-model="pageNavigation.hide_pagination_title">
								<label></label>
							</div>
							<h6 class="ccb-heading-5"><?php esc_html_e( 'Hide page title in navigation', 'cost-calculator-builder' ); ?></h6>
						</div>
					</div>
				</div>
				<div class="row ccb-p-t-15">
					<div class="col-12">
						<div class="ccb-style-preview">
							<span class="ccb-style-preview-header"><?php esc_html_e( 'Style preview', 'cost-calculator-builder' ); ?></span>
							<img :src="navigationImg" v-if="pageNavigation.pagination_type !== 'hidden'">
							<span v-if="pageNavigation.pagination_type == 'hidden'"><?php esc_html_e( 'Page Navigation Hidden', 'cost-calculator-builder' ); ?></span>
						</div>
					</div>
				</div>
			</template>
			<template v-if="tab === 'settings'">
				<div class="row ccb-p-t-15" style="margin-top: 10px;" v-if="this.$store.getters.getPageBreakStatus">
					<div class="col-12">
						<div class="list-header" style="padding-top: 10px;">
							<div class="ccb-switch">
								<input type="checkbox" v-model="pageNavigation.summary_after_last_page" @change="disableTotalInPage"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-6 ccb-bold"><?php esc_html_e( 'Show summary block on the last page of multi-step calculator', 'cost-calculator-builder' ); ?></h6>
							<span class="ccb-help-tip-block" style="margin-top: 2px;">
								<span class="ccb-help-label" ><?php esc_html_e( 'Preview', 'cost-calculator-builder' ); ?></span>
								<span class="ccb-help ccb-help-settings page-custom-help-tip" style="left: -290%; bottom: -150px;">
									<span class="ccb-help-content">
										<img src="<?php echo esc_url( CALC_URL . '/frontend/dist/img/summary-block.gif' ); ?>" alt="woo logo">
									</span>
								</span>
							</span>
						</div>
						<span class="ccb-heading-6" style="margin-left: 56px; width: 100%; color: #768493;"><?php esc_html_e( 'The setting will be applied only when a calculator is built with Page breaker', 'cost-calculator-builder' ); ?></span>
					</div>
				</div>
				<div class="row ccb-p-t-15" v-if="this.$store.getters.getPageBreakStatus" :class="{'ccb-settings-disabled': !pageNavigation.summary_after_last_page}">
					<div class="col-12">
						<div class="list-header">
							<div class="ccb-switch">
								<input type="checkbox" v-model="pageNavigation.total_in_page"/>
								<label></label>
							</div>
							<h6 class="ccb-heading-6 ccb-bold"><?php esc_html_e( 'Show total summary on the page', 'cost-calculator-builder' ); ?></h6>
							<span class="ccb-help-tip-block" style="margin-top: 2px;">
								<span class="ccb-help-label" ><?php esc_html_e( 'Preview', 'cost-calculator-builder' ); ?></span>
								<span class="ccb-help ccb-help-settings">
									<span class="ccb-help-content">
										<img src="<?php echo esc_url( CALC_URL . '/frontend/dist/img/show-total.png' ); ?>" alt="woo logo">
									</span>
								</span>
							</span>
						</div>
						<span class="ccb-heading-6" style="margin-left: 56px; display: inline-block; width: 90%; color: #768493;"><?php esc_html_e( 'There will be a button on the bottom of each step for users to click and open a popup with Summary', 'cost-calculator-builder' ); ?></span>
					</div>
				</div>
				<div class="row ccb-p-t-15" v-if="this.$store.getters.getPageBreakStatus && pageNavigation.total_in_page">
					<div class="col-16">
						<span class="ccb-field-title">
							<?php esc_html_e( 'Total Field Element', 'cost-calculator-builder-pro' ); ?>
						</span>
						<span class="ccb-field-totals">
							<label class="ccb-field-totals-item ccb-default-title" v-for="formula in getFormulaFields" :for="'contact_' + formula.idx">{{ formula.title | to-short-description }}</label>
						</span>
						<div class="ccb-select-box">
							<div class="multiselect">
								<span v-if="formulas.length > 0 && formulas.length <= 3" class="anchor ccb-heading-5 ccb-light-3 ccb-selected" @click.prevent="multiselectShow(event)">
									<span class="selected-payment" v-for="formula in formulas">
										{{ formula.title | to-short-input  }}
										<i class="ccb-icon-close" @click.self="removeIdx( formula )" :class="{'settings-item-disabled': getTotalsIdx.length === 1 && getTotalsIdx.includes(+formula.idx)}"></i>
									</span>
								</span>
								<span v-else-if="formulas.length > 0 && formulas.length > 3" class="anchor ccb-heading-5 ccb-light ccb-selected" @click.prevent="multiselectShow(event)">
									{{ formulas.length }} <?php esc_attr_e( 'totals selected', 'cost-calculator-builder-pro' ); ?>
								</span>
								<span v-else class="anchor ccb-heading-5 ccb-light-3" @click.prevent="multiselectShow(event)">
									<?php esc_html_e( 'Select totals', 'cost-calculator-builder-pro' ); ?>
								</span>
								<ul class="items row-list settings-list totals">
									<li class="option-item settings-item" v-for="formula in getFormulaFields" :class="{'settings-item-disabled': getTotalsIdx.length === 1 && getTotalsIdx.includes(+formula.idx)}" @click="(e) => autoSelect(e, formula)">
										<input :id="'contact_' + formula.idx" :checked="getTotalsIdx.includes(+formula.idx)" name="contactTotals" class="index" type="checkbox" @change="multiselectChooseTotals(formula)"/>
										<label :for="'contact_' + formula.idx" class="ccb-heading-5">{{ formula.title | to-short }}</label>
									</li>
								</ul>
								<input name="options" type="hidden" />
							</div>
						</div>
					</div>
				</div>
			</template>
		</div>
	</div>
</div>

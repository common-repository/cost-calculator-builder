<?php

use cBuilder\Classes\CCBSettingsData;

$general_settings = CCBSettingsData::get_calc_global_settings();
$invoice          = isset( $general_settings['invoice'] ) ? $general_settings['invoice'] : '';
$invoice_texts    = array(
	'order'             => esc_html__( 'Order', 'cost-calculator-builder' ),
	'total_title'       => esc_html__( 'Total Summary', 'cost-calculator-builder' ),
	'total'             => esc_html__( 'Total', 'cost-calculator-builder' ),
	'payment_method'    => esc_html__( 'Payment Method:', 'cost-calculator-builder' ),
	'date'              => esc_html__( 'Date:', 'cost-calculator-builder' ),
	'contact_title'     => esc_html__( 'Contact Information', 'cost-calculator-builder' ),
	'export_pdf_title'  => esc_html__( 'Export to PDF', 'cost-calculator-builder' ),
	'email_quote_title' => esc_html__( 'Email Quote', 'cost-calculator-builder' ),
	'order_form'        => array(
		'name'           => esc_html__( 'name', 'cost-calculator-builder' ),
		'email'          => esc_html__( 'email', 'cost-calculator-builder' ),
		'phone'          => esc_html__( 'phone', 'cost-calculator-builder' ),
		'message'        => esc_html__( 'message', 'cost-calculator-builder' ),
		'input-textbox'  => esc_html__( 'input-textbox', 'cost-calculator-builder' ),
		'number'         => esc_html__( 'number', 'cost-calculator-builder' ),
		'textarea'       => esc_html__( 'textarea', 'cost-calculator-builder' ),
		'dropdown'       => esc_html__( 'dropdown', 'cost-calculator-builder' ),
		'radio'          => esc_html__( 'radio', 'cost-calculator-builder' ),
		'checkbox'       => esc_html__( 'checkbox', 'cost-calculator-builder' ),
		'formatted-text' => esc_html__( 'formatted-text', 'cost-calculator-builder' ),
	),
	'contact_form'      => array(
		'name'  => esc_html__( 'name', 'cost-calculator-builder' ),
		'email' => esc_html__( 'email', 'cost-calculator-builder' ),
		'phone' => esc_html__( 'phone', 'cost-calculator-builder' ),
	),
	'total_header'      => array(
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
	'close'          => esc_html__( 'Close', 'cost-calculator-builder' ),
	'success_text'   => isset( $general_settings['invoice']['successText'] ) ? $general_settings['invoice']['successText'] : esc_html__( 'Email Quote Successfully Sent!', 'cost-calculator-builder' ),
	'error_message'  => isset( $general_settings['invoice']['errorText'] ) ? $general_settings['invoice']['errorText'] : esc_html__( 'Fill in the required fields correctly.', 'cost-calculator-builder' ),
);

wp_enqueue_script( 'cbb-feedback', CALC_URL . '/frontend/dist/feedback.js', array(), CALC_VERSION, true );
wp_enqueue_style( 'ccb-bootstrap-css', CALC_URL . '/frontend/dist/css/bootstrap.min.css', array(), CALC_VERSION );
wp_enqueue_style( 'ccb-calc-font', CALC_URL . '/frontend/dist/css/font/font.css', array(), CALC_VERSION );
wp_enqueue_style( 'ccb-admin-app-css', CALC_URL . '/frontend/dist/css/admin.css', array(), CALC_VERSION );
wp_enqueue_script( 'cbb-order-js', CALC_URL . '/frontend/dist/order.js', array(), CALC_VERSION, true );
wp_localize_script(
	'cbb-order-js',
	'ajax_window',
	array(
		'ajax_url'     => admin_url( 'admin-ajax.php' ),
		'dateFormat'   => get_option( 'date_format' ),
		'language'     => substr( get_bloginfo( 'language' ), 0, 2 ),
		'plugin_url'   => CALC_URL,
		'translations' => array_merge( \cBuilder\Classes\CCBTranslations::get_frontend_translations(), \cBuilder\Classes\CCBTranslations::get_backend_translations() ),
		'pro_active'   => ccb_pro_active(),
	)
);
?>

<?php require_once CALC_PATH . '/templates/admin/components/notice-mobile.php'; ?>

<div class="ccb-settings-wrapper calculator-orders" id="calculator_orders">
	<div class="ccb-main-container">
		<?php require_once CALC_PATH . '/templates/admin/components/header.php'; ?>
		<div class="ccb-tab-content">
			<div class="ccb-tab-sections">
				<div class="ccb-table-body ccb-orders-page">
					<div class="ccb-table-body--card">
						<div class="table-display" style="z-index: 3;">
							<div class="table-display--left">
								<div class="ccb-bulk-actions" v-if="showBulkActions">
									<div class="ccb-select-wrapper">
										<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
										<select v-model="bulkAction" class="ccb-select">
											<option value="none"><?php esc_html_e( 'Bulk actions', 'cost-calculator-builder' ); ?></option>
											<option value="complete" class="hide-if-no-js"><?php esc_html_e( 'Complete', 'cost-calculator-builder' ); ?></option>
											<option value="pending" class="hide-if-no-js"><?php esc_html_e( 'Pending', 'cost-calculator-builder' ); ?></option>
											<option value="delete"><?php esc_html_e( 'Delete', 'cost-calculator-builder' ); ?></option>
										</select>
									</div>
									<button class="ccb-button default" @click="updateMany"><?php esc_html_e( 'Apply', 'cost-calculator-builder' ); ?></button>
									<a class="ccb-link" href="<?php echo esc_url( get_admin_url() . 'edit.php?post_type=shop_order' ); ?>" target="_blank">
										<?php esc_html_e( 'WooCommerce Orders', 'cost-calculator-builder' ); ?>
										<i class="ccb-icon-click-out"></i>
									</a>
								</div>
							</div>
							<div class="table-display--right" style="align-items: center">
								<div class="ccb-bulk-actions" v-if="showBulkActions">
									<div class="ccb-input-wrapper">
										<i class="ccb-icon-Search-Magnifier ccb-search-magnifier"></i>
										<input type="text" v-model="search.searchInput" class="ccb-search-input" ref="searchInput" placeholder=<?php esc_html_e( 'Search', 'cost-calculator-builder' ); ?>>
										<i v-if="search.searchInput?.length > 0" class="ccb-icon-close-x ccb-search-close" @click="resetSearch"></i>
									</div>
									<div class="ccb-select-wrapper">
										<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
										<select v-model="search.searchType" class="ccb-select" style="padding-right: 20px; min-width: 160px;">
											<option value=""><?php esc_html_e( 'Search by', 'cost-calculator-builder' ); ?></option>
											<option value="email"><?php esc_html_e( 'Email', 'cost-calculator-builder' ); ?></option>
											<option value="calc_name" class="hide-if-no-js"><?php esc_html_e( 'Calculator name', 'cost-calculator-builder' ); ?></option>
											<option value="order_id" class="hide-if-no-js"><?php esc_html_e( 'Order ID', 'cost-calculator-builder' ); ?></option>
										</select>
									</div>


									<div class="ccb-button-wrapper" @click="showAdvancedFilters">
										<i class="ccb-icon-Path-3514 ccb-button-arrow" :class="{'ccb-button-arrow-up': showFilters}"></i>
										<?php esc_html_e( 'More filter', 'cost-calculator-builder' ); ?> {{filterCount}}
									</div>
									<div class="ccb-export-button-wrapper" @click="showExcelMenuClick" ref="excelMenu">
										<i class="ccb-icon-Path-3514 ccb-button-arrow" :class="{'ccb-button-arrow-up': showExcelMenu}"></i>
										<?php esc_html_e( 'Export', 'cost-calculator-builder' ); ?>
									</div>
									<div class="ccb-help-menu-export" v-if="showExcelMenu" >
										<div class="ccb-help-menu-export-item">
											<download-csv class="ccb-help-menu-export-item-text" :fetch="fetchDataExcel" :fields="jsonFields" type="csv" name="orders.csv"> <?php esc_html_e( 'Download CSV', 'cost-calculator-builder' ); ?></download-csv>
										</div>
										<div class="ccb-help-menu-export-item">
											<download-excel class="ccb-help-menu-export-item-text" :fetch="fetchDataExcel" :fields="jsonFields" name="orders.xls"> <?php esc_html_e( 'Download XLS', 'cost-calculator-builder' ); ?></download-excel>
										</div>

									</div>
								</div>
							</div>
						</div>
						<div class="table-display" v-if="showFilters" style="z-index: 2;">
							<div class="table-display--down">
								<div class="ccb-input-wrapper">
									<div class="ccb-input-box ccb-custom-date" style="width: 305px;">
										<custom-date-calendar @set-date="setDate" :field="dateField" ref="datePicker"></custom-date-calendar>
									</div>
								</div>
								<div class="ccb-select-wrapper">
									<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
									<select v-model="sort.payment" class="ccb-select" @change="resetPage">
										<option value="all"><?php esc_html_e( 'All payments', 'cost-calculator-builder' ); ?></option>
										<option value="no_payments" class="hide-if-no-js"><?php esc_html_e( 'No payments', 'cost-calculator-builder' ); ?></option>
										<option value="stripe" class="hide-if-no-js"><?php esc_html_e( 'Stripe', 'cost-calculator-builder' ); ?></option>
										<option value="razorpay" class="hide-if-no-js"><?php esc_html_e( 'Razorpay', 'cost-calculator-builder' ); ?></option>
										<option value="paypal"><?php esc_html_e( 'Paypal', 'cost-calculator-builder' ); ?></option>
										<option value="cash_payment"><?php esc_html_e( 'Cash Payment', 'cost-calculator-builder' ); ?></option>
									</select>
								</div>
								<div class="ccb-select-wrapper">
									<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
									<select v-model="sort.status" class="ccb-select" @change="resetPage">
										<option value="all"><?php esc_html_e( 'Any status', 'cost-calculator-builder' ); ?></option>
										<option value="pending" class="hide-if-no-js"><?php esc_html_e( 'Pending', 'cost-calculator-builder' ); ?></option>
										<option value="complete" class="hide-if-no-js"><?php esc_html_e( 'Complete', 'cost-calculator-builder' ); ?></option>
									</select>
								</div>
								<div class="ccb-select-wrapper">
									<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
									<select v-model="sort.calc_id" class="ccb-select" @change="resetPage" style="padding-right: 20px;">
										<option value="all"><?php esc_html_e( 'All Calculators', 'cost-calculator-builder' ); ?></option>
										<option :value="calc.calc_id" class="hide-if-no-js" v-for="calc in this.calculatorList" :title="calc.calc_title">
											{{ calc.calc_text }}
										</option>
									</select>
								</div>
								<div class="ccb-button-wrapper" @click="resetFilters">
									<?php esc_html_e( 'Reset filters', 'cost-calculator-builder' ); ?> 
								</div>
							</div>
						</div>
						<div class="table-concept ccb-custom-scrollbar" >
							<div class="list-item orders-header" v-show="!noOrders">
								<div class="list-title check">
									<input type="checkbox" class="ccb-pure-checkbox" v-model="selectAll" @change="checkAll">
								</div>
								<div class="list-title sortable id" :class="isActiveSort('id')" @click="setSort('id')">
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'ID', 'cost-calculator-builder' ); ?></span>
								</div>
								<div class="list-title email" >
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'Email', 'cost-calculator-builder' ); ?></span>
								</div>
								<div class="list-title title">
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'Calculator Name', 'cost-calculator-builder' ); ?></span>
								</div>
								<div class="list-title payment">
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'Payment method', 'cost-calculator-builder' ); ?></span>
								</div>
								<div class="list-title sortable total" :class="isActiveSort('total')" @click="setSort('total')">
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'Total', 'cost-calculator-builder' ); ?></span>
								</div>
								<div class="list-title sortable created_at" :class="isActiveSort('created_at')" @click="setSort('created_at')">
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'Date created', 'cost-calculator-builder' ); ?></span>
								</div>
								<div class="list-title sortable status" :class="isActiveSort('status')" @click="setSort('status')">
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'Status', 'cost-calculator-builder' ); ?></span>
								</div>
								<div class="list-title actions">
									<span class="ccb-default-title ccb-light"><?php esc_html_e( 'Actions', 'cost-calculator-builder' ); ?></span>
								</div>
							</div>
							<div class="ccb-table-body--no-content" v-if="preloader">
								<loader></loader>
							</div>
							<orders-empty v-else-if="noOrders && 'list' === step" label="<?php esc_attr_e( 'No Orders yet', 'cost-calculator-builder' ); ?>" description="<?php esc_attr_e( 'Your order list seems to be empty.', 'cost-calculator-builder' ); ?>"></orders-empty>
							<template v-else-if="step === 'list'">
								<orders-item
										v-for="order in getOrders"
										:key="order.id"
										:order="order"
										:detail="order.id === isOrderSelected?.id"
										:selected="order.selected"
										invoice-detail='<?php echo esc_attr( wp_json_encode( $invoice ) ); ?>'
										@set-details="setOrderDetails"
										@order-selected="onSelected"
										@fetch-data="fetchData"
										@generate-pdf="generatePdf"
								></orders-item>
							</template>
							<template v-else>
								<orders-empty label="<?php esc_attr_e( 'No results found', 'cost-calculator-builder' ); ?>" description="<?php esc_attr_e( 'Change search criteria', 'cost-calculator-builder' ); ?>"></orders-empty>
							</template>
						</div>
						<div class="ccb-pagination" v-if="step === 'list' && !noOrders" :class="{'ccb-pagination-scroll': totalPages > 20}">
							<div class="ccb-pages" :class="{'ccb-custom-scrollbar': totalPages > 20}">
								<span class="ccb-page-item" @click="prevPage" v-if="sort.page != 1">
									<i class="ccb-icon-Path-3481 prev"></i>
								</span>
								<span class="ccb-page-item" v-for="n in totalPages" :key="n" :class="{active: n === sort.page}" @click="getPage(n)" :disabled="n == sort.page">{{ n }}</span>
								<span class="ccb-page-item" @click="nextPage" v-if="sort.page != totalPages">
									<i class="ccb-icon-Path-3481"></i>
								</span>
							</div>
							<div class="ccb-bulk-actions" v-if="showBulkActions">
								<div class="ccb-select-wrapper ccb-select-orders">
									<i class="ccb-icon-Path-3485 ccb-select-arrow"></i>
									<select v-model="sort.limit" @change="resetPage" class="ccb-select">
										<option value="5"><?php esc_html_e( '5 orders per page', 'cost-calculator-builder' ); ?></option>
										<option value="10" class="hide-if-no-js"><?php esc_html_e( '10 orders per page', 'cost-calculator-builder' ); ?></option>
										<option value="15" class="hide-if-no-js"><?php esc_html_e( '15 orders per page', 'cost-calculator-builder' ); ?></option>
										<option value="20"><?php esc_html_e( '20 orders per page', 'cost-calculator-builder' ); ?></option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<order-detail
						:selected="isOrderSelected"
						@clear-details="setOrderDetails"
						@export-pdf="generatePdf"
						@show-modal-pdf="showModalSendPdf"
						invoice-texts="<?php echo esc_attr( wp_json_encode( $invoice_texts ) ); ?>"
					>
					</order-detail>
					<send-quote
						ref="sendQuote"
						static-texts='<?php echo wp_json_encode( $send_pdf_texts ); ?>'
						admin-email="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
					>
					</send-quote>
					<invoice
						ref="invoice"
						v-if="this.invoiceOrder"
						invoice-detail='<?php echo esc_attr( wp_json_encode( $invoice ) ); ?>'
						invoice-texts="<?php echo esc_attr( wp_json_encode( $invoice_texts ) ); ?>"
						:order="this.invoiceOrder"
					>
					</invoice>
				</div>
			</div>
		</div>
	</div>
</div>

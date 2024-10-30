<?php

$featuresCCB = array(
	array(
		'feature' => 'Unlimited Forms',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Powerful Calculator Builder',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Customization',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Calculation Formula for Total Element',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Page Builders Compatibility',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Instant Cost Estimation',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'User-friendly Interface',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Vertical view',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Horizontal view',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Two Columns view',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Currency Options',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Calculator Duplicate',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Calculator Import & Export',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Quick Tour',
		'free'    => true,
		'pro'     => true,
	),
	array(
		'feature' => 'Premium Calculator Templates',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Conditional System',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'reCaptcha',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'WooCommerce Checkout',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Stripe Integration',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'PayPal Integration',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Razorpay',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Cash Payment',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Contact Form 7',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Order Form',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Date Picker Custom Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Time Picker Custom Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Multi Range Custom Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'File Upload Custom Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Image Dropdown Custom Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Several forms on one page',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Orders Dashboard',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'PDF Entries',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Image Checkbox & Radio',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Form Estimation Email Template',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'New Styles of Form Elements',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Custom Webhooks',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Backup & Changes Restore',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Auto Backup (last 3 saves)',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Repeater Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Group Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Discounts',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Geolocation Element',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Validated Form',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Sticky calculator',
		'free'    => false,
		'pro'     => true,
	),
	array(
		'feature' => 'Page Breaker (Step-by-Step calculator)',
		'free'    => false,
		'pro'     => true,
	),
);

?>

<div class="container">
	<div class="ccb-upgrade-list">
		<div class="ccb-upgrade-table-info">
			<div class="ccb-upgrade-table-info-title">
				<?php echo esc_html__( 'Free vs Pro', 'cost-calculator-builder' ); ?>
			</div>
			<div class="ccb-upgrade-table-info-desc">
				<?php echo esc_html__( 'Choose the best option. Upgrade to Pro version just for $49', 'cost-calculator-builder' ); ?>
			</div>
		</div>
		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Features', 'cost-calculator-builder' ); ?></th>
					<th class="ccb-table-head"><?php esc_html_e( 'Free', 'cost-calculator-builder' ); ?></th>
					<th class="ccb-table-head"><?php esc_html_e( 'Pro', 'cost-calculator-builder' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $featuresCCB as $item ) : ?>
				<tr>
					<td class="ccb-title"><?php echo esc_html( $item['feature'] ); ?></td>
					<td class="ccb-small-table"><?php echo $item['free'] ? '<span class="ccb-table-icon"><i class="ccb-icon-Octicons"></i></span>' : '<span class="ccb-table-icon ccb-x"><i class="ccb-icon-close-x"></i></span>'; ?></td>
					<td class="ccb-small-table"><?php echo $item['pro'] ? '<span class="ccb-table-icon"><i class="ccb-icon-Octicons"></i></span>' : '<span class="ccb-table-icon ccb-x"><i class="ccb-icon-close-x"></i></span>'; ?></td>
				</tr>
			<?php endforeach; ?>
				<tr class="upgrade-list-action">
					<td>
						<a href="https://stylemixthemes.com/cost-calculator-plugin/pricing/?utm_source=calcadmin&utm_medium=button&utm_campaign=upgradebutton" target="_blank"
							class="upgrade-list-action-btn"><?php echo esc_html__( 'Get Cost Calculator Pro', 'cost-calculator-builder' ); ?></a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

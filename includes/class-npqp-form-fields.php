<?php
$return = array(
	'enabled' => array(
		'title'       => __('Enable/Disable', 'npqp'),
		'label'       => __('Enable QR payments via PayPal', 'npqp'),
		'type'        => 'checkbox',
		'description' => '',
		'default'     => 'no'
	),
	'title' => array(
		'title'       => __('Title', 'npqp'),
		'type'        => 'text',
		'description' => __('This controls the title which the user sees during checkout.', 'npqp'),
		'default'     => __('QR payments via PayPal', 'npqp'),
		'desc_tip'    => true,
	),
	'description' => array(
		'title'       => __('Description', 'npqp'),
		'type'        => 'textarea',
		'description' => __('This controls the description which the user sees during checkout.', 'npqp'),
		'default'     => __('Take QR payments via PayPal on your store.', 'npqp'),
        'desc_tip'    => true,
	),
	'testmode' => array(
		'title'       => __('Sandbox mode', 'npqp'),
		'label'       => __('Enable Sandbox Mode', 'npqp'),
		'type'        => 'checkbox',
		'description' => __('Place the payment gateway in test mode using Sandbox API keys.', 'npqp'),
		'default'     => 'yes',
		'desc_tip'    => true,
	),
	'sandbox_paypal_hosted_settings_title' => array(
		'title'       => __('Sandbox API Credentials', 'npqp'),
		'type'        => 'title',
	),
	'sandbox_api_client_id' => array(
		'title'       => __('Sandbox API Client Id', 'npqp'),
		'type'        => 'text'
	),
	'sandbox_api_secret_id' => array(
		'title'       => __('Sandbox API Client Secret', 'npqp'),
		'type'        => 'text',
	),
	'sandbox_paypal_webhook_id' => array(
		'title'       => __('Sandbox PayPal Webhook Id', 'npqp'),
        'description' => __('Webhook: https://' . $_SERVER['SERVER_NAME'] . '/wc-api/npqp/. Events tracked: * All Events'),
		'type'        => 'text'
	),
	'live_paypal_hosted_settings_title' => array(
		'title'       => __('Live API Credentials', 'npqp'),
		'type'        => 'title',
	),
	'live_api_client_id' => array(
		'title'       => __('Live API Client Id', 'npqp'),
		'type'        => 'text'
	),
	'live_api_secret_id' => array(
		'title'       => __('Live API Client Secret', 'npqp'),
		'type'        => 'text'
	),
	'live_paypal_webhook_id' => array(
		'title'       => __('Live PayPal Webhook Id', 'npqp'),
		'description' => __('Webhook: https://' . $_SERVER['SERVER_NAME'] . '/wc-api/npqp/. Events tracked: * All Events'),
		'type'        => 'text'
	),
	'qr_settings' => array(
		'title'       => __('QR code settings', 'npqp'),
		'type'        => 'title',
	),
	'qr_code_width' => array(
		'title'       => __('Width', 'npqp'),
		'description' => __('The width of the qr code(px).', 'npqp'),
		'default'     => '200',
		'desc_tip'    => true,
		'type'        => 'number'
	),
	'qr_code_height' => array(
		'title'       => __('Height', 'npqp'),
		'description' => __('The height of the qr code(px).', 'npqp'),
		'default'     => '200',
		'desc_tip'    => true,
		'type'        => 'number'
	),
	'qr_code_stroke_color' => array(
		'title'       => __('QR code stroke color', 'npqp'),
		'description' => __('In HEX format. e.g. - #000', 'npqp'),
		'default'     => '#000',
		'desc_tip'    => true,
		'type'        => 'color'
	),
	'detail_settings_title' => array(
		'title'       => __('Invoice details', 'npqp'),
		'type'        => 'title',
	),
	'detail_note' => array(
		'title'       => __('Detail note', 'npqp'),
		'description' => __('e.g. - Thank you for your purchase!', 'npqp'),
		'desc_tip'    => true,
		'default'     => __('Thank you for your purchase!', 'npqp'),
		'type'        => 'text'
	),
	'detail_term' => array(
		'title'       => __('Detail term', 'npqp'),
		'default'     => __('No refunds after 30 days.', 'npqp'),
		'type'        => 'text'
	),
	'detail_memo' => array(
		'title'       => __('Detail memo', 'npqp'),
		'default'     => __('This is a long contract', 'npqp'),
		'type'        => 'text'
	),
	'additional_notes' => array(
		'title'       => __('Additional notes', 'npqp'),
		'description' => __('Additional notes about your store', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'tax_id' => array(
		'title'       => __('Tax id', 'npqp'),
		'type'        => 'text'
	),
	'invoicer_settings_title' => array(
		'title'       => __('Invoicer data', 'npqp'),
		'type'        => 'title',
	),
	'given_name' => array(
		'title'       => __('Given name', 'npqp'),
		'description' => __('e.g. - Vitaly', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'surname' => array(
		'title'       => __('Surname', 'npqp'),
		'description' => __('e.g. - Mironov', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'address_line_1' => array(
		'title'       => __('Address 1', 'npqp'),
		'description' => __('e.g. - 1234 First Street', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'address_line_2' => array(
		'title'       => __('Address 2', 'npqp'),
		'description' => __('e.g. - 12345 Hillside Court', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'admin_area_2' => array(
		'title'       => __('City', 'npqp'),
		'description' => __('e.g. - Anytown', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'admin_area_1' => array(
		'title'       => __('State', 'npqp'),
		'description' => __('e.g. - CA', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'country_code' => array(
		'title'       => __('Country code', 'npqp'),
		'description' => __('e.g. - US', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'postal_code' => array(
		'title'       => __('Postal code', 'npqp'),
		'description' => __('e.g. - 98765', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'phone_country_code' => array(
		'title'       => __('Phone Country code', 'npqp'),
		'description' => __('e.g. - 001', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'number'
	),
	'national_number' => array(
		'title'       => __('National number', 'npqp'),
		'description' => __('e.g. - 4085551234', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'phone_type' => array(
		'title'       => __('Phone type', 'npqp'),
		'default'     => 'MOBILE',
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'MOBILE'   => 'MOBILE',
			'HOME'     => 'HOME',
			'FAX'      => 'FAX',
			'PAGER'    => 'PAGER',
			'OTHER'    => 'OTHER',
		),
	),
	'email_address' => array(
		'title'       => __('Email address', 'npqp'),
		'description' => __('e.g. - merchant@example.com', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'email'
	),
	'website' => array(
		'title'       => __('Website', 'npqp'),
		'description' => __('The invoicer\'s website. e.g. - http://site.com', 'npqp'),
		'default'     => 'http://' . $_SERVER['SERVER_NAME'],
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'logo_url' => array(
		'title'       => __('Logo url', 'npqp'),
		'description' => __('The full URL to an external logo image. The logo image must not be larger than 250 pixels wide by 90 pixels high. Https required.', 'npqp'),
		'desc_tip'    => true,
		'type'        => 'text'
	),
	'configuration_term' => array(
		'title'       => __('Term', 'npqp'),
		'type'        => 'title',
	), 
	'term_type' => array(
		'title'       => __('Term type', 'npqp'),
		'default'     => 'DUE_ON_RECEIPT',
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'DUE_ON_RECEIPT'          => 'DUE_ON_RECEIPT',
			'DUE_ON_DATE_SPECIFIED'   => 'DUE_ON_DATE_SPECIFIED',
			'NET_10'                  => 'NET_10',
			'NET_15'                  => 'NET_15',
			'NET_30'                  => 'NET_30',
			'NET_45'                  => 'NET_45',
			'NET_60'                  => 'NET_60',
			'NET_90'                  => 'NET_90',
			'NO_DUE_DATE'             => 'NO_DUE_DATE',
		),
	),
	'due_date' => array(
		'title'       => __('Due date(days)', 'npqp'),
		'description' => __('e.g. - 30', 'npqp'),
		'desc_tip'    => true,
		'default'     => 0,
		'type'        => 'number'
	),
	'custom_amount' => array(
		'title'       => __('Custom amount', 'npqp'),
		'type'        => 'title',
	),
	'custom_amount_inclusive' => array(
		'title'       => __('Custom amount inclusive', 'npqp'),
		'type'        => 'checkbox',
		'default'     => 'no'
	),
	'custom_amount_label' => array(
		'title'       => __('Label', 'npqp'),
		'type'        => 'text',
		'description' => __('e.g. - Packing Charges', 'npqp'),
		'desc_tip'    => true,
	),
	'custom_amount_currency_code' => array(
		'title'       => __('Currency code', 'npqp'),
		'type'        => 'text',
		'description' => __('e.g. - USD', 'npqp'),
		'desc_tip'    => true,
	),
	'custom_amount_value' => array(
		'title'       => __('Currency value', 'npqp'),
		'type'        => 'text',
		'description' => __('e.g. - 10.00', 'npqp'),
		'desc_tip'    => true,
	),
	'configuration_tax' => array(
		'title'       => __('Tax', 'npqp'),
		'type'        => 'title',
	),
	'tax_inclusive' => array(
		'title'       => __('Tax inclusive', 'npqp'),
		'type'        => 'checkbox',
		'default'     => 'no'
	),
	'tax_name' => array(
		'title'       => __('Tax name', 'npqp'),
		'type'        => 'text',
		'description' => __('e.g. - Sales Tax', 'npqp'),
		'desc_tip'    => true,
	),
	'tax_percent' => array(
		'title'       => __('Tax percent', 'npqp'),
		'type'        => 'text',
		'description' => __('e.g. - 7.25', 'npqp'),
		'desc_tip'    => true,
	),
	'tax_calculated_after_discount' => array(
		'title'       => __('Tax calculated after discount', 'npqp'),
		'type'        => 'checkbox',
		'default'     => 'no'
	),
	'configuration_title' => array(
		'title'       => __('Configuration', 'npqp'),
		'type'        => 'title',
	),
	'unit_of_measure' => array(
		'title'       => __('Unit of measure', 'npqp'),
		'default'     => 'QUANTITY',
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'QUANTITY' => 'QUANTITY',
			'HOURS'    => 'HOURS',
			'AMOUNT'   => 'AMOUNT',
		),
	),
	'billing_phone_type' => array(
		'title'       => __('Billing phone type', 'npqp'),
		'default'     => 'MOBILE',
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'MOBILE'   => 'MOBILE',
			'HOME'     => 'HOME',
			'FAX'      => 'FAX',
			'PAGER'    => 'PAGER',
			'OTHER'    => 'OTHER',
		),
	),
	'allow_tip' => array(
		'title'       => __('Allow tip', 'npqp'),
		'type'        => 'checkbox',
		'default'     => 'no'
	),
	'product_description' => array(
		'title'       => __('Short product description', 'npqp'),
		'type'        => 'checkbox',
		'description' => __('Show short product description in invoice', 'npqp'),
		'desc_tip'    => true,
		'default'     => 'no'
	),
);

$return = apply_filters( 'npqp_form_fields', $return ); // filter

return $return;


<?php

//debug($order, 1);

if ( !$order ){
    wp_die('Not isset order data');
    return;
} else if ( !$fields ) {
    wp_die('Not isset order data');
    return;
}

$billing           = $order->data['billing'];
$country_code_res  = $NPQP_Country_Code->getCountryCode( $billing['phone'] );
$b_country_code    = $country_code_res['country_code'];
$b_national_number = $country_code_res['number'];

$shipping          = $order->data['shipping'];

$invoice_number    = $order->id;
$currency_code     = $order->data['currency'] ? $order->data['currency'] : 'USD';
$due_days          = $fields['due_date'] ? (int)$fields['due_date'] : 0;
$due_date          = date('Y-m-d', strtotime ('+' . $due_days . ' day'));

$site_url          = $_SERVER['SERVER_NAME'];
$unit_of_measure   = $fields['unit_of_measure'] ? $fields['unit_of_measure'] : 'QUANTITY';

$amount = [
    "breakdown" => [
        "shipping" => [
            "amount" => [
                "currency_code" => $currency_code,
                "value"         => npqppriceFormat( (string)$order->data['shipping_total'] )
            ],
        ],
        "discount" => [ 
            "invoice_discount" => [
                "amount" => [
                    "currency_code" => $currency_code,
                    "value"         => npqppriceFormat( (string)$order->data['discount_total'] )
                ],
            ]
        ]
    ]
];

if ( $fields['tax_inclusive'] == 'yes' ) {
    $amount['breakdown']['shipping']["tax"] = [
        "name"    => $fields['tax_name'],
        "percent" => $fields['tax_percent'],
    ];
}

if ( $fields['custom_amount_inclusive'] == 'yes' ) {
    $currency_code = $fields['custom_amount_currency_code'] ? $fields['custom_amount_currency_code'] : 'USD';
    $amount['breakdown']['custom']['label']  = $fields['custom_amount_label'];
    $amount['breakdown']['custom']['amount'] = [
        "currency_code" => $currency_code,
        "value"         => $fields['custom_amount_value'],
    ];
}

$items = [];
$i = 0;
$order_items = $order->get_items();

if ( !is_wp_error( $order_items ) ) {
    
	foreach( $order_items as $item_id => $order_item ) {
	    
	    $product  = $order_item->get_product();
        
        $total = $product->get_sale_price() ? $product->get_sale_price() : $product->get_regular_price();
        $total = npqppriceFormat( (string)$total );
        
        if ( $product->get_sale_price() ) {
            $discount_amount = ( ( $product->get_regular_price() - $product->get_sale_price() ) * $order_item->get_quantity() );
            $discount_amount = npqppriceFormat( (string)$discount_amount );
        } else {
            $discount_amount = 0;
        }
        
	    $items[$i]['name'] = $order_item->get_name();
	    
	    if ( $fields['product_description'] == 'yes' ) {
	        $items[$i]['description'] = $product->get_short_description();
	    } else {
	        $items[$i]['description'] = "";
	    }
	    
	    $items[$i]['quantity']          = $order_item->get_quantity();
	    $items[$i]['get_regular_price'] = $product->get_regular_price(); // debug
	    $items[$i]['get_sale_price']    = $product->get_sale_price(); // debug
	    
	    $items[$i]['unit_amount'] = [
	        "currency_code" => $currency_code,
            "value"         => npqppriceFormat( $product->get_regular_price() )
	    ];
	    
	    $items[$i]['discount'] = [
	        "amount" => [
                "currency_code" => $currency_code,
                "value"         => $discount_amount,
             ]
	    ];
	    $items[$i]['unit_of_measure'] = $unit_of_measure;
	    
	    
        if ( $fields['tax_inclusive'] == 'yes' ) {
            $items[$i]['tax'] = [
    	        "name" => $fields['tax_name'],
                "percent" => $fields['tax_percent'],
    	    ];
        }

	    $i++;
	}
}

//debug($items, 1);

$primary_recipients = [
    [
        "billing_info" => [
            "name" => [
                "given_name" => $billing['first_name'],
                "surname"    => $billing['last_name'],
            ],
            "address" => [
                "address_line_1" => $billing['address_1'],
                "admin_area_2"   => $billing['city'],
                "admin_area_1"   => $billing['state'],
                "postal_code"    => $billing['postcode'],
                "country_code"   => $billing['country'],
            ],
            "email_address" => $billing['email'],
            "phones" => [
                [
                    "country_code"    => $b_country_code,
                    "national_number" => $b_national_number,
                    "phone_type"      => $fields['billing_phone_type'] ? $fields['billing_phone_type'] : 'MOBILE',
                ]
            ],
        ],
        "shipping_info" => [
            "name" => [
                "given_name" => $shipping['first_name'],
                "surname"    => $shipping['last_name'],
            ],
            "address" => [
                "address_line_1" => $shipping['address_1'],
                "admin_area_2"   => $shipping['city'],
                "admin_area_1"   => $shipping['state'],
                "postal_code"    => $shipping['postcode'],
                "country_code"   => $shipping['country'],
            ],
        ]
    ]
];

// детали платежа
$detail = [
    "invoice_number"   => "$invoice_number",
    "reference"        => $site_url,
    "invoice_date"     => date('Y-m-d'),
    "currency_code"    => $currency_code,
    "note"             => $fields['detail_note'],
    "term"             => $fields['detail_term'],
    "memo"             => $fields['detail_memo'],
    "additional_notes" => $fields['additional_notes'],
    "tax_id"           => $fields['tax_id'],
    "payment_term" => [
        "term_type" => $fields['term_type'],
        "due_date" => $due_date,
    ]
];

// информация о магазине
$invoicer = [
    "name" => [
        "given_name" => $fields['given_name'],
        "surname"    => $fields['surname'],
    ],
    "address" => [
        "address_line_1" => $fields['address_line_1'],
        "address_line_2" => $fields['address_line_2'],
        "admin_area_2"   => $fields['admin_area_2'],
        "admin_area_1"   => $fields['admin_area_1'],
        "postal_code"    => $fields['postal_code'],
        "country_code"   => $fields['country_code'],
    ],
    "email_address" => $fields['email_address'],
    "phones" => [
        [
            "country_code"    => $fields['phone_country_code'],
            "national_number" => $fields['national_number'],
            "phone_type"      => $fields['phone_type'],
        ]
    ],
    "website"  => $fields['website'],
    "logo_url" => $fields['logo_url'],
];

$configuration = [
    "allow_tip"                     => $fields['allow_tip'] == "yes" ? true : false,
    "tax_calculated_after_discount" => $fields['tax_calculated_after_discount'] == "yes" ? true : false,
    "tax_inclusive"                 => $fields['tax_inclusive'] == "yes" ? true : false,
];

$return = [
    'detail'             => $detail,
    'invoicer'           => $invoicer,
    'primary_recipients' => $primary_recipients,
    'items'              => $items,
    'configuration'      => $configuration,
    'amount'             => $amount,
];

$return = apply_filters( 'npqp_invoice_data', $return ); // filter

//npqpLog('invoicer all return', $return);

//debug($return, 1);

// price format
function npqppriceFormat($price){
    return str_replace( [',', ' '], '', number_format( $price, 5 ) );
}

return $return;
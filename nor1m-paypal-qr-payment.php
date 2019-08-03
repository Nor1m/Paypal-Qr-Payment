<?php
/*
 * Plugin Name: QR payments via PayPal
 * Description: Take QR payments via PayPal on your store.
 * Author: Nor1mSoft
 * Author URI: http://nor1m.ru
 * Version: 1.0.0
 */

/*
 * Translates
 */
add_action( 'plugins_loaded', 'npqp_init' );
function npqp_init(){
    load_plugin_textdomain( 'npqp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/*
 * Creating a page with a qr code when activating the plugin
 */
register_activation_hook( __FILE__, 'npqp_plugin_activate' );
function npqp_plugin_activate(){
    
    $title = "QR Payment";
    $content  = '<p id="qr_code_img" style="text-align: center;">[NPQP-QR-CODE-PAGE]</p>';
    $content .= '<p style="text-align: center; display: inline-block; position: relative; margin-top: -10px; width: 100%;">';
    $content .=     '<img style="display: inline-block;" src="'. plugins_url('assets/img/processing.gif', __FILE__) .'" />';
    $content .= '</p>';
    $content .= '<b style="text-align: center; display: inline-block; position: relative; top: -30px; width: 100%; font-size: 16px; color: #565656;">Payment is pending</b>';
    
	if ( ! post_exists( $title ) ) {
        $post_id = wp_insert_post(array(
            'post_type'         => 'page',
            'post_title'        => $title,
            'post_content'      => $content,
            'post_status'       => 'publish',
            'post_author'       => 1,
            'post_name'         => 'qr-payments',
        ));
	}
}

require_once dirname( __FILE__ ) . '/includes/class-npqp-paypal-api.php';

/*
* This action hook registers our PHP class as a WooCommerce payment gateway
*/
add_filter( 'woocommerce_payment_gateways', 'NPQP_add_gateway_class' );
function NPQP_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_NPQP_Gateway';
	return $gateways;
}

// admin styles
wp_enqueue_style( 'npqp_admin_css', plugins_url('assets/css/admin.css', __FILE__) );

// qr code generator
wp_enqueue_script( 'qrcode', plugins_url('assets/js/qrcode.min.js', __FILE__) );

// frontend styles
wp_enqueue_style( 'npqp_frontend_css', plugins_url('assets/css/frontend.css', __FILE__) );

/*
* The class itself, please note that it is inside plugins_loaded action hook
*/
add_action( 'plugins_loaded', 'NPQP_init_gateway_class' );
function NPQP_init_gateway_class() {
 
    // check ajax status
    if ( isset($_GET['npqp_ajax_url']) ) {
    	echo npqp_ajax();
    	die(200);
    }

    class WC_NPQP_Gateway extends WC_Payment_Gateway {

        public function __construct() {
            
            $this->id                 = 'npqp'; // payment gateway plugin ID
        	$icon                     = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $icon                     = apply_filters( 'npqp_icon', $icon ); // filter
            $this->icon               = $icon;
        	$this->has_fields         = true; // in case you need a custom credit card form
        	$this->method_title       = __('QR payments via PayPal', 'npqp');
        	$this->method_description = __('Payment with qr code', 'npqp'); // will be displayed on the options page
            $this->form_fields        = require dirname( __FILE__ ) . '/includes/class-npqp-form-fields.php';

            include_once dirname( __FILE__ ) . '/includes/class-npqp-country-code.php';

        	// gateways can support subscriptions, refunds, saved payment methods,
        	// but in this tutorial we begin with simple payments
        	$this->supports = array(
        		'products'
        	);
         
        	// Load the settings.
        	$this->init_settings();
        	$this->title                    = $this->get_option( 'title' );
        	$this->description              = $this->get_option( 'description' );
        	$this->enabled                  = $this->get_option( 'enabled' );
        	$this->testmode                 = 'yes' === $this->get_option( 'testmode' );
        	$this->api_client_id            = $this->testmode ? $this->get_option( 'sandbox_api_client_id' ) : $this->get_option( 'live_api_client_id' );
        	$this->api_secret_id            = $this->testmode ? $this->get_option( 'sandbox_api_secret_id' ) : $this->get_option( 'live_api_secret_id' );
        	$this->paypal_webhook_id        = $this->testmode ? $this->get_option( 'sandbox_paypal_webhook_id' ) : $this->get_option( 'live_paypal_webhook_id' );
        	
        	$this->qr_code_page_url         = $this->get_option( 'qr_code_page_url' ) ? $this->get_option( 'qr_code_page_url' ) : "qr-code-page";
        	$this->qr_code_width            = $this->get_option( 'qr_code_width' ) ? $this->get_option( 'qr_code_width' ) : "200";
        	$this->qr_code_height           = $this->get_option( 'qr_code_height' ) ? $this->get_option( 'qr_code_height' ) : "200";
        	$this->qr_code_stroke_color     = $this->get_option( 'qr_code_stroke_color' ) ? $this->get_option( 'qr_code_stroke_color' ) : "#00000";
        	
            // and this is our custom JS in your plugin directory that works with token.js
            wp_enqueue_script( 'npqp_main_js', plugins_url( 'assets/js/main.js', __FILE__ ), array('jquery') );

            if ( is_admin() && isset($_GET['section']) && $_GET['section'] == 'npqp' ) {
                wp_localize_script('jquery', 'nqpp_l18n', array(
                    'Visit site' => __('Visit site', 'npqp'),
                    'Donate'     => __('Donate', 'npqp')
                ));
                wp_enqueue_script( 'npqp_admin_js', plugins_url( 'assets/js/admin.js', __FILE__ ), array('jquery') );
            }

        	// in most payment processors you have to use PUBLIC KEY to obtain a token
            wp_localize_script( 'npqp_main_js', 'npqp_params', array(
            	'qr_code_page_url'         => $this->qr_code_page_url,
            	'qr_code_width'            => $this->qr_code_width,
            	'qr_code_height'           => $this->qr_code_height,
            	'qr_code_stroke_color'     => $this->qr_code_stroke_color,
            	'qr_code_background_color' => $this->qr_code_background_color,
            	'npqp_ajax_url'            => home_url('/?npqp_ajax_url'),
            	'is_qrcodepage'            => is_qrcodepage()
            ));
        	
        	// This action hook saves the settings
        	add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [
        	    $this, 'process_admin_options'
            ]);
         
        	// You can also register a webhook here
        	add_action( 'woocommerce_api_npqp', [ $this, 'webhook' ] );
        	
        	if ( isset($_GET['npqrdbg']) ) {
        	    //...
            }

        }
        
        /**
        * Payment fields in checkout
        */
        public function payment_fields() {
        	if ( $this->description ) {
        		if ( $this->testmode ) {
        			$this->description  = trim( $this->description );
        		}
        		echo wpautop( wp_kses_post( $this->description ) );
        	}
        }
        
        /*
        * Get PayPal payment link
        */
        public function get_payment_link($order){
            
            $PayPalApi = new NPQP_PayPal_API([
                'api_client_id'     => $this->api_client_id,
                'api_secret_id'     => $this->api_secret_id,
                'paypal_webhook_id' => $this->paypal_webhook_id,
                'order'             => $order,
                'fields'            => $this->settings
            ]);
            
            return $PayPalApi->getPaymentLink();
        }
        
        /*
        * Fields validation
        */
        public function validate_fields() {
            
            $validate = true;
            $post = $_POST;
            $post = apply_filters( 'npqp_validate_fields_post', $post ); // filter
            
            if( empty( $post['billing_first_name'] ) ) {
        		wc_add_notice( __('First name is required!', 'npqp'), 'error' );
        		$validate = false;
        	}
        	
            $validate = apply_filters( 'npqp_validate_fields_flag', $validate ); // filter
        	return $validate;
        }
    
        /*
        * We're processing the payments here
        */
        public function process_payment( $order_id ) {
            
            global $woocommerce;
         
        	// we need it to get any order detailes
        	$order = wc_get_order( $order_id );
        	
            $payment_link = $this->get_payment_link($order);
            
            if ( isset($payment_link['status']) && $payment_link['status'] == 'error' ) {
                $error = $payment_link['error'];
                $error = apply_filters( 'npqp_error_text', $error ); // filter
                wc_add_notice( $error , 'error' );
        		return;
            }
            
            if ( !$payment_link ) {
                npqpLog('process_payment No valid payment link', [
                    'order_id' => $order_id,
                    'payment_link' => $payment_link,
                ]);
                wc_add_notice( __('No valid payment link', 'npqp'), 'error' );
        		return;
            }
            
            // Redirect to the qr code page
            $redirect_link = $this->qr_code_page_url ? $this->qr_code_page_url : 'qr-payments';
    		$redirect_link = '/' . $redirect_link . '/?payment_link=' . htmlentities(urlencode(esc_url($payment_link))) . '&order_id=' . (int)$order_id;
    		$redirect_link = home_url( $redirect_link );
            $redirect_link = apply_filters( 'npqp_redirect_link', $redirect_link ); // filter
                                
    		npqpLog('process_payment', [
                'order_id'      => $order_id,
                'payment_link'  => $payment_link,
                'redirect_link' => $redirect_link,
            ]);
            
            // redirect to qr page
			return array(
				'result'   => 'success',
				'redirect' => $redirect_link
			);
        }
    
        /*
        * In case you need a webhook, like PayPal IPN etc
        */
        public function webhook() {
    		
    		$post = json_decode( file_get_contents('php://input'), 1 );
            
            if ( isset($post['event_type']) && $post['event_type'] == 'INVOICING.INVOICE.PAID' ) {
                
                $PayPalApi = new NPQP_PayPal_API([
                    'api_client_id'     => $this->api_client_id,
                    'api_secret_id'     => $this->api_secret_id,
                    'paypal_webhook_id' => $this->paypal_webhook_id
                ]);
                
                $verify = $PayPalApi->verifyWebhook();
                
                npqpLog('webhook', [
                    'verify' => $verify,
                ]);
                
                if ( $verify == 'SUCCESS' ) {
                    
                    $webhook_event_arr = json_decode( file_get_contents('php://input'), true );
                    $status_success = 'PAID';
                    $status_success = apply_filters( 'npqp_status_success', $status_success ); // filter

                    if ( $webhook_event_arr['resource']['invoice']['status'] != $status_success ) {
                        return;
                    }
                    
                    npqpLog('verifyWebhook $webhook_event_arr', [
                        '$webhook_event_arr' => $webhook_event_arr,
                    ]);

                    if ( isset($webhook_event_arr['resource']['invoice']['detail']['invoice_number']) ){
                        
                        $invoice_number = $webhook_event_arr['resource']['invoice']['detail']['invoice_number'];
                        
                        // get clear invoice id
                        if ( strpos($invoice_number, '-') !== false ) {
                            $invoice_number = explode('-', $invoice_number)[0];
                        }

                        $order = wc_get_order( $invoice_number );
                        
                        $order = apply_filters( 'npqp_order', $order ); // filter
                        
                    	if ( $order ) {
                    	    
                    	    if ( ! $order->is_paid() ) {
                                global $woocommerce;
                        	    $order->payment_complete();
                            	$order->reduce_order_stock();
                                $woocommerce->cart->empty_cart();
                                
                                $add_order_note = __('Hey, your order is paid! Thank you!', 'npqp');
                                $add_order_note = apply_filters( 'npqp_add_order_note', $add_order_note ); // filter
                        		$order->add_order_note( $add_order_note );
                                
                                npqpLog('verifyWebhook', [
                                    'payment complete' => $order->is_paid(),
                                ]);
                    	    }
                    	    
                    	} else {
                            npqpLog('verifyWebhook', [
                                'Incorect invoice number' => $invoice_number,
                            ]);
                    	}
                    }
                } else {
                    npqpLog('webhook FAILURE');
                }
            }
            
        }
        
    }
}

// шорткод страницы с qr кодом
add_shortcode( 'NPQP-QR-CODE-PAGE', 'NPQP_QR_code_page' );
function NPQP_QR_code_page() {
    
	if ( isset($_GET['order_id']) ){
    	$order = wc_get_order( $_GET['order_id'] );
    	if ( $order ) {
            $return_url = $order->get_checkout_order_received_url();
    	} else {
            $return_url = home_url();
    	}
	} else {
        $return_url = home_url();
	}
	
    echo '<div id="wc-npqp-qr-form" class="wc-npqp-qr-form">';
         
	do_action( 'npqp_qr_form_before', 'npqp' ); // action
	
	echo  '<div class="npqp-qr-form-wrapper">
	        <input type="hidden" id="npqp-order-id" value="'. esc_attr( urldecode( $_GET['order_id'] ) ) .'">
	        <input type="hidden" id="npqp-return-url" value="'. esc_attr( urldecode( $return_url ) ) .'">
	        <input type="hidden" id="npqp-qrcode-text" value="'. esc_attr( urldecode( $_GET['payment_link'] ) ) .'">
    	    <a target="_blank" href="'. esc_attr( urldecode( $_GET['payment_link'] ) ) .'"><div id="npqp-qrcode"></div></a>
        	<a class="npqp-paynow-btn onlymob" target="_blank" href="'. esc_attr( urldecode( $_GET['payment_link'] ) ) .'">Pay Now</a>
    	  </div>';
 
	do_action( 'npqp_qr_form_after', 'npqp' ); // action
 
	echo '<div class="clear"></div>';
}

// npqp ajax       
function npqp_ajax() {
    if( empty( $_GET['action'] ) ) {
        return __('Action is missed', 'npqp');
    }
    if ( $_GET['action'] == 'getorderpaidstatus' ) {
        return npqp_order_is_paid($_GET['order_id']);
    }
}

// order is paid
function npqp_order_is_paid($id){
    $post = get_post($id);
    if ( isset($post->post_status) ){
        return ($post->post_status == 'wc-processing' || $post->post_status == 'wc-completed') ? 1 : 0;
    } else {
        return __('Invalid order id', 'npqp');
    }
}

// debug
function npqpDebug($data, $die = false) {
    echo "<pre>"; var_dump($data); echo "</pre>"; 
    if ($die) exit(200);
}

function is_qrcodepage(){
    return wc_post_content_has_shortcode( 'NPQP-QR-CODE-PAGE' );
}

// log
function npqpLog($title, $data = false){
    $filename = dirname( __FILE__ ) . '/log.txt';
    if ( !$data ) {
        file_put_contents( $filename, date('d.m.Y/H:i:s') . ': ' . $_SERVER['REMOTE_ADDR'] . ': ' . $title . PHP_EOL, FILE_APPEND );
    } else {
        file_put_contents( $filename, date('d.m.Y/H:i:s') . ': ' . $_SERVER['REMOTE_ADDR'] . ': ' . $title . PHP_EOL . var_export($data, true) . PHP_EOL, FILE_APPEND );
    }
}
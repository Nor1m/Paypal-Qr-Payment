<?php
/*
 * Plugin Name: WooCommerce QR payments via PayPal
 * Description: Take QR payments via PayPal on your store.
 * Author: Vitaly Mironov
 * Author URI: http://nor1m.ru
 * Version: 0.9
 */

if (!@npqp_is_woocommerce_active()) {
    return false;
}

function npqp_is_woocommerce_active()
{
    static $active_plugins;
    if (!isset($active_plugins)) {
        $active_plugins = (array)get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
    }
    return
        in_array('woocommerce/woocommerce.php', $active_plugins) ||
        array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}

add_filter( 'plugin_action_links', 'npqp_plugin_action_links', 10, 2 );
function npqp_plugin_action_links( $actions, $plugin_file ){
    if( false === strpos( $plugin_file, basename(__FILE__) ) )
        return $actions;
    $settings_link = '<a href="/wp-admin/admin.php?page=wc-settings&tab=checkout&section=npqp">' . __('Settings', 'npqp') . '</a>';
    array_unshift( $actions, $settings_link );
    $settings_link = '<a target="_blank" href="https://nor1m.ru/shop/qr-manual?from=plugins">' . __('Guide', 'npqp') . '</a>';
    array_unshift( $actions, $settings_link );
    return $actions;
}

/*
 * Translates
 */
add_action('plugins_loaded', 'npqp_init');
function npqp_init()
{
    load_plugin_textdomain('npqp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

/*
 * Qr code route
 */
add_filter('template_include', function ($template) {
    global $wp;

    if (is_page('qr-payments') || $wp->request == 'qr-payments') {
        header("HTTP/1.1 200 OK");

        $template_checkout = get_theme_file_path('/templates/npqp-checkout.php');

        if ( file_exists($template_checkout) ) {
            return $template_checkout;
        } else {
            return plugin_dir_path(__FILE__) . 'templates/npqp-checkout.php';
        }
    }

    return $template;
});

require_once dirname(__FILE__) . '/includes/class-npqp-paypal-api.php';

/*
* This action hook registers our PHP class as a WooCommerce payment gateway
*/
add_filter('woocommerce_payment_gateways', 'NPQP_add_gateway_class');
function NPQP_add_gateway_class($gateways)
{
    $gateways[] = 'WC_NPQP_Gateway';
    return $gateways;
}

// правильный способ подключить стили и скрипты
add_action('admin_enqueue_scripts', 'npqp_wp_enqueue_scripts');
add_action('wp_enqueue_scripts', 'npqp_wp_enqueue_scripts');
function npqp_wp_enqueue_scripts () {
    // admin styles
    wp_enqueue_style('npqp_admin_css', plugins_url('assets/css/admin.css', __FILE__));
    // qr code generator
    wp_enqueue_script('qrcode', plugins_url('assets/js/qrcode.min.js', __FILE__));
    // frontend styles
    wp_enqueue_style('npqp_frontend_css', plugins_url('assets/css/frontend.css', __FILE__));
    // frontend scripts
    wp_enqueue_script('npqp_main_js', plugins_url('assets/js/main.js', __FILE__), array('jquery'));
    wp_enqueue_script('jquery.bind', plugins_url('assets/js/jquery.bind.js', __FILE__), array('jquery'));
    wp_enqueue_script('jquery.phone', plugins_url('assets/js/jquery.phone.js', __FILE__), array('jquery'));
    wp_enqueue_script('jquery.inputmask', plugins_url('assets/js/jquery.inputmask.js', __FILE__), array('jquery'));
}

/*
* The class itself, please note that it is inside plugins_loaded action hook
*/
add_action('plugins_loaded', 'NPQP_init_gateway_class');
function NPQP_init_gateway_class()
{

    // check ajax status
    if (isset($_GET['npqp_ajax_url'])) {
        echo npqp_ajax();
        die(200);
    }

    class WC_NPQP_Gateway extends WC_Payment_Gateway
    {

        public $id;
        public $title;
        public $icon;
        public $has_fields;
        public $method_title;
        public $method_description;
        public $form_fields;
        public $description;
        public $enabled;
        public $testmode;
        public $api_client_id;
        public $api_secret_id;
        public $paypal_webhook_id;
        public $qr_code_page_url;
        public $qr_code_width;
        public $qr_code_height;
        public $qr_code_stroke_color;
        public $qr_code_background_color;
        public $supports;

        public function __construct()
        {

            $this->id = 'npqp'; // payment gateway plugin ID
            $icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $icon = apply_filters('npqp_icon', $icon); // filter
            $this->icon = $icon;
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = __('QR payments via PayPal', 'npqp');
            $this->method_description = __('Payment with qr code', 'npqp'); // will be displayed on the options page
            $this->form_fields = require dirname(__FILE__) . '/includes/class-npqp-form-fields.php';

            include_once dirname(__FILE__) . '/includes/class-npqp-country-code.php';

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');
            $this->testmode = 'yes' === $this->get_option('testmode');
            $this->api_client_id = $this->testmode ? $this->get_option('sandbox_api_client_id') : $this->get_option('live_api_client_id');
            $this->api_secret_id = $this->testmode ? $this->get_option('sandbox_api_secret_id') : $this->get_option('live_api_secret_id');
            $this->paypal_webhook_id = $this->testmode ? $this->get_option('sandbox_paypal_webhook_id') : $this->get_option('live_paypal_webhook_id');

            $this->qr_code_page_url = "qr-payments";
            $this->qr_code_width = $this->get_option('qr_code_width') ? $this->get_option('qr_code_width') : "200";
            $this->qr_code_height = $this->get_option('qr_code_height') ? $this->get_option('qr_code_height') : "200";
            $this->qr_code_stroke_color = $this->get_option('qr_code_stroke_color') ? $this->get_option('qr_code_stroke_color') : "#00000";
            $this->qr_code_background_color = "#fff";

            add_action( 'wp_enqueue_scripts', function(){
                wp_localize_script('npqp_main_js', 'npqp_params', array(
                    'qr_code_page_url' => $this->qr_code_page_url,
                    'qr_code_width' => $this->qr_code_width,
                    'qr_code_height' => $this->qr_code_height,
                    'qr_code_stroke_color' => $this->qr_code_stroke_color,
                    'qr_code_background_color' => $this->qr_code_background_color,
                    'npqp_ajax_url' => home_url('/?npqp_ajax_url'),
                    'npqp_plugin_js_url' => plugins_url('assets/js', __FILE__),
                    'is_qrcodepage' => is_qrcodepage()
                ));
            });

            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [
                $this, 'process_admin_options'
            ]);

            // You can also register a webhook here
            add_action('woocommerce_api_npqp', [$this, 'webhook']);

        }

        /**
         * Payment fields in checkout
         */
        public function payment_fields()
        {
            if ($this->description) {
                echo wpautop(wp_kses_post(trim($this->description)));
            }
        }

        /**
         * Get PayPal payment link
         * @param $order
         * @return array|bool|string|string[]
         */
        public function get_payment_link($order)
        {
            $PayPalApi = new NPQP_PayPal_API([
                'testmode' => $this->testmode,
                'api_client_id' => $this->api_client_id,
                'api_secret_id' => $this->api_secret_id,
                'paypal_webhook_id' => $this->paypal_webhook_id,
                'order' => $order,
                'fields' => $this->settings
            ]);

            return $PayPalApi->getPaymentLink();
        }

        /**
         * Fields validation
         * @return mixed
         */
        public function validate_fields()
        {
            $validate = true;
            $validate = apply_filters('npqp_validate_fields_flag', $validate); // filter
            return $validate;
        }

        /**
         * We're processing the payments here
         * @param $order_id
         * @return array|void
         */
        public function process_payment($order_id)
        {

            global $woocommerce;

            // we need it to get any order detailes
            $order = wc_get_order($order_id);

            $payment_link = $this->get_payment_link($order);

            if (isset($payment_link['status']) && $payment_link['status'] == 'error') {
                $error = $payment_link['error'];
                $error = apply_filters('npqp_error_text', $error); // filter
                wc_add_notice($error, 'error');
                return;
            }

            if (!$payment_link) {
                npqpLog('process_payment No valid payment link', [
                    'order_id' => $order_id,
                    'payment_link' => $payment_link,
                ]);
                wc_add_notice(__('No valid payment link', 'npqp'), 'error');
                return;
            }

            // Redirect to the qr code page
            $redirect_link = $this->qr_code_page_url;
            $redirect_link = '/' . $redirect_link . '/?payment_link=' . htmlentities(urlencode(esc_url($payment_link))) . '&order_id=' . (int)$order_id;
            $redirect_link = home_url($redirect_link);
            $redirect_link = apply_filters('npqp_redirect_link', $redirect_link); // filter

            npqpLog('process_payment', [
                'order_id' => $order_id,
                'payment_link' => $payment_link,
                'redirect_link' => $redirect_link,
            ]);

            // redirect to qr page
            return array(
                'result' => 'success',
                'redirect' => $redirect_link
            );
        }

        /*
        * In case you need a webhook, like PayPal IPN etc
        */
        public function webhook()
        {

            $post = json_decode(file_get_contents('php://input'), 1);

            if (isset($post['event_type']) && $post['event_type'] == 'INVOICING.INVOICE.PAID') {

                $PayPalApi = new NPQP_PayPal_API([
                    'testmode' => $this->testmode,
                    'api_client_id' => $this->api_client_id,
                    'api_secret_id' => $this->api_secret_id,
                    'paypal_webhook_id' => $this->paypal_webhook_id
                ]);

                $verify = $PayPalApi->verifyWebhook();

                npqpLog('webhook', [
                    'verify' => $verify,
                ]);

                if ($verify == 'SUCCESS') {

                    $webhook_event_arr = json_decode(file_get_contents('php://input'), true);
                    $status_success = 'PAID';
                    $status_success = apply_filters('npqp_status_success', $status_success); // filter

                    if ($webhook_event_arr['resource']['invoice']['status'] != $status_success) {
                        return;
                    }

                    npqpLog('verifyWebhook $webhook_event_arr', [
                        '$webhook_event_arr' => $webhook_event_arr,
                    ]);

                    if (isset($webhook_event_arr['resource']['invoice']['detail']['invoice_number'])) {

                        $invoice_number = $webhook_event_arr['resource']['invoice']['detail']['invoice_number'];

                        // get clear invoice id
                        if (strpos($invoice_number, '-') !== false) {
                            $invoice_number = explode('-', $invoice_number)[0];
                        }

                        $order = wc_get_order($invoice_number);

                        $order = apply_filters('npqp_order', $order); // filter

                        if ($order) {

                            if (!$order->is_paid()) {
                                global $woocommerce;
                                $order->payment_complete();
                                $order->reduce_order_stock();
                                $woocommerce->cart->empty_cart();

                                $add_order_note = __('Hey, your order is paid! Thank you!', 'npqp');
                                $add_order_note = apply_filters('npqp_add_order_note', $add_order_note); // filter
                                $order->add_order_note($add_order_note);

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


    new WC_NPQP_Gateway();
}

/**
 * npqp ajax
 * @return mixed
 */
function npqp_ajax()
{
    if (empty($_GET['action'])) {
        return __('Action is missed', 'npqp');
    }
    if ($_GET['action'] == 'getorderpaidstatus') {
        return npqp_order_is_paid($_GET['order_id']);
    }
}

/**
 * order is paid
 * @param $id
 * @return mixed
 */
function npqp_order_is_paid($id)
{
    $post = get_post($id);
    if (isset($post->post_status)) {
        return ($post->post_status == 'wc-processing' || $post->post_status == 'wc-completed') ? 1 : 0;
    } else {
        return __('Invalid order id', 'npqp');
    }
}

/**
 * debug
 * @param $data
 * @param bool $die
 */
function npqpDebug($data, $die = false)
{
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
    if ($die) exit(200);
}

/**
 * @return mixed
 */
function is_qrcodepage()
{
    global $wp;
    return is_page('qr-payments') || $wp->request == 'qr-payments';
}

/**
 * Logger
 * @param $title
 * @param bool $data
 */
function npqpLog($title, $data = false)
{
    $filename = dirname(__FILE__) . '/log.txt';
    if ( file_exists($filename) ) {
        if (!$data) {
            file_put_contents($filename, date('d.m.Y/H:i:s') . ': ' . $_SERVER['REMOTE_ADDR'] . ': ' . $title . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents($filename, date('d.m.Y/H:i:s') . ': ' . $_SERVER['REMOTE_ADDR'] . ': ' . $title . PHP_EOL . var_export($data, true) . PHP_EOL, FILE_APPEND);
        }
    }
}

// guide button in wc-settings/checkout
add_action( 'woocommerce_settings_checkout', function () { ?>
    <a target="_blank" class="npqp-guide-link"
       href="https://nor1m.ru/shop/qr-manual?from=wc-settings"><?= __('Guide', 'npqp') ?></a> <?php
});
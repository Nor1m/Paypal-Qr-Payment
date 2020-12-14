<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <title><?= __('QR payments via PayPal', 'npqp') ?>></title>
    <?= wp_head(); ?>
</head>
<?php

$return_url = home_url();
if (isset($_GET['order_id'])) {
    $order = wc_get_order($_GET['order_id']);
    if ($order) $return_url = $order->get_checkout_order_received_url();
}

?>
<body class="npqp-checkout-body">

    <div class="npqp-checkout-block">

        <div id="qr_code_img" class="npqp-checkout-qr-block">

            <div id="wc-npqp-qr-form" class="wc-npqp-qr-form">

                <div class="npqp-qr-form-wrapper">
                    <input type="hidden" id="npqp-order-id" value="<?= esc_attr(urldecode($_GET['order_id'])) ?>">
                    <input type="hidden" id="npqp-return-url" value="<?= esc_attr(urldecode($return_url)) ?>">
                    <input type="hidden" id="npqp-qrcode-text" value="<?= esc_attr(urldecode($_GET['payment_link'])) ?>">
                    <a target="_blank" href="<?= esc_attr(urldecode($_GET['payment_link'])) ?>">
                        <div id="npqp-qrcode"></div>
                    </a>
                </div>

        </div>

        <div class="npqp-checkout-img-block">
            <img class="npqp-checkout-img" alt="processing" src="<?= plugins_url('qr-payments-via-paypal/assets/img/processing.gif') ?>"/>
        </div>

        <div class="npqp-checkout-text-block">
            <p class="npqp-checkout-title">
                <?= __('Payment is pending', 'npqp') ?>
            </p>
            <p class="npqp-checkout-description">
                <?= __('After payment, the response is received within 2 minutes. You will be automatically redirected to the successful order page', 'npqp') ?>
            </p>
        </div>

        <div class="npqp-checkout-text-block">
            <a class="npqp-checkout-link-invoice" target="_blank" href="<?= esc_attr(urldecode($_GET['payment_link'])) ?>">
                <?= __('Pay by link', 'npqp') ?>
            </a>
            <a class="npqp-checkout-link-checkout" href="javascript:history.back()">
                <?= __('Back to checkout', 'npqp') ?>
            </a>
        </div>

    </div>

</body>
<footer>
    <?= wp_footer(); ?>
</footer>
</html>

var successCallback = function(data) {
 
	var checkout_form = $( 'form.woocommerce-checkout' );
 
	// add a token to our hidden input field
	console.log('successCallback', data);
	checkout_form.find('#npqp_token').val(data.token);
 
	// deactivate the tokenRequest function event
	checkout_form.off( 'checkout_place_order', tokenRequest );
 
	// submit the form now
	checkout_form.submit();
 
};

var tokenRequest = function() {

    // here will be a payment gateway function that process all the card data from your form,
    // maybe it will need your Publishable API key which is misha_params.publishableKey
    // and fires successCallback() on success and errorCallback on failure
    return 1234;
};

var errorCallback = function(data) {
	console.log('errorCallback', data);
};


jQuery(function($){
    
    jQuery('body').on('click', '#npqp-qr-generate', function(e){
        nqpq_qr_code_show();
    });
    
    nqpq_qr_code_show();
    
    function nqpq_qr_code_show(){
        
        jQuery('#npqp-qrcode').html('');
        
        if ( jQuery('#npqp-qrcode-text').val() ) {
            var qrcode_text = jQuery('#npqp-qrcode-text').val();
        } else{
            return;
        }
        
        var qrcode = new QRCode("npqp-qrcode", {
            text: qrcode_text,
            width: npqp_params.qr_code_width,
            height: npqp_params.qr_code_height,
            colorDark: npqp_params.qr_code_stroke_color,
            correctLevel: QRCode.CorrectLevel.H
        });
        
        jQuery('#npqp-qrcode').fadeIn(500);
    }
    
    function npqp_ajax_getorderpaidstatus() {
        
        var order_id = jQuery('#npqp-order-id').val();
        var return_url = jQuery('#npqp-return-url').val();
        
        jQuery.ajax({
            type: "GET",
            url: npqp_params.npqp_ajax_url,
            data: "action=getorderpaidstatus&order_id=" + order_id,
            success: function(res){
                if ( res == 1 ){
                    location.href = return_url;
                }
            }
        });
    } 
    
    if ( npqp_params.is_qrcodepage ){
        setInterval(npqp_ajax_getorderpaidstatus, 3000);
    }
});
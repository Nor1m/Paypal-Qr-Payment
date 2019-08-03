jQuery(document).ready(function(){
    var content = '<div class=\"npqp-notice-admin-btns\"> ' +
        '<a target="_blank" class=\"npqp-btn-donate\" href=\"https://paypal.me/nor1m\">'+nqpp_l18n['Donate']+'</a>' +
        '<a target="_blank" class=\"npqp-btn-site\" href=\"http://nor1m.ru\">'+nqpp_l18n['Visit site']+'</a>' +
        '</div>';
    jQuery('h2').after( content );
});
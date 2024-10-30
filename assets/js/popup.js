"use strict";

(function ($) {
    $(document.body).on('added_to_cart', function (e) {
        $(".ywcn-modal-box").addClass('active')
        $(".product-modal-overlay").addClass('active')
        // $.getJSON(Cross_Up_Sell_Popup_For_WC.ajaxurl, {
        //     action: 'validate_cart_to_popup'
        // }, function (data) {
        //     if (data.success) {
        //         $(".ywcn-modal-box").addClass('active')
        //         $(".product-modal-overlay").addClass('active')
        //     }
        // })
    })

    $(document).on('click', '.closemodal', function (e) {
        $.getJSON(Cross_Up_Sell_Popup_For_WC.ajaxurl, {
            action: 'close_popup'
        }, function (data) {
            $(".ywcn-modal-box").removeClass('active')
            $(".product-modal-overlay").removeClass('active')
        })
    })
})(jQuery)
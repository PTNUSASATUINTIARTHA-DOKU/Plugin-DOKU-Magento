/**
 * Copyright © 2016 Doku. All rights reserved.
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'jquery'
    ],
    function (
        Component,
        rendererList,
        $
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'cc_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/cc-merchanthosted-method'
            },
            {
                type: 'doku_wallet_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/doku-wallet-merchanthosted-method'
            },
            {
                type: 'mandiri_clickpay_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/mandiri-clickpay-merchanthosted-method'
            }
        );

        /** Add view logic here if needed */

        return Component.extend({
            initObservable: function () {
  
                $("head").append("<link>");
                var css = $("head").children(":last");
                css.attr({
                    rel: "stylesheet",
                    type: "text/css",
                    href: "https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"
                });

                return this;
            }
        });
    }
);
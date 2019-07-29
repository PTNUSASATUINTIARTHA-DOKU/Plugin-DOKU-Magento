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
                type: 'bca_klikpay_core',
                component: 'Doku_Core/js/view/payment/method-renderer/bca-klikpay-core-method'
            },
            {
                type: 'klik_bca_core',
                component: 'Doku_Core/js/view/payment/method-renderer/klik-bca-core-method'
            }
        );

        /** Add view logic here if needed */

        return Component.extend({});
    }
);
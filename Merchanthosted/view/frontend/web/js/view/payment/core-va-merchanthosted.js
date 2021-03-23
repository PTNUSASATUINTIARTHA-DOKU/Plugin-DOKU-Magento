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
                type: 'permata_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/permata-va-merchanthosted-method'
            }, 
            {
                type: 'mandiri_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/mandiri-va-merchanthosted-method'
            },
            {
                type: 'sinarmas_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/sinarmas-va-merchanthosted-method'
            },
            {
                type: 'danamon_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/danamon-va-merchanthosted-method'
            },
            {
                type: 'bca_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/bca-va-merchanthosted-method'
            },
            {
                type: 'bri_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/bri-va-merchanthosted-method'
            },
            {
                type: 'cimb_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/cimb-va-merchanthosted-method'
            },
            {
                type: 'alfa_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/alfa-merchanthosted-method'
            },
            {
                type: 'indomaret_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/indomaret-merchanthosted-method'
            },
            {
                type: 'bni_va_merchanthosted',
                component: 'Doku_Merchanthosted/js/view/payment/method-renderer/bni-va-merchanthosted-method'
            }
        );

        /** Add view logic here if needed */

        return Component.extend({});
    }
);
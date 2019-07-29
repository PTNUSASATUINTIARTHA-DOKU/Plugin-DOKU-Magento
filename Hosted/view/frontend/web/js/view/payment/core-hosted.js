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
                type: 'cc_hosted',
                component: 'Doku_Hosted/js/view/payment/method-renderer/creditcard-hosted-method'
            },
            {
                type: 'cc_authorization_hosted',
                component: 'Doku_Hosted/js/view/payment/method-renderer/creditcard-authorization-hosted-method'
            },
            {
                type: 'cc_recurring_hosted',
                component: 'Doku_Hosted/js/view/payment/method-renderer/creditcard-recurring-hosted-method'
            },
            {
                type: 'doku_wallet_hosted',
                component: 'Doku_Hosted/js/view/payment/method-renderer/dokuwallet-hosted-method'
            },
            {
                type: 'doku_hosted_payment',
                component: 'Doku_Hosted/js/view/payment/method-renderer/doku-all-payment-hosted-method'
            }
        );

        if(window.checkoutConfig.payment.doku_hosted_payment.is_opt_dropdown != "1") {
            rendererList.push(
                {
                    type: 'alfa_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/alfa-hosted-method'
                },
                {
                    type: 'bca_va_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/bcava-hosted-method'
                },
                {
                    type: 'indomaret_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/indomaret-hosted-method'
                },
                {
                    type: 'mandiri_clickpay_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/mandiri-clickpay-hosted-method'
                },
                {
                    type: 'ib_danamon_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/ib-danamon-hosted-method'
                },
                {
                    type: 'ib_permata_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/ib-permata-hosted-method'
                },
                {
                    type: 'ib_muamalat_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/ib-muamalat-hosted-method'
                },
                {
                    type: 'epay_bri_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/epay-bri-hosted-method'
                },
                {
                    type: 'cimb_click_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/cimb-click-hosted-method'
                },
                {
                    type: 'permata_va_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/permata-va-hosted-method'
                },
                {
                    type: 'danamon_va_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/danamon-va-hosted-method'
                },
                {
                    type: 'mandiri_va_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/mandiri-va-hosted-method'
                },
                {
                    type: 'bri_va_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/bri-va-hosted-method'
                },
                {
                    type: 'cimb_va_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/cimb-va-hosted-method'
                },
                {
                    type: 'kredivo_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/kredivo-hosted-method'
                },
                {
                    type: 'sinarmas_va_hosted',
                    component: 'Doku_Hosted/js/view/payment/method-renderer/sinarmas-va-hosted-method'
                },
            );
        }
        /** Add view logic here if needed */

        return Component.extend({});
    }
);
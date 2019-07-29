/**
 * Copyright © 2016 Doku. All rights reserved.
 */
define(
        [
            'Magento_Checkout/js/view/payment/default',
            'jquery',
            'mage/url',
            'Magento_Ui/js/modal/alert',
            'Magento_Checkout/js/checkout-data',
            'mage/loader'
        ],
        function (Component, $, url, alert, checkout, loader) {
            'use strict';

            return Component.extend({
                defaults: {
                    template: 'Doku_Core/payment/bca-klikpay-core',
                    setWindow: false,
                    dokuObj: {},
                    dokuDiv: ''
                },
                redirectAfterPlaceOrder: false,
                afterPlaceOrder: function () {

                    $.ajax({
                        type: 'GET',
                        url: url.build('dokucore/payment/request'),
                        showLoader: true,
                        success: function (response) {
                            var dataResponse = $.parseJSON(response);

                            if (dataResponse.err == false) {
                                jQuery.each(dataResponse.result, function (i, val) {
                                    if (i != 'URL') {
                                        $("#bca-klikpay-core").append('<input type="hidden" name="' + i + '" value="' + val + '">');
                                    } else {
                                        $("#bca-klikpay-core").attr("action", val);
                                    }
                                    $("#bca-klikpay-core").submit();
                                });
                            } else {
                                alert({
                                    title: 'Payment error!',
                                    content: 'Error code : ' + dataResponse.res_response_code + '<br>Please retry payment',
                                    actions: {
                                        always: function () {
                                        }
                                    }
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            alert({
                                title: 'Payment Error!',
                                content: 'Please retry payment',
                                actions: {
                                    always: function () {
                                    }
                                }
                            });
                        }
                    });

                },
                getDescription: function(){
                     return window.checkoutConfig.payment.bca_klikpay_core.description
                }
            });
        }
);

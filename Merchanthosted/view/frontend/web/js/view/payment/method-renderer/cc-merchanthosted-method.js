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
            'mage/loader',
            'Magento_Checkout/js/model/totals',
            'Doku_Merchanthosted/js/doku/countdown'
        ],
        function (Component, $, url, alert, checkout, loader, totals, countdown) {
            'use strict';

            return Component.extend({
                defaults: {
                    template: 'Doku_Merchanthosted/payment/cc-merchanthosted',
                    setWindow: false,
                    dokuObj: {},
                    dokuDiv: ''
                },
                redirectAfterPlaceOrder: false,
                afterPlaceOrder: function () {
                    $.ajax({
                        type: 'GET',
                        url: url.build('dokumerchanthosted/payment/requestmerchanthosted'),
                        showLoader: true,
                        success: function (response) {
                            var dataResponse = $.parseJSON(response);

                            if (dataResponse.err == false) {
                                $.getScript("https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js", function () {});
                                $.getScript(dataResponse.result.doku_js_url, function () {
                                    $("head").append("<link>");
                                    var css = $("head").children(":last");
                                    css.attr({
                                        rel: "stylesheet",
                                        type: "text/css",
                                        href: "https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css"
                                    });

                                    $("#billing-address-section").remove();
                                    $("#place-order-section").remove();
                                    $(".loading-mask").remove();

                                    $("#dw-merchanthosted-form").remove();

                                    loader.hide;

                                    var data = new Object();
                                    data.req_merchant_code = dataResponse.result.req_merchant_code;
                                    data.req_chain_merchant = dataResponse.result.req_chain_merchant;
                                    data.req_payment_channel = dataResponse.result.req_payment_channel;
                                    data.req_transaction_id = dataResponse.result.req_transaction_id;
                                    data.req_currency = dataResponse.result.req_currency;
                                    data.req_amount = dataResponse.result.req_amount;
                                    data.req_words = dataResponse.result.req_words;
                                    data.req_form_type = dataResponse.result.req_form_type;
                                    data.req_server_url = dataResponse.result.req_server_url;
                                    data.req_session_id = dataResponse.result.req_session_id;

                                    console.log(data);

                                    $(function () {
                                        if (window.checkoutConfig.payment.core.environment == 'development') {
                                            getForm(data, 'staging');
                                        } else {
                                            getForm(data);
                                        }
                                    });
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
                     return window.checkoutConfig.payment.cc_merchanthosted.description
                },
                getChargingUrl: function(){
                    return url.build('dokumerchanthosted/payment/chargemerchanthosted');
                }
            });
        }
);

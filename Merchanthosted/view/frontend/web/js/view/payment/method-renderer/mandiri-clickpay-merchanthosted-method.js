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
            'Magento_Checkout/js/model/totals'
        ],
        function (Component, $, url, alert, checkout, loader, totals) {
            'use strict';

            return Component.extend({
                defaults: {
                    template: 'Doku_Merchanthosted/payment/mandiri-clickpay-merchanthosted',
                    setWindow: false,
                    dokuObj: {},
                    dokuDiv: ''
                },
                redirectAfterPlaceOrder: false,
                afterPlaceOrder: function () {

                    $.ajax({
                        type: 'GET',
                        url: url.build('dokumerchanthosted/payment/requestmandiriclickpay'),
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
                                    $(".payment-method-billing-address").remove();
                                    $(".actions-toolbar").remove();

                                    $(".loading-mask").remove();

                                    loader.hide;

                                    $("#form-request").show();

                                    $("#doku_invoice_no").val(dataResponse.result.req_transaction_id);
                                    $("#CHALLENGE_CODE_2").val(dataResponse.result.CHALLENGE_CODE_2);
                                    $("#amount-label").append(dataResponse.result.CHALLENGE_CODE_2);

                                    var data = new Object();
                                    data.req_cc_field = dataResponse.result.cc_number;
                                    data.req_challenge_field = dataResponse.result.CHALLENGE_CODE_1;

                                    dokuMandiriInitiate(data);

                                    $('.cc-number').payment('formatCardNumber');
                                    $.fn.toggleInputError = function (erred) {
                                        this.parent('.form-group').toggleClass('has-error', erred);
                                        return this;
                                    };
                                    $('#cc_number').change(function () {
                                        $('.cc-number').toggleInputError(!$.payment.validateCardNumber($('.cc-number').val()));
                                    });
                                    var challenge3 = Math.floor(Math.random() * 999999999);
                                    $("#challenge_div_3").text(challenge3);
                                    $("#CHALLENGE_CODE_3").val(challenge3);

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
                     return window.checkoutConfig.payment.mandiri_clickpay_merchanthosted.description
                },
                getChargingUrl: function(){
                    return url.build('dokumerchanthosted/payment/chargemerchanthosted');
                }
            });
        }
);

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
                    template: 'Doku_Core/payment/klik-bca-core',
                    setWindow: false,
                    dokuObj: {},
                    dokuDiv: ''
                },
                redirectAfterPlaceOrder: false,
                
                beforePlaceOrder: function () {
                    if ($("#klikbca-user-id").val() === "") {
                        loader.hide;
                        alert({
                            title: 'Klik BCA User ID Empty!',
                            content: 'Please insert your Klik BCA User ID',
                            actions: {
                                always: function () {
                                }
                            }
                        });
                        
                        return false;
                    } else {
                         this.placeOrder();
                    }
                },
                
                afterPlaceOrder: function () {
                
                    if ($("#klikbca-user-id").val() !== "") {
                        $.ajax({
                            type: 'GET',
                            url: url.build('dokucore/payment/request?klikbcauserid=' + $("#klikbca-user-id").val()),
                            showLoader: true,
                            success: function (response) {
                                var dataResponse = $.parseJSON(response);

                                if (dataResponse.err == false) {
                                    jQuery.each(dataResponse.result, function (i, val) {
                                        if (i != 'URL') {
                                            $("#klik-bca-core").append('<input type="hidden" name="' + i + '" value="' + val + '">');
                                        } else {
                                            $("#klik-bca-core").attr("action", val);
                                        }
                                        $("#klik-bca-core").submit();
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

                    } else {
                        alert({
                            title: 'Klik BCA User ID Empty!',
                            content: 'Please insert your Klik BCA User ID',
                            actions: {
                                always: function () {
                                }
                            }
                        });
                    }

                },
                getDescription: function(){
                     return window.checkoutConfig.payment.klik_bca_core.description
                }
            });
        }
);

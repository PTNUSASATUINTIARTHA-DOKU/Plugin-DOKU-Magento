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
            'domReady!'
        ],
        function (Component, $, url, alert, checkout, loader) {
            'use strict';
            //console.log(window.checkoutConfig.payment.doku_hosted_payment);
            return Component.extend({
                defaults: {
                    template: 'Doku_Hosted/payment/doku-all-payment-hosted',
                    setWindow: false,
                    dokuObj: {},
                    dokuDiv: ''
                },
                redirectAfterPlaceOrder: false,
                afterPlaceOrder: function () {

                    var requestUrl = url.build('dokuhosted/payment/request');
                    if(window.checkoutConfig.payment.doku_hosted_payment.is_opt_dropdown == "1") {
                        var dokuSelectedGroup = $('input[name=doku-all-group]:checked').val();
                        if(dokuSelectedGroup == 'doku-all-transfer') {
                            var dokuSelectedChannel = $('#doku-all-payment-hosted-options-transfer').val();
                        } else {
                            var dokuSelectedChannel = $('#doku-all-payment-hosted-options-ib').val();
                        }

                        requestUrl = url.build('dokuhosted/payment/request?group='+dokuSelectedGroup+"&channel="+dokuSelectedChannel);
                    }
                    $.ajax({
                        type: 'GET',
                        url: requestUrl,
                        showLoader: true,
                        success: function (response) {
                            var dataResponse = $.parseJSON(response);

                            if (dataResponse.err == false) {
                                jQuery.each(dataResponse.result, function (i, val) {
                                    if (i != 'URL') {
                                        $("#doku-all-payment-hosted").append('<input type="hidden" name="' + i + '" value="' + val + '">');
                                    } else {
                                        $("#doku-all-payment-hosted").attr("action", val);
                                    }
                                });
                                $("#doku-all-payment-hosted").submit();
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

//                window.location = url.build('dokuhosted/payment/request');
                },
                getDescription: function(){
                    return window.checkoutConfig.payment.doku_hosted_payment.is_opt_dropdown == "1" ? "" : window.checkoutConfig.payment.doku_hosted_payment.description+"<br />";
                },
                getPaymentOptions: function() {
                    var paymentDropdownValues = window.checkoutConfig.payment.doku_hosted_payment.dropdown_values_label;
                    var optString = '';
                    Object.keys(paymentDropdownValues).forEach(function(key,index) {
                        optString += "<option value='" + key +"'>"+ paymentDropdownValues[key] +"</option>";
                    });
                    return optString;
                },
                getTransferOptions: function() {
                    var paymentDropdownValues = window.checkoutConfig.payment.doku_hosted_payment.transfer_dropdown_values;
                    var optString = '';
                    Object.keys(paymentDropdownValues).forEach(function(key,index) {
                        optString += "<option value='" + key +"'>"+ paymentDropdownValues[key] +"</option>";
                    });
                    return optString;
                },
                getIbOptions: function() {
                    var paymentDropdownValues = window.checkoutConfig.payment.doku_hosted_payment.ib_dropdown_values;
                    var optString = '';
                    Object.keys(paymentDropdownValues).forEach(function(key,index) {
                        optString += "<option value='" + key +"'>"+ paymentDropdownValues[key] +"</option>";
                    });
                    return optString;
                },
                showBankTransfer: function() {
                    if(!this.displayAsDropdown()) return false;
                    if(this.getTransferOptions() == '') return false;
                    return true;
                },
                showInternetBanking: function() {
                    if(!this.displayAsDropdown()) return false;
                    if(this.getIbOptions() == '') return false;
                    return true;
                },
                displayAsDropdown: function() {
                    return window.checkoutConfig.payment.doku_hosted_payment.is_opt_dropdown == "1";
                },
                showDokuAllTransfer: function() {
                    $('#doku-all-ib-channel').hide();
                    $('#doku-all-transfer-channel').show();
                    return true;
                },
                showDokuAllIb: function() {
                    $('#doku-all-transfer-channel').hide();
                    $('#doku-all-ib-channel').show();
                    return true;
                },
            });
        }
);

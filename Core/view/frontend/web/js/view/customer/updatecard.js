define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'domReady!'
], function ($, alert) {
    'use strict';
    $.widget('mage.dokucorerequire', {
        options: {},
        _create: function () {
            var self = this;
            console.log("Submitting request to Doku");
            $('#updatecard-form').submit();
        }
    });
    return $.mage.dokucorerequire;
});
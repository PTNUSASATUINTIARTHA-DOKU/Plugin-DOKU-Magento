define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/mage'
], function($, modal) {
    return function(config) {
        var url = config.dokuUrl + 'refundrequest/order/index',
            title = config.dokuPopupTitle,
            data = config.dataOrder,
            orderRefund = config.orderRefund
            orders = config.orders;
        var options = {
            wrapperClass: 'doku-modals-wrapper',
            modalClass: 'doku-modal',
            overlayClass: 'doku-modals-overlay',
            responsiveClass: 'doku-modal-slide',
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: title,
            zIndex: 10,
            buttons: [{
                text: $.mage.__('Send Request'),
                class: 'doku-popup-button',
                click: function (data) {
                    $('#doku-refund-form').submit();

                    // var form_data = $("#doku-refund-form").serialize();
                    // if ($('#doku-refund-form').valid()) {
                    //
                    //     $.ajax({
                    //         showLoader: true,
                    //         url: url,
                    //         type: 'POST',
                    //         data: form_data
                    //     })
                    //         .done(function () {
                    //             $("#doku-refund-modal").modal('closeModal');
                    //             $("#doku-refund-form")[0].reset();
                    //             location.reload(true);
                    //
                    //         })
                    //         .fail(function () {
                    //             $("#doku-refund-modal").modal('closeModal');
                    //         });
                    // }
                }
            },
                {
                    text: $.mage.__('Cancel Request'),
                    class: 'doku-popup-button',
                    click: function (data) {
                        $("#doku-refund-form")[0].reset();
                        $("#doku-refund-modal").modal('closeModal');

                    }
                }
            ]
        };

        $("#my-orders-table tbody tr").each(function() {
            var pos = $(this).closest("tr");
            var col1 = pos.find("td:eq(0)").text();
            col1 = $.trim(col1);
            var array = orderRefund.split(",");
            var status = "";
            $(this).attr("data-oder-id", col1);

            /* Add refund button to each Order Row */
            $.each(orders, function(key, value) {
                if (value.increment_id == col1) {
                    status = value.status;
                    return;
                }
            });
            if ($.inArray(status, array) !== -1) {
                $(this).find('.col.actions').append("<span class='refund'><a href='#' class='refund-order'>Refund</a></span>");
            }

            var buttonPos = "tr[data-oder-id="+col1+"] td.col.actions";
            $.each(data, function(key, value) {
                var classRefund = buttonPos + ' ' + 'span.refund';
                if (col1 == value.increment_id && value.refund_status == 0) {
                    $(classRefund).html('Pending');
                }
                if (col1 == value.increment_id && value.refund_status == 1) {
                    $(classRefund).html('Accepted');
                }
                if (col1 == value.increment_id && value.refund_status == 2) {
                    $(classRefund).html('Rejected');
                }
                if (col1 == value.increment_id && value.refund_status == 3) {
                    $(classRefund).html('Refunded');
                }
            });
        });

        $(document).on('click', '.refund-order', function () {
            var test = $(this).closest("tr");
            var col1=test.find("td:eq(0)").text();
            col1 = col1.replace(/\s/g, '');
            $(this).attr("data-oder-id", col1);
        });
        $(document).on('click', '.refund-order', function () {
            var order_id = $(this).attr('data-oder-id');
            modal(options, $("#doku-refund-modal"));
            $("#doku-refund-modal").modal('openModal');
            $(".doku-refund-oder-id").attr('value', order_id);
            $('.doku-modal').css('z-index', 900);
        });
    }
});
!function (a) {
    "use strict";
    "function" == typeof define && define.amd ? define(["jquery"], a) : a(jQuery)
}(function (a) {
    "use strict";

    function b(a) {
        if (a instanceof Date) return a;
        if (String(a).match(g)) return String(a).match(/^[0-9]*$/) && (a = Number(a)), String(a).match(/\-/) && (a = String(a).replace(/\-/g, "/")), new Date(a);
        throw new Error("Couldn't cast `" + a + "` to a date object.")
    }

    function c(a) {
        var b = a.toString().replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
        return new RegExp(b)
    }

    function d(a) {
        return function (b) {
            var d = b.match(/%(-|!)?[A-Z]{1}(:[^;]+;)?/gi);
            if (d) for (var f = 0, g = d.length; f < g; ++f) {
                var h = d[f].match(/%(-|!)?([a-zA-Z]{1})(:[^;]+;)?/), j = c(h[0]), k = h[1] || "", l = h[3] || "",
                    m = null;
                h = h[2], i.hasOwnProperty(h) && (m = i[h], m = Number(a[m])), null !== m && ("!" === k && (m = e(l, m)), "" === k && m < 10 && (m = "0" + m.toString()), b = b.replace(j, m.toString()))
            }
            return b = b.replace(/%%/, "%")
        }
    }

    function e(a, b) {
        var c = "s", d = "";
        return a && (a = a.replace(/(:|;|\s)/gi, "").split(/\,/), 1 === a.length ? c = a[0] : (d = a[0], c = a[1])), Math.abs(b) > 1 ? c : d
    }

    var f = [], g = [], h = {precision: 100, elapse: !1, defer: !1};
    g.push(/^[0-9]*$/.source), g.push(/([0-9]{1,2}\/){2}[0-9]{4}( [0-9]{1,2}(:[0-9]{2}){2})?/.source), g.push(/[0-9]{4}([\/\-][0-9]{1,2}){2}( [0-9]{1,2}(:[0-9]{2}){2})?/.source), g = new RegExp(g.join("|"));
    var i = {
        Y: "years",
        m: "months",
        n: "daysToMonth",
        d: "daysToWeek",
        w: "weeks",
        W: "weeksToMonth",
        H: "hours",
        M: "minutes",
        S: "seconds",
        D: "totalDays",
        I: "totalHours",
        N: "totalMinutes",
        T: "totalSeconds"
    }, j = function (b, c, d) {
        this.el = b, this.$el = a(b), this.interval = null, this.offset = {}, this.options = a.extend({}, h), this.instanceNumber = f.length, f.push(this), this.$el.data("countdown-instance", this.instanceNumber), d && ("function" == typeof d ? (this.$el.on("update.countdown", d), this.$el.on("stoped.countdown", d), this.$el.on("finish.countdown", d)) : this.options = a.extend({}, h, d)), this.setFinalDate(c), this.options.defer === !1 && this.start()
    };
    a.extend(j.prototype, {
        start: function () {
            null !== this.interval && clearInterval(this.interval);
            var a = this;
            this.update(), this.interval = setInterval(function () {
                a.update.call(a)
            }, this.options.precision)
        }, stop: function () {
            clearInterval(this.interval), this.interval = null, this.dispatchEvent("stoped")
        }, toggle: function () {
            this.interval ? this.stop() : this.start()
        }, pause: function () {
            this.stop()
        }, resume: function () {
            this.start()
        }, remove: function () {
            this.stop.call(this), f[this.instanceNumber] = null, delete this.$el.data().countdownInstance
        }, setFinalDate: function (a) {
            this.finalDate = b(a)
        }, update: function () {
            if (0 === this.$el.closest("html").length) return void this.remove();
            var b, c = void 0 !== a._data(this.el, "events"), d = new Date;
            b = this.finalDate.getTime() - d.getTime(), b = Math.ceil(b / 1e3), b = !this.options.elapse && b < 0 ? 0 : Math.abs(b), this.totalSecsLeft !== b && c && (this.totalSecsLeft = b, this.elapsed = d >= this.finalDate, this.offset = {
                seconds: this.totalSecsLeft % 60,
                minutes: Math.floor(this.totalSecsLeft / 60) % 60,
                hours: Math.floor(this.totalSecsLeft / 60 / 60) % 24,
                days: Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
                daysToWeek: Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
                daysToMonth: Math.floor(this.totalSecsLeft / 60 / 60 / 24 % 30.4368),
                weeks: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7),
                weeksToMonth: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7) % 4,
                months: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 30.4368),
                years: Math.abs(this.finalDate.getFullYear() - d.getFullYear()),
                totalDays: Math.floor(this.totalSecsLeft / 60 / 60 / 24),
                totalHours: Math.floor(this.totalSecsLeft / 60 / 60),
                totalMinutes: Math.floor(this.totalSecsLeft / 60),
                totalSeconds: this.totalSecsLeft
            }, this.options.elapse || 0 !== this.totalSecsLeft ? this.dispatchEvent("update") : (this.stop(), this.dispatchEvent("finish")))
        }, dispatchEvent: function (b) {
            var c = a.Event(b + ".countdown");
            c.finalDate = this.finalDate, c.elapsed = this.elapsed, c.offset = a.extend({}, this.offset), c.strftime = d(this.offset), this.$el.trigger(c)
        }
    }), a.fn.countdown = function () {
        var b = Array.prototype.slice.call(arguments, 0);
        return this.each(function () {
            var c = a(this).data("countdown-instance");
            if (void 0 !== c) {
                var d = f[c], e = b[0];
                j.prototype.hasOwnProperty(e) ? d[e].apply(d, b.slice(1)) : null === String(e).match(/^[$A-Z_][0-9A-Z_$]*$/i) ? (d.setFinalDate.call(d, e), d.start()) : a.error("Method %s does not exist on jQuery.countdown".replace(/\%s/gi, e))
            } else new j(this, b[0], b[1])
        })
    }
});

var threeDResponse, data_obj, doku_timeout, doku_timeout_page, doku_ajax, frmSaveToken, $ = jQuery.noConflict(),
    jsEnv = "prod",
    originUrl = "https://pay.doku.com",
    requestURL = originUrl + "/api/payment/",
    loadingUrl = originUrl + "/doku-js/assets/images/loading.gif",
    failedUrl = originUrl + "/doku-js/assets/images/failed.png",
    clientWidth = document.documentElement.clientWidth,
    clientHeight = document.documentElement.clientHeight,
    iFrameWidth = clientWidth < 500 ? clientWidth : 500,
    iFrameScale = 1,
    iFrameStyle = "z-index: 1; -webkit-transform:scale(" + iFrameScale + ");  -moz-transform:scale(" + iFrameScale + ");  -o-transform:scale(" + iFrameScale + "); -ms-transform:scale(" + iFrameScale + "); overflow: hidden;",
    form3D = '<form method="post" name="doku-form-3d" id="doku-form-3d" target=\'doku-iframe\'><input name="PaReq" type="hidden" id="PaReq"/><input name="MD" type="hidden" id="MD"/><input name="TermUrl" type="hidden" id="TermUrl"/>' + "</form><iframe name='doku-iframe' height='500px' width='" + (iFrameWidth = clientWidth < 500 ? "400px" : "500px") + "' style='" + iFrameStyle + "'></iframe>",
    formDw = '<div style="" class="dw-acc-exist"><div style="" class="ava-dw-exist"><img src="" alt="ava-image"></div><div style="" class="info-dw"><div class="info-dw-user" style=""></div><div class="info-dw-label" style="">Saldo saya</div><div class="info-dw-saldo" style="" class=""></div></div><div class="clear"></div></div>',
    pageTimeout = "",
    timeout = 15;

function setDokuEnv(e) {
    switch (e) {
        case "local":
            originUrl = "http://192.168.11.134";
            break;
        case "dev":
            originUrl = "http://team-oco.103.10.130.55.xip.io";
            break;
        case "uat":
            originUrl = "http://luna2.nsiapay.com";
            break;
        case "staging":
            originUrl = "https://staging.doku.com";
            break;
        default:
            originUrl = "https://pay.doku.com"
    }
    requestURL = originUrl + "/api/payment/",
        loadingUrl = originUrl + "/doku-js/assets/images/loading.gif",
        failedUrl = originUrl + "/doku-js/assets/images/failed.png"
}

function getForm(e) {
    getForm(e, "production")
}

function getForm(a, e) {
    setDokuEnv(e);
    var t = new Object;
    console.log(a.req_amount);
    t.req_merchant_code = a.req_merchant_code,
        t.req_transaction_id = a.req_transaction_id,
        t.req_payment_channel = a.req_payment_channel,
        t.req_chain_merchant = a.req_chain_merchant,
        t.req_amount = a.req_amount,
        t.req_currency = a.req_currency,
        t.req_words = a.req_words,
        t.req_form_type = a.req_form_type,
        t.req_domain_valid = document.location.origin,
        t.req_timeout = a.req_timeout,
//        t.req_device_info = getDeviceInfo(),
        null != a.req_ref_account_id && "" != a.req_ref_account_id && (t.req_ref_account_id = a.req_ref_account_id,
            t.req_access_type = "W",
            t.req_session_id = a.req_session_id),
        null != a.req_customer_id && "" != a.req_customer_id && (t.req_customer_id = a.req_customer_id),
        null != a.req_token_payment && "" != a.req_token_payment && (t.req_token_payment = a.req_token_payment);
    var o = $("[doku-div='form-payment']").html();
    doku_timeout = setTimeout(function() {
            $("[doku-div='form-payment']").html("<br><center><span id='doku-loader-text'>TIMEOUT</span><br><br><a id='doku-retry-button' href='#' onclick='getForm(" + JSON.stringify(a) + ")'>Try again</a></center><br>"),
                doku_ajax.abort()
        }, 65e3),
        doku_ajax = $.ajax({
            url: requestURL + "getRequestCode",
            data: {
                data: JSON.stringify(t)
            },
            dataType: "json",
            type: "POST",
            beforeSend: function() {
                $("[doku-div='form-payment']").html("<br><center><img src='" + loadingUrl + "' class='doku-loader-image'></center><br>")
            },
            success: function(e) {
                if (clearTimeout(doku_timeout),
                    $("[doku-div='form-payment']").html(o),
                    "0000" == e.res_response_code) {
                    var t = !1;
                    if ("inline" == a.req_form_type)
                        if (t = !0,
                            pageTimeout = "<center>We are sorry your session has been expired. Please start a new payment request. Thank you.</center>",
                            data_obj = $.extend(a, e),
                            null == a.req_custom_form)
                            if (null != a.req_mage && "" != a.req_mage)
                                for (i = 0; i < Object.size(e.res_form_payment); i++)
                                    $("[doku-div='form-payment']").append(e.res_form_payment[i]);
                            else
                                for (i = 0; i < Object.size(e.res_form_payment); i++)
                                    $("[doku-div='form-payment']").prepend(e.res_form_payment[i]);
                    else {
                        if ("15" == a.req_payment_channel) {
                            if (null != a.req_mage && "" != a.req_mage)
                                for (i = 0; i < a.req_custom_form.length; i++)
                                    $("#" + a.req_custom_form[i]).append(e.res_form_payment[i]);
                            else
                                for (i = 0; i < a.req_custom_form.length; i++)
                                    $("#" + a.req_custom_form[i]).prepend(e.res_form_payment[i]);
                            a.req_custom_form.length < 5 && (frmSaveToken = e.res_form_payment[4]),
                                "true" == e.res_service_two_click ? null != a.req_token_payment && "" != a.req_token_payment && null != e.res_tokenization ? (tokenization = $.parseJSON(e.res_tokenization),
                                    $("#doku-cc-number").val(tokenization.res_cc_number),
                                    $("#doku-cc-number").addClass("readonlycase"),
                                    $("#doku-cc-number").prop("disabled", "disabled"),
                                    $("#doku-cc-number").removeAttr("validation-rules"),
                                    $("#" + data_obj.req_custom_form[2]).remove(),
                                    $("#" + data_obj.req_custom_form[3]).remove()) : ($("[doku-div='form-payment']").append(frmSaveToken),
                                    $("[doku-div='form-payment']").after("<br>"),
                                    $(".doku-cc-number").payment("formatCardNumber"),
                                    $(".doku-cc-exp").payment("formatCardExpiry")) : ($(".doku-cc-number").payment("formatCardNumber"),
                                    $(".doku-cc-exp").payment("formatCardExpiry")),
                                $(".doku-cvc").payment("formatCardCVC")
                        } else {
                            if (null != a.req_mage && "" != a.req_mage)
                                for (i = 0; i < a.req_custom_form.length; i++)
                                    $("#" + a.req_custom_form[i]).append(e.res_form_payment[i]);
                            else
                                for (i = 0; i < a.req_custom_form.length; i++)
                                    $("#" + a.req_custom_form[i]).prepend(e.res_form_payment[i]);
                            if (null != a.req_ref_account_id && "" != a.req_ref_account_id) {
                                var n = $.parseJSON(e.res_data_dw);
                                "0000" == n.responseCode && (pageTimeout = "",
                                    $("[doku-div='form-payment']").html(formDw),
                                    $("[doku-div='form-payment']").find("img").attr("src", n.avatar),
                                    $(".info-dw-user").html(n.customerName + " - " + n.dokuId + "&nbsp;&nbsp;"),
                                    $(".info-dw-saldo").html("Rp " + n.listPaymentChannel[0].details.lastBalance.format(0, 3, ".", ",")),
                                    null != a.req_wallet_fields && "" != a.req_wallet_fields && (null != a.req_wallet_fields.name && "" != a.req_wallet_fields.name && $("#" + a.req_wallet_fields.name).val(n.customerName),
                                        null != a.req_wallet_fields.phone && "" != a.req_wallet_fields.phone && $("#" + a.req_wallet_fields.phone).val(n.customerPhone),
                                        null != a.req_wallet_fields.address && "" != a.req_wallet_fields.address && $("#" + a.req_wallet_fields.address).val(n.customerAddress),
                                        null != a.req_wallet_fields.city && "" != a.req_wallet_fields.city && $("#" + a.req_wallet_fields.city).val(n.customerCity),
                                        null != a.req_wallet_fields.country && "" != a.req_wallet_fields.country && $("#" + a.req_wallet_fields.country).val(n.customerCountry),
                                        null != a.req_wallet_fields.email && "" != a.req_wallet_fields.email && $("#" + a.req_wallet_fields.email).val(n.customerEmail)))
                            }
                        }
                        btnOnBlur()
                    } else if ("full" == a.req_form_type) {
                        t = !1,
                            pageTimeout = e.res_timeout_page,
                            a = $.extend(a, e),
                            $("[doku-div='form-payment']").html(e.res_form_payment),
                            $(".amount").text(parseInt(a.req_amount).format(0, 3, ".", ",")),
                            "15" == a.req_payment_channel && ("true" == e.res_service_two_click ? null != a.req_token_payment && "" != a.req_token_payment && null != e.res_tokenization ? (tokenization = $.parseJSON(e.res_tokenization),
                                $("#doku-cc-number").val(tokenization.res_cc_number),
                                $("#doku-cc-number").addClass("readonlycase"),
                                $("#doku-cc-number").prop("disabled", "disabled"),
                                $("#doku-cc-number").removeAttr("validation-rules"),
                                $("#doku-li-save-cc").remove(),
                                $("#doku-div-name-cc").remove(),
                                $("#doku-div-cc-exp").remove()) : ($(".doku-cc-number").payment("formatCardNumber"),
                                $(".doku-cc-exp").payment("formatCardExpiry"),
                                $(".doku-cvc").payment("formatCardCVC")) : ($("#doku-li-save-cc").hide(),
                                $(".doku-cc-number").payment("formatCardNumber"),
                                $(".doku-cc-exp").payment("formatCardExpiry"),
                                $(".doku-cvc").payment("formatCardCVC"))),
                            btnOnBlur();
                        var r = $("input[type='button']").map(function() {
                            return this.id
                        }).get();
                        for (i = 0; i < r.length; i++)
                            $("#" + r[i]).click(function() {
                                delete a[0],
                                    formatForm($.extend($(this).parents("form"), $.extend(a, e)))
                            })
                    }
                    setTimeoutPage(timeout = e.res_timeout, pageTimeout, a, !1, t)
                } else
                    "5536" == e.res_response_code ? (pageTimeout = "inline" == a.req_form_type ? "<center>We are sorry your session has been expired. Please start a new payment request. Thank you.</center>" : e.res_timeout_page,
                        $("[doku-div='form-payment']").html(pageTimeout),
                        void 0 === e.res_redirect_url || null == e.res_redirect_url || "" == e.res_redirect_url ? $("[doku-div='form-payment']").find("a").remove() : $("[doku-div='form-payment']").find(".backtomerchant").attr("href", e.res_redirect_url)) : $("[doku-div='form-payment']").html("<br><center><span id='doku-loader-text'>Request failed... error code : " + e.res_response_code.replace(new RegExp("_", "g"), " ") + "</span><br><br><a id='doku-retry-button' href='#' onclick='getForm(" + JSON.stringify(a) + ")'>Try again</a></center><br>")
            }
        })
}

function btnOnBlur() {
    var e = $("input").not("[type='button']").map(function() {
        return this.id
    }).get();
    for (i = 0; i < e.length; i++)
        $("#" + e[i]).blur(function() {
            if (null != $(this).attr("validation-rules")) {
                var e = $(this).attr("validation-rules").split("|"),
                    t = new Object;
                for (j = 0; j < e.length; j++) {
                    if (0 == (t = checkRules(this.id, e[j])).status)
                        break
                }
                0 == t.status ? ($(this).parent().addClass("has-error"),
                    0 == $(this).parent().children("font").length ? $(this).parent().append("<font color='red'>" + t.msg + "</font>") : $(this).parent().children("font").html(t.msg)) : ($(this).parent().removeClass("has-error"),
                    $(this).parent().children("font").remove())
            }
        })
}

function formatForm(e) {
    var t = $("#" + e[0].id + " :input").not("[type='button']").map(function() {
        return this.id
    }).get();
    if (validateForm(t)) {
        var n = new Object;
        n.req_input_form = t,
            n.req_pairing_code = e.res_request_code,
            n.req_server_url = e.req_server_url,
            n.req_loading = e.res_form_loading,
            n.req_result = e.res_form_result,
            submitForm($.extend(e, n))
    }
}

function validateForm(e) {
    var t = !0;
    for (i = 0; i < e.length; i++)
        if (null != $("#" + e[i]).attr("validation-rules")) {
            var n = $("#" + e[i]).attr("validation-rules").split("|"),
                r = new Object;
            for (j = 0; j < n.length; j++) {
                if (0 == (r = checkRules(e[i], n[j])).status)
                    break
            }
            0 == r.status && ($("#" + e[i]).parent().addClass("has-error"),
                0 == $("#" + e[i]).parent().children("font").length ? $("#" + e[i]).parent().append("<font color='red'>" + r.msg + "</font>") : $("#" + e[i]).parent().children("font").html(r.msg),
                t = !1)
        }
    return t
}

function checkRules(e, t) {
    var n = new Object,
        r = t;
    switch (-1 < r.indexOf("maxlength") && (r = "maxlength"),
        r) {
        case "required":
            "" == $("#" + e).val() && (n.status = !1,
                n.msg = "this field cannot be empty");
            break;
        case "maxlength":
            var i = t.split(":");
            $("#" + e).val().length > i[1] && (n.status = !1,
                n.msg = "this field cannot exceed " + i[1] + " characters");
            break;
        case "cc":
            $.payment.validateCardNumber($("#" + e).val()) || (n.status = !1,
                n.msg = "The card number is not a valid credit card number.");
            break;
        case "cc-cvc":
            $.payment.validateCardCVC($("#" + e).val()) || (n.status = !1,
                n.msg = "The card's security code is invalid.");
            break;
        case "cc-exp":
            if (null != $("#" + e).val()) {
                var a = $("#" + e).val().split(" / ");
                2 == a[1].length && (a[1] = "20" + a[1]),
                    $.payment.validateCardExpiry(a[0], a[1]) || (n.status = !1,
                        n.msg = "The card's expiration date is invalid.")
            }
            break;
        case "number":
            1 == isNaN($("#" + e).val()) && (n.status = !1,
                n.msg = "Only numbers allowed");
            break;
        case "email":
            n.status = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test($("#" + e).val()),
                n.msg = "Please input your correct email."
    }
    return n
}

function receiveMessage(e) {
    e.origin == originUrl && (threeDResponse = e.data,
        $.fancybox.close())
}

function submitForm(t) {
    var n = processForm(t);
    $("[doku-div='form-payment']").html(t.req_loading),
        doku_timeout = setTimeout(function() {
            t.form_req_result_note = "Timeout",
                showResult(t),
                doku_ajax.abort()
        }, 65e3),
        doku_ajax = $.ajax({
            url: requestURL + "getToken",
            data: {
                data: JSON.stringify(n)
            },
            dataType: "json",
            type: "POST",
            success: function(e) {
                clearTimeout(doku_timeout),
                    clearTimeout(doku_timeout_page),
                    "0000" == e.res_response_code ? "04" == t.req_payment_channel ? initiateFormWallet($.extend(t, $.extend(n, e))) : null != e.res_result_3D ? (obj3D = JSON.parse(e.res_result_3D),
                        window.addEventListener("message", receiveMessage, !1),
                        $.fancybox.open([{
                            closeClick: !1,
                            type: "iframe",
                            openEffect: "fade",
                            closeEffect: "fade",
                            openSpeed: "slow",
                            closeSpeed: "slow",
                            content: form3D,
                            closeBtn: !1,
                            autoResize: !1,
                            autoSize: !1,
                            padding: 0,
                            margin: 0,
                            overflow: "hidden",
                            width: "360px",
                            helpers: {
                                overlay: {
                                    closeClick: !1
                                }
                            },
                            afterClose: function() {
                                obj3dResponse = $.parseJSON(threeDResponse),
                                    "0000" == obj3dResponse.res_response_code ? backToMerchant($.extend(t, $.extend(n, e))) : (t.form_req_result_note = obj3dResponse.res_response_code,
                                        showResult(t))
                            }
                        }]),
                        $("#doku-form-3d").find("#PaReq").val(obj3D.PAREQ),
                        $("#doku-form-3d").find("#MD").val(obj3D.MD),
                        $("#doku-form-3d").find("#TermUrl").val(obj3D.TERMURL),
                        $("#doku-form-3d").attr("action", obj3D.ACSURL),
                        $("#doku-form-3d").submit()) : backToMerchant($.extend(t, $.extend(n, e))) : (t.form_req_result_note = e.res_response_code,
                        showResult(t))
            }
        })
}

function processForm(e) {
    var t = new Object;
    if (t.req_merchant_code = e.req_merchant_code,
        t.req_payment_channel = e.req_payment_channel,
        t.req_transaction_id = e.req_transaction_id,
        t.req_amount = e.req_amount,
        t.req_currency = e.req_currency,
        t.req_pairing_code = e.req_pairing_code,
        t.req_access_type = "W",
        t.req_domain_valid = document.location.origin,
        "15" == e.req_payment_channel) {
        if (null != e.req_customer_id && "" != e.req_customer_id) {
            if (null == e.res_tokenization)
                e.form_req_date = $("#doku-cc-exp").val(),
                e.form_req_number = $("#doku-cc-number").val(),
                2 == (n = e.form_req_date.split(" / "))[1].length && (n[1] = "20" + n[1]),
                t.req_date = n[1].substring(2, 4) + n[0],
                t.req_number = e.form_req_number.replace(/ - /g, ""),
                t.req_name = $("#doku-name-cc").val();
            1 == $("#doku-save-cc").prop("checked") ? t.req_save_customer = "SAVE" : t.req_save_customer = "UNSAVE"
        } else {
            var n;
            e.form_req_date = $("#" + e.req_input_form[3]).val(),
                e.form_req_number = $("#" + e.req_input_form[0]).val(),
                2 == (n = e.form_req_date.split(" / "))[1].length && (n[1] = "20" + n[1]),
                t.req_date = n[1].substring(2, 4) + n[0],
                t.req_number = e.form_req_number.replace(/ - /g, ""),
                t.req_name = $("#" + e.req_input_form[2]).val()
        }
        t.req_secret = $("#doku-cvc").val()
    } else
        "04" == e.req_payment_channel && (t.req_doku_id = $("#" + e.req_input_form[0]).val(),
            t.req_doku_pass = $("#" + e.req_input_form[1]).val(),
            t.req_session_id = e.req_session_id,
            t.req_chain_merchant = e.req_chain_merchant);
    return t
}

function backToMerchant(n) {
    var e = {
        doku_token: n.res_token_id,
        doku_pairing_code: n.req_pairing_code,
        doku_invoice_no: n.req_transaction_id,
        doku_amount: n.req_amount,
        doku_chain_merchant: n.req_chain_merchant,
        doku_currency: n.req_currency,
        doku_mall_id: n.req_merchant_code
    };
    $.ajax({
        url: n.req_server_url,
        data: e,
        type: "POST",
        success: function(e) {
            var t = JSON.parse(e);
            "15" == n.req_payment_channel ? "0000" != t.res_response_code ? (n.form_req_result_note = t.res_response_code,
                showResult(n)) : 1 == t.res_show_doku_page ? ($("[doku-div='form-payment']").html(n.req_result),
                $(".success").show(),
                $(".fail").remove(),
                $("doku-approval-code").html(t.res_approval_code),
                $("doku-card-number").html(t.res_mcn),
                $("doku-invoice-number").html(t.res_trans_id_merchant),
                $("doku-amount").html(t.res_amount),
                null != t.res_redirect_url ? ($("div.detail-result").children("a").attr("href", "#"),
                    $("div.detail-result").children("a").attr("onclick", "window.location = '" + t.res_redirect_url + "';")) : $("div.detail-result").children("a").remove()) : window.location = t.res_redirect_url : "04" == n.req_payment_channel && ("0000" != t.res_response_code ? (n.form_req_result_note = t.res_response_code,
                showResult(n)) : 1 == t.res_show_doku_page ? ($("[doku-div='form-payment']").html(n.req_result),
                $(".success").show(),
                $(".fail").remove(),
                $("doku-approval-code").html(t.res_approval_code),
                $("doku-invoice-number").html(n.req_transaction_id),
                null != t.res_card_number ? $("doku-card-number").html(t.res_card_number) : $("doku-card-number").parent().parent().remove(),
                null != t.res_promotion && ($("doku-discount").html(parseInt(t.res_promotion.amount).format(0, 3, ".", ",")),
                    parseInt(n.req_amount) - parseInt(t.res_promotion.amount) < 0 ? $("doku-total").html(parseInt(0).format(0, 3, ".", ",")) : $("doku-total").html(parseInt(parseInt(n.req_amount) - parseInt(t.res_promotion.amount)).format(0, 3, ".", ",")),
                    $("#doku-discount").show(),
                    $("#doku-total").show()),
                $("doku-amount").html(parseInt(n.req_amount).format(0, 3, ".", ",")),
                null != t.res_redirect_url ? ($("div.detail-result").children("a").attr("href", "#"),
                    $("div.detail-result").children("a").attr("onclick", "window.location = '" + t.res_redirect_url + "';")) : $("div.detail-result").children("a").remove()) : window.location = t.res_redirect_url)
        }
    })
}

function initiateFormWallet(e) {
    if ("0000" == e.res_response_code) {
        $("[doku-div='form-payment']").html(e.res_form_payment_dw),
            $("#respTabs").responsiveTabs(),
            $(".resp-tabDrop").wrap("<div class='selectdw'><div class='wrapper-select'></div></div>"),
            $("input[name$='optdw']").click(function() {
                var e = $(this).val();
                $("div.promocontent-dw").hide(),
                    $("#" + e).show()
            }),
            $(".amount").text(parseInt(e.req_amount).format(0, 3, ".", ","));
        JSON.parse(e.res_data_dw);
        formatWalletCash(e),
            formatWalletCc(e),
            btnOnBlur(),
            inquiryPromoCode(e);
        var t = $("input[type='button']").map(function() {
            return this.id
        }).get();
        for (i = 0; i < t.length; i++)
            $("#" + t[i]).click(function() {
                delete e[0],
                    formatFormWallet($.extend($(this).parents("form"), e))
            });
        var n = !1;
        timeout = e.res_timeout,
            "inline" == e.req_form_type ? (n = !0,
                pageTimeout = "<center>We are sorry your session has been expired. Please start a new payment request. Thank you.</center>") : pageTimeout = e.res_timeout_page,
            setTimeoutPage(timeout, pageTimeout, e, n, !1)
    } else
        e.form_req_result_note = e.res_response_code,
        showResult(e)
}

function inquiryPromoCode(a) {
    $(".redeempromocode").on("click", function() {
        var e = $("#promo-code").val(),
            t = a.req_doku_id,
            n = a.req_amount,
            r = a.req_merchant_code,
            i = a.req_chain_merchant;
        $.ajax({
            url: requestURL + "InquiryPromoCodeDW",
            data: {
                req_merchant_code: r,
                req_chain_merchant: i,
                req_amount: n,
                req_doku_id: t,
                req_promo_code: e
            },
            dataType: "json",
            type: "POST",
            success: function(e) {
                var t = JSON.parse(e.res_inquiry_promo_code),
                    n = "";
                null != t && "" != t ? (console.log("responseCode"),
                        console.log(t.responseCode),
                        n = "0000" == t.responseCode ? "Anda mendapat potongan sebesar Rp. " + t.amount : t.responseMsg) : n = "Promo Code yang Anda Masukkan Tidak Valid",
                    $(".promo-code-message").html(n)
            },
            error: function() {
                console.log("error inquiry promo code")
            }
        })
    })
}

function formatWalletCash(e) {
    var t = JSON.parse(e.res_data_dw);
    if ($(".nameuser").text(t.customerName),
        $(".saldo").text(t.listPaymentChannel[0].details.lastBalance.format(0, 3, ".", ",")),
        $(".amount-payment").text(parseInt(e.req_amount).format(0, 3, ".", ",")),
        $(".amount-discount").text(parseInt("0").format(0, 3, ".", ",")),
        $(".amount-total").text(parseInt(e.req_amount).format(0, 3, ".", ",")),
        null != t.listPromotion && 0 < t.listPromotion.length) {
        for (i = 0; i < t.listPromotion.length; i++)
            $("#doku-voucher").append('<option value="' + t.listPromotion[i].id + '">' + t.listPromotion[i].amount + "</option>");
        $("#doku-voucher").change(function() {
                $(".amount-discount").text(parseInt("0").format(0, 3, ".", ","))
            }),
            $("#doku-voucher").change(function() {
                "" != $("option:selected", this).val() && ($(".amount-discount").text(parseInt($("option:selected", this).text()).format(0, 3, ".", ",")),
                    parseInt(e.req_amount) - parseInt($("option:selected", this).text()) < 0 ? $(".amount-total").text(0) : $(".amount-total").text(parseInt(parseInt(e.req_amount) - parseInt($("option:selected", this).text())).format(0, 3, ".", ",")))
            }),
            $("#voucher_check").change(function() {
                1 == $(this).prop("checked") ? "" != $("#doku-voucher :selected").val() && ($(".amount-discount").text(parseInt($("#doku-voucher :selected").text()).format(0, 3, ".", ",")),
                    $(".amount-total").text(parseInt(parseInt(e.req_amount) - parseInt($("#doku-voucher :selected").text())).format(0, 3, ".", ","))) : ($(".amount-discount").text(parseInt("0").format(0, 3, ".", ",")),
                    $(".amount-total").text(parseInt(e.req_amount).format(0, 3, ".", ",")))
            })
    } else
        $("#doku-voucher-div").remove(),
        $("#doku-voucher-value").remove(),
        $("#doku-total-payment").remove()
}

function formatWalletCc(e) {
    var t = JSON.parse(e.res_data_dw);
    if (null == t.listPaymentChannel[1])
        $("#tablistItem2").remove(),
        $("#cc-tabs").remove();
    else if (null == t.listPaymentChannel[1].details)
        $("#frmcc").remove(),
        $(".doku-cc-number").payment("formatCardNumber"),
        $(".doku-cc-exp").payment("formatCardExpiry"),
        $(".doku-cvc").payment("formatCardCVC");
    else {
        $("#frmcc-manual").remove();
        var n = "";
        for (i = 0; i < t.listPaymentChannel[1].details.length; i++)
            0 == i ? (n = document.getElementById("listcard" + i).outerHTML,
                $("#r0").attr("checked", !0)) : $("#listcard" + (i - 1)).after(n.replace(new RegExp("0", "g"), i)),
            "4" == t.listPaymentChannel[1].details[i].cardNoMasked.substring(0, 1) ? $("#type-cc" + i).addClass("visa") : "5" == t.listPaymentChannel[1].details[i].cardNoMasked.substring(0, 1) && $("#type-cc" + i).addClass("mastercard"),
            $("#doku-cc-number" + i).text(t.listPaymentChannel[1].details[i].cardNoMasked)
    }
}

function formatFormWallet(e) {
    var t = $("#" + e[0].id + " :input").not("[type='button']").map(function() {
        return this.id
    }).get();
    if (validateForm(t))
        if ("frmcash" == e[0].id)
            e.req_promo_code = $("#" + t[t.length - 2]).val(),
            e.req_pin = $("#" + t[t.length - 1]).val(),
            submitFormWallet(e);
        else if ("frmcc" == e[0].id)
        e.req_cvv = $("#" + t[t.length - 1]).val(),
        e.req_cc_id = $("#" + e[0].id + " :input[type='radio']").map(function() {
            if (1 == $(this).prop("checked"))
                return this.id
        }).get()[0],
        submitFormWallet(e);
    else {
        var n = $("#" + t[1]).val().split(" / ");
        2 == n[1].length && (n[1] = "20" + n[1]),
            e.req_wallet_form = {
                CC_NAME: $("#" + t[2]).val(),
                CC_CARDNUMBER: $("#" + t[0]).val().replace(/ - /g, ""),
                CC_EXPIRYDATE: n[1].substring(2, 4) + n[0],
                CC_MOBILEPHONE: $("#" + t[3]).val(),
                CC_EMAIL: $("#" + t[4]).val(),
                CC_CVV: $("#" + t[t.length - 1]).val()
            },
            submitFormWallet(e)
    }
}

function submitFormWallet(t) {
    var e = JSON.parse(t.res_data_dw),
        n = new Object;
    n.req_token_id = t.res_token_id,
        n.req_pairing_code = t.req_pairing_code,
        n.req_words = t.req_words,
        n.req_domain_valid = document.location.origin,
        "frmcash" == t[0].id ? (n.req_dokuwallet = {
                req_channel_code: e.listPaymentChannel[0].channelCode,
                req_customer_pin: t.req_pin,
                req_inquiry_code: e.inquiryCode,
                req_customer_name: e.customerName,
                req_customer_email: e.customerEmail,
                req_doku_id: e.dokuId,
                req_promo_code: t.req_promo_code
            },
            1 == $("#voucher_check").prop("checked") && "" != $("#doku-voucher :selected").val() && (n.req_dokuwallet.req_promotion_id = $("#doku-voucher :selected").val())) : "frmcc" == t[0].id ? n.req_dokuwallet = {
            req_channel_code: e.listPaymentChannel[1].channelCode,
            req_inquiry_code: e.inquiryCode,
            req_doku_id: e.dokuId,
            req_link_id: e.listPaymentChannel[1].details[t.req_cc_id.substring(1, 2)].linkId,
            req_number: e.listPaymentChannel[1].details[t.req_cc_id.substring(1, 2)].cardNoEncrypt,
            req_date: e.listPaymentChannel[1].details[t.req_cc_id.substring(1, 2)].cardExpiryDateEncrypt,
            req_cvv: t.req_cvv
        } : (n.req_dokuwallet = t.req_wallet_form,
            n.req_dokuwallet.req_channel_code = e.listPaymentChannel[1].channelCode,
            n.req_dokuwallet.req_inquiry_code = e.inquiryCode,
            n.req_dokuwallet.req_doku_id = e.dokuId),
        $("[doku-div='form-payment']").html(t.req_loading),
        doku_timeout = setTimeout(function() {
            t.form_req_result_note = "Timeout",
                showResult(t),
                doku_ajax.abort()
        }, 65e3),
        doku_ajax = $.ajax({
            url: requestURL + "PrePayment",
            data: {
                data: JSON.stringify(n)
            },
            dataType: "json",
            type: "POST",
            success: function(e) {
                clearTimeout(doku_timeout),
                    "0000" == e.res_response_code ? null != e.res_result_3D ? (obj3D = JSON.parse(e.res_result_3D),
                        window.addEventListener("message", receiveMessage, !1),
                        $.fancybox.open([{
                            closeClick: !1,
                            type: "iframe",
                            openEffect: "fade",
                            closeEffect: "fade",
                            openSpeed: "slow",
                            closeSpeed: "slow",
                            content: form3D,
                            closeBtn: !1,
                            autoResize: !0,
                            helpers: {
                                overlay: {
                                    closeClick: !1
                                }
                            },
                            afterClose: function() {
                                obj3dResponse = $.parseJSON(threeDResponse),
                                    "0000" == obj3dResponse.res_response_code ? backToMerchant($.extend(t, $.extend(n, e))) : (t.form_req_result_note = obj3dResponse.res_response_code,
                                        showResult(t))
                            }
                        }]),
                        $("#doku-form-3d").find("#PaReq").val(obj3D.PAREQ),
                        $("#doku-form-3d").find("#MD").val(obj3D.MD),
                        $("#doku-form-3d").find("#TermUrl").val(obj3D.TERMURL),
                        $("#doku-form-3d").attr("action", obj3D.ACSURL),
                        $("#doku-form-3d").submit()) : backToMerchant($.extend(t, $.extend(n, e))) : (t.form_req_result_note = e.res_response_code,
                        showResult(t))
            }
        })
}

function showResult(e) {
    $("[doku-div='form-payment']").html(e.req_result),
        $(".fail").show(),
        $(".success").remove(),
        $("doku-approval-code").parent().parent().remove(),
        $("doku-card-number").parent().parent().remove(),
        $("doku-invoice-number").html(e.req_transaction_id),
        $("doku-amount").html(e.req_amount),
        $("doku-message").html("Error code : " + e.form_req_result_note.replace(new RegExp("_", "g"), " ")),
        $("div.detail-result").children("a").attr("href", "#"),
        $("div.detail-result").children("a").html("Try Again"),
        e.retry = 1,
        $("div.detail-result").children("a").attr("onclick", "getForm(" + JSON.stringify(e) + ")")
}

function DokuToken(e) {
    var t = new Object;
    if (null == data_obj.res_data_dw ? t.responseCode = "9999" : (data_obj.req_doku_id = $("#doku-username").val(),
            console.log("data_obj.req_doku_id == " + data_obj.req_doku_id),
            void 0 !== data_obj.res_data_dw && (t = $.parseJSON(data_obj.res_data_dw))),
        "0000" == t.responseCode && null != data_obj.req_ref_account_id && "" != data_obj.req_ref_account_id)
        processInlineForm(data_obj);
    else {
        if ("15" == data_obj.req_payment_channel)
            var n = ["doku-cc-exp", "doku-cc-number", "doku-name-cc", "doku-cvc"];
        else if ("04" == data_obj.req_payment_channel)
            n = ["doku-username", "doku-password"];
        if (validateFormInline(n)) {
            $.fancybox.open([{
                closeClick: !1,
                type: "html",
                openEffect: "fade",
                closeEffect: "fade",
                openSpeed: "slow",
                closeSpeed: "slow",
                padding: 0,
                margin: 0,
                overflow: "hidden",
                width: "360px",
                autoResize: !0,
                content: "<center><br><img src='" + loadingUrl + "' class='doku-loader-logo'><br><br><span class='doku-loader-text'>Please wait... your request is being processed</span></center>",
                closeBtn: !1,
                helpers: {
                    overlay: {
                        closeClick: !1
                    }
                }
            }]);
            var r = formatFormInline();
            doku_timeout = setTimeout(function() {
                    $.fancybox.open([{
                            closeClick: !1,
                            type: "html",
                            openEffect: "fade",
                            closeEffect: "fade",
                            openSpeed: "slow",
                            closeSpeed: "slow",
                            content: "<center><br><img width='10%' src='" + failedUrl + "' class='doku-failed-logo'><br><br><span class='doku-loader-text'>Request failed... Timeout</span><br><br><a href='#' class='doku-btn-retry' onclick='closePopup(); $(\"#" + n[0] + "\").focus();'>Try Again</a></center>",
                            closeBtn: !1,
                            helpers: {
                                overlay: {
                                    closeClick: !1
                                }
                            }
                        }]),
                        doku_ajax.abort()
                }, 65e3),
                doku_ajax = $.ajax({
                    url: requestURL + "getToken",
                    data: {
                        data: JSON.stringify(r)
                    },
                    dataType: "json",
                    type: "POST",
                    success: function(e) {
                        for (clearTimeout(doku_timeout),
                            i = 0; i < n.length; i++)
                            $("#" + n[i]).val("");
                        "0000" == e.res_response_code ? processInlineForm(e) : (console.log(e),
                            $.fancybox.open([{
                                closeClick: !1,
                                type: "html",
                                openEffect: "fade",
                                closeEffect: "fade",
                                openSpeed: "slow",
                                closeSpeed: "slow",
                                content: "<center><br><img width='10%' src='" + failedUrl + "' class='doku-failed-logo'><br><br><span class='doku-loader-text'>Request failed... error code : " + e.res_response_code.replace(new RegExp("_", "g"), " ") + "</span><br><br><a href='#' class='doku-btn-retry' onclick='closePopup(); $(\"#" + n[0] + "\").focus();'>Try Again</a></center>",
                                closeBtn: !1,
                                helpers: {
                                    overlay: {
                                        closeClick: !1
                                    }
                                }
                            }]))
                    }
                }),
                pageTimeout = "<center>We are sorry your session has been expired. Please start a new payment request. Thank you.</center>",
                setTimeoutPage(data_obj.res_timeout, pageTimeout, data_obj, !0, !0)
        }
    }
}

function closePopup() {
    $.fancybox.close()
}

function validateFormInline(e) {
    var t = !0;
    for (i = 0; i < e.length; i++)
        if (null != $("#" + e[i]).attr("validation-rules")) {
            var n = $("#" + e[i]).attr("validation-rules").split("|"),
                r = new Object;
            for (j = 0; j < n.length; j++) {
                if (0 == (r = checkRules(e[i], n[j])).status)
                    break
            }
            0 == r.status && ($("#" + e[i]).parent().addClass("has-error"),
                0 == $("#" + e[i]).parent().children("font").length ? $("#" + e[i]).parent().append("<font color='red'>" + r.msg + "</font>") : $("#" + e[i]).parent().children("font").html(r.msg),
                t = !1)
        }
    return t
}

function formatFormInline() {
    var e = new Object;
    if (e.req_merchant_code = data_obj.req_merchant_code,
        e.req_payment_channel = data_obj.req_payment_channel,
        e.req_transaction_id = data_obj.req_transaction_id,
        e.req_amount = data_obj.req_amount,
        e.req_currency = data_obj.req_currency,
        e.req_pairing_code = data_obj.res_request_code,
        e.req_access_type = "W",
        e.req_domain_valid = document.location.origin,
        "15" == data_obj.req_payment_channel) {
        if (null != data_obj.req_customer_id && "" != data_obj.req_customer_id) {
            if (null == data_obj.res_tokenization) {
                var t = $("#doku-cc-exp").val(),
                    n = $("#doku-cc-number").val();
                2 == (r = t.split(" / "))[1].length && (r[1] = "20" + r[1]),
                    e.req_date = r[1].substring(2, 4) + r[0],
                    e.req_number = n.replace(/ - /g, ""),
                    e.req_name = $("#doku-name-cc").val()
            }
            1 == $("#doku-save-cc").prop("checked") ? e.req_save_customer = "SAVE" : e.req_save_customer = "UNSAVE"
        } else {
            var r;
            t = $("#doku-cc-exp").val(),
                n = $("#doku-cc-number").val();
            2 == (r = t.split(" / "))[1].length && (r[1] = "20" + r[1]),
                e.req_date = r[1].substring(2, 4) + r[0],
                e.req_number = n.replace(/ - /g, ""),
                e.req_name = $("#doku-name-cc").val()
        }
        e.req_secret = $("#doku-cvc").val()
    } else
        "04" == data_obj.req_payment_channel && (e.req_doku_id = $("#doku-username").val(),
            e.req_doku_pass = $("#doku-password").val(),
            e.req_session_id = data_obj.req_session_id,
            e.req_chain_merchant = data_obj.req_chain_merchant);
    return e
}

function processInlineForm(e) {
    "15" == data_obj.req_payment_channel ? (e.res_pairing_code = data_obj.res_request_code,
        e.res_invoice_no = data_obj.req_transaction_id,
        e.res_amount = data_obj.req_amount,
        e.res_chain_merchant = data_obj.req_chain_merchant,
        e.res_currency = data_obj.req_currency,
        e.res_mall_id = data_obj.req_merchant_code,
        $("#doku-cc-exp").val(""),
        $("#doku-cc-number").val(""),
        $("#doku-name-cc").val(""),
        $("#doku-cvc").val(""),
        null != e.res_result_3D ? (obj3D = JSON.parse(e.res_result_3D),
            window.addEventListener("message", receiveMessage, !1),
            $.fancybox.open([{
                closeClick: !1,
                type: "iframe",
                content: form3D,
                padding: 0,
                margin: 0,
                overflow: "hidden",
                width: "360px",
                closeBtn: !1,
                autoResize: !0,
                helpers: {
                    overlay: {
                        closeClick: !1
                    }
                },
                afterClose: function() {
                    obj3dResponse = $.parseJSON(threeDResponse),
                        e = $.extend(e, obj3dResponse),
                        getToken(e)
                }
            }]),
            $("#doku-form-3d").find("#PaReq").val(obj3D.PAREQ),
            $("#doku-form-3d").find("#MD").val(obj3D.MD),
            $("#doku-form-3d").find("#TermUrl").val(obj3D.TERMURL),
            $("#doku-form-3d").attr("action", obj3D.ACSURL),
            $("#doku-form-3d").submit()) : getToken(e)) : "04" == data_obj.req_payment_channel && formatInlineFormDW(e)
}

function formatInlineFormDW(e) {
    if ("0000" == e.res_response_code) {
        $.fancybox.open([{
                closeClick: !1,
                type: "html",
                openEffect: "fade",
                closeEffect: "fade",
                openSpeed: "slow",
                closeSpeed: "slow",
                wrapCSS: "doku-wrap",
                content: e.res_form_payment_dw,
                helpers: {
                    overlay: {
                        closeClick: !1
                    }
                }
            }]),
            $("#respTabs").responsiveTabs(),
            $(".resp-tabDrop").wrap("<div class='selectdw'><div class='wrapper-select'></div></div>"),
            $("input[name$='optdw']").click(function() {
                var e = $(this).val();
                $("div.promocontent-dw").hide(),
                    $("#" + e).show()
            }),
            formatWalletCashInline(JSON.parse(e.res_data_dw)),
            formatWalletCcInline(JSON.parse(e.res_data_dw)),
            btnOnBlur();
        var t = $(".fancybox-wrap").find("input[type='button']").map(function() {
            return this.id
        }).get();
        for (i = 0; i < t.length; i++)
            $("#" + t[i]).click(function() {
                formatFormWalletInline($.extend($(this).parents("form"), e))
            })
    } else {
        getToken(e)
    }
}

function formatWalletCashInline(e) {
    if ($(".nameuser").text(e.customerName),
        $(".saldo").text(e.listPaymentChannel[0].details.lastBalance.format(0, 3, ".", ",")),
        $(".amount-payment").text(parseInt(data_obj.req_amount).format(0, 3, ".", ",")),
        $(".amount-discount").text(parseInt("0").format(0, 3, ".", ",")),
        $(".amount-total").text(parseInt(data_obj.req_amount).format(0, 3, ".", ",")),
        null != e.listPromotion && 0 < e.listPromotion.length) {
        for (i = 0; i < e.listPromotion.length; i++)
            $("#doku-voucher").append('<option value="' + e.listPromotion[i].id + '">' + e.listPromotion[i].amount + "</option>");
        $("#doku-voucher").change(function() {
                $(".amount-discount").text(parseInt("0").format(0))
            }),
            $("#doku-voucher").change(function() {
                "" != $("option:selected", this).val() && ($(".amount-discount").text(parseInt($("option:selected", this).text()).format(0, 3, ".", ",")),
                    parseInt(data_obj.req_amount) - parseInt($("option:selected", this).text()) < 0 ? $(".amount-total").text(0) : $(".amount-total").text(parseInt(parseInt(data_obj.req_amount) - parseInt($("option:selected", this).text())).format(0, 3, ".", ",")))
            }),
            $("#voucher_check").change(function() {
                1 == $(this).prop("checked") ? "" != $("#doku-voucher :selected").val() && ($(".amount-discount").text(parseInt($("#doku-voucher :selected").text()).format(0, 3, ".", ",")),
                    $(".amount-total").text(parseInt(parseInt(data_obj.req_amount) - parseInt($("#doku-voucher :selected").text())).format(0, 3, ".", ","))) : ($(".amount-discount").text(parseInt("0").format(0, 3, ".", ",")),
                    $(".amount-total").text(parseInt(data_obj.req_amount).format(0, 3, ".", ",")))
            })
    } else
        $("#doku-voucher-div").remove(),
        $("#doku-voucher-value").remove(),
        $("#doku-total-payment").remove();
    inquiryPromoCode(data_obj)
}

function formatWalletCcInline(e) {
    if (null == e.listPaymentChannel[1])
        $("#tablistItem2").remove(),
        $("#cc-tabs").remove();
    else if (null == e.listPaymentChannel[1].details)
        $("#frmcc").remove(),
        $(".doku-cc-number").payment("formatCardNumber"),
        $(".doku-cc-exp").payment("formatCardExpiry"),
        $(".doku-cvc").payment("formatCardCVC");
    else {
        $("#frmcc-manual").remove();
        var t = "";
        for (i = 0; i < e.listPaymentChannel[1].details.length; i++)
            0 == i ? (t = document.getElementById("listcard" + i).outerHTML,
                $("#r0").attr("checked", !0)) : $("#listcard" + (i - 1)).after(t.replace(new RegExp("0", "g"), i)),
            "4" == e.listPaymentChannel[1].details[i].cardNoMasked.substring(0, 1) ? $("#type-cc" + i).addClass("visa") : "5" == e.listPaymentChannel[1].details[i].cardNoMasked.substring(0, 1) && $("#type-cc" + i).addClass("mastercard"),
            $("#doku-cc-number" + i).text(e.listPaymentChannel[1].details[i].cardNoMasked)
    }
}

function formatFormWalletInline(e) {
    var t = $("#" + e[0].id + " :input").not("[type='button']").map(function() {
        return this.id
    }).get();
    if (validateForm(t))
        if ("frmcash" == e[0].id)
            e.req_promo_code = $("#" + t[t.length - 2]).val(),
            e.req_pin = $("#" + t[t.length - 1]).val(),
            submitFormWalletInline(e);
        else if ("frmcc" == e[0].id)
        e.req_cvv = $("#" + t[t.length - 1]).val(),
        e.req_cc_id = $("#" + e[0].id + " :input[type='radio']").map(function() {
            if (1 == $(this).prop("checked"))
                return this.id
        }).get()[0],
        submitFormWalletInline(e);
    else {
        var n = $("#" + t[1]).val().split(" / ");
        2 == n[1].length && (n[1] = "20" + n[1]),
            e.req_wallet_form = {
                CC_NAME: $("#" + t[2]).val(),
                CC_CARDNUMBER: $("#" + t[0]).val().replace(/ - /g, ""),
                CC_EXPIRYDATE: n[1].substring(2, 4) + n[0],
                CC_MOBILEPHONE: $("#" + t[3]).val(),
                CC_EMAIL: $("#" + t[4]).val(),
                CC_CVV: $("#" + t[t.length - 1]).val()
            },
            submitFormWalletInline(e)
    }
}

function submitFormWalletInline(n) {
    var e = JSON.parse(n.res_data_dw),
        t = new Object;
    t.req_token_id = n.res_token_id,
        t.req_pairing_code = data_obj.res_request_code,
        t.req_words = data_obj.req_words,
        t.req_domain_valid = document.location.origin,
        "frmcash" == n[0].id ? (t.req_dokuwallet = {
                req_channel_code: e.listPaymentChannel[0].channelCode,
                req_customer_pin: n.req_pin,
                req_inquiry_code: e.inquiryCode,
                req_customer_name: e.customerName,
                req_customer_email: e.customerEmail,
                req_doku_id: e.dokuId,
                req_promo_code: n.req_promo_code
            },
            1 == $("#voucher_check").prop("checked") && "" != $("#doku-voucher :selected").val() && (t.req_dokuwallet.req_promotion_id = $("#doku-voucher :selected").val())) : "frmcc" == n[0].id ? t.req_dokuwallet = {
            req_channel_code: e.listPaymentChannel[1].channelCode,
            req_inquiry_code: e.inquiryCode,
            req_doku_id: e.dokuId,
            req_link_id: e.listPaymentChannel[1].details[n.req_cc_id.substring(1, 2)].linkId,
            req_number: e.listPaymentChannel[1].details[n.req_cc_id.substring(1, 2)].cardNoEncrypt,
            req_date: e.listPaymentChannel[1].details[n.req_cc_id.substring(1, 2)].cardExpiryDateEncrypt,
            req_cvv: n.req_cvv
        } : (t.req_dokuwallet = n.req_wallet_form,
            t.req_dokuwallet.req_channel_code = e.listPaymentChannel[1].channelCode,
            t.req_dokuwallet.req_inquiry_code = e.inquiryCode,
            t.req_dokuwallet.req_doku_id = e.dokuId),
        doku_timeout = setTimeout(function() {
            $.fancybox.open([{
                    closeClick: !1,
                    type: "html",
                    openEffect: "fade",
                    closeEffect: "fade",
                    openSpeed: "slow",
                    closeSpeed: "slow",
                    content: "<center><br><img width='10%' src='" + failedUrl + "' class='doku-failed-logo'><br><br><span class='doku-loader-text'>Request failed... Timeout</span><br><br><a href='#' class='doku-btn-retry' onclick='closePopup();'>Try Again</a></center>",
                    closeBtn: !1,
                    helpers: {
                        overlay: {
                            closeClick: !1
                        }
                    }
                }]),
                doku_ajax.abort()
        }, 65e3),
        doku_ajax = $.ajax({
            url: requestURL + "PrePayment",
            data: {
                data: JSON.stringify(t)
            },
            dataType: "json",
            type: "POST",
            beforeSend: function() {
                $.fancybox.open([{
                    closeClick: !1,
                    type: "html",
                    openEffect: "fade",
                    closeEffect: "fade",
                    openSpeed: "slow",
                    closeSpeed: "slow",
                    content: "<center><br><img src='" + loadingUrl + "' class='doku-loader-logo'><br><br><span class='doku-loader-text'>Please wait... your request is being processed</span></center>",
                    closeBtn: !1,
                    helpers: {
                        overlay: {
                            closeClick: !1
                        }
                    }
                }])
            },
            success: function(t) {
                if (clearTimeout(doku_timeout),
                    "0000" == t.res_response_code)
                    if (null != t.res_result_3D)
                        obj3D = JSON.parse(t.res_result_3D),
                        window.addEventListener("message", receiveMessage, !1),
                        $.fancybox.open([{
                            closeClick: !1,
                            type: "iframe",
                            content: form3D,
                            closeBtn: !1,
                            autoResize: !0,
                            helpers: {
                                overlay: {
                                    closeClick: !1
                                }
                            },
                            afterClose: function() {
                                obj3dResponse = $.parseJSON(threeDResponse),
                                    t.res_token_id = n.res_token_id,
                                    t.res_pairing_code = data_obj.res_request_code,
                                    t.res_invoice_no = data_obj.req_transaction_id,
                                    t.res_amount = data_obj.req_amount,
                                    t.res_chain_merchant = data_obj.req_chain_merchant,
                                    t.res_currency = data_obj.req_currency,
                                    t.res_mall_id = data_obj.req_merchant_code,
                                    delete t.res_result_3D,
                                    obj = $.extend(t, obj3dResponse);
                                var e = obj;
                                getToken(e)
                            }
                        }]),
                        $("#doku-form-3d").find("#PaReq").val(obj3D.PAREQ),
                        $("#doku-form-3d").find("#MD").val(obj3D.MD),
                        $("#doku-form-3d").find("#TermUrl").val(obj3D.TERMURL),
                        $("#doku-form-3d").attr("action", obj3D.ACSURL),
                        $("#doku-form-3d").submit();
                    else {
                        t.res_token_id = n.res_token_id,
                            t.res_pairing_code = data_obj.res_request_code,
                            t.res_invoice_no = data_obj.req_transaction_id,
                            t.res_amount = data_obj.req_amount,
                            t.res_chain_merchant = data_obj.req_chain_merchant,
                            t.res_currency = data_obj.req_currency,
                            t.res_mall_id = data_obj.req_merchant_code;
                        var e = t;
                        $.fancybox.close(),
                            getToken(e)
                    }
                else
                    $.fancybox.open([{
                        closeClick: !1,
                        type: "html",
                        openEffect: "fade",
                        closeEffect: "fade",
                        openSpeed: "slow",
                        closeSpeed: "slow",
                        content: "<center><br><img width='10%' src='" + failedUrl + "' class='doku-failed-logo'><br><br><span class='doku-loader-text'>Request failed... error code : " + t.res_response_code + "</span><br><br><a href='#' class='doku-btn-retry' onclick='closePopup();'>Try Again</a></center>",
                        closeBtn: !1,
                        helpers: {
                            overlay: {
                                closeClick: !1
                            }
                        }
                    }])
            }
        })
}

function dokuMandiriInitiate(l) {
    $("#" + l.req_cc_field).focusout(function() {
        for (var e = $("#cc_number").val(), t = "", n = !1, r = 0; r < e.length; r++)
            "0" <= e.charAt(r) && e.charAt(r) <= "9" && (t += e.charAt(r));
        for (var i = 0, a = 1; a <= t.length; a++) {
            var o = t.charAt(t.length - a);
            if (a % 2 == 0) {
                var s = 2 * o;
                9 < s ? (i += 1,
                    i += s % 10 - 0) : i += s - 0
            } else
                i += o - 0
        }
        i % 10 || (n = !0),
            n && ($("#" + l.req_cc_field).removeClass("error"),
                $("#" + l.req_challenge_field).val($("#cc_number").val().replace(/ - /g, "").substr(6, 16)))
    })
}

function setTimeoutPage(e, t, n, r, i) {
    timeleft(e = parseInt(e), i),
        e *= 60,
        doku_timeout_page = setTimeout(function() {
            r && closePopup(),
                updatePreTransactionTimeout(t, n),
                doku_ajax.abort()
        }, 1e3 * e)
}

function updatePreTransactionTimeout(t, e) {
    $.ajax({
        url: requestURL + "timeout",
        data: {
            req_merchant_code: e.req_merchant_code,
            req_chain_merchant: e.req_chain_merchant,
            req_transaction_id: e.req_transaction_id
        },
        dataType: "json",
        type: "POST",
        success: function(e) {
            $("[doku-div='form-payment']").html(t),
                void 0 === e.res_redirect_url || null == e.res_redirect_url || "" == e.res_redirect_url ? $("[doku-div='form-payment']").find("a").remove() : $("[doku-div='form-payment']").find(".backtomerchant").attr("href", e.res_redirect_url)
        },
        error: function() {
            $("[doku-div='form-payment']").html(t),
                void 0 === data.res_redirect_url || null == data.res_redirect_url || "" == data.res_redirect_url ? $("[doku-div='form-payment']").find("a").remove() : $("[doku-div='form-payment']").find(".backtomerchant").attr("href", data.res_redirect_url)
        }
    })
}

function getDeviceInfo() {
    var e = new ClientJS,
        t = e.getFingerprint(),
        n = e.getFonts(),
        r = e.getMimeTypes(),
        i = e.getBrowser(),
        a = e.getOS(),
        o = e.getOSVersion(),
        s = e.getCPU(),
        l = e.getUserAgentLowerCase(),
        c = e.getScreenPrint(),
        d = e.getCurrentResolution(),
        u = e.getAvailableResolution(),
        p = e.getColorDepth(),
        m = e.getBrowserVersion(),
        h = e.getBrowserMajorVersion(),
        f = e.getEngine(),
        g = e.getEngineVersion(),
        v = e.getPlugins(),
        _ = e.getDevice(),
        y = e.getDeviceType(),
        b = e.getDeviceVendor(),
        w = e.getTimeZone(),
        k = e.isSessionStorage(),
        x = e.isLocalStorage(),
        $ = e.getCPU(),
        C = new Object;
    return C.fingerprint = t,
        C.fonts = n,
        C.mimeTypes = r,
        C.browser = i,
        C.os = a,
        C.osVersion = o,
        C.cpu = s,
        C.userAgentLowerCase = l,
        C.screenPrint = c,
        C.currentResolution = d,
        C.availableResolution = u,
        C.colorDepth = p,
        C.browserVersion = m,
        C.browserMajorVersion = h,
        C.engine = f,
        C.engineVersion = g,
        C.plugins = v,
        C.device = _,
        C.deviceType = y,
        C.deviceVendor = b,
        C.timezone_offset = w,
        C.session_storage = k,
        C.local_storage = x,
        C.cpu_class = $,
        JSON.stringify(C)
}

function timeleft(e, t) {
    var n = new Date;
    n.setMinutes(n.getMinutes() + e),
        n.setSeconds(n.getSeconds() + 0),
        $(".select-payment-channel").countdown(n, function(e) {
            $(".doku-timeleft").html('Time Left :<data class="doku-time"> ' + e.strftime("%Mm:%Ss") + "</data>"),
                t && ($(".doku-timeleft").css({
                        "text-align": "center",
                        "font-family": "dinpro-reg",
                        color: "#4A4A4A"
                    }),
                    $(".doku-h3").css({
                        display: "block",
                        "font-size": "24px",
                        "margin-left": "0",
                        "margin-right": "0"
                    }),
                    $(".doku-time").css({
                        color: "#e0272a",
                        "font-family": "dinpro-bold"
                    }))
        }).on("finish.countdown", function() {
            $(".doku-timeleft").html("")
        })
}


Number.prototype.format = function(e, t, n, r) {
        var i = "\\d(?=(\\d{" + (t || 3) + "})+" + (0 < e ? "\\D" : "$") + ")",
            a = this.toFixed(Math.max(0, ~~e));
        return (r ? a.replace(".", r) : a).replace(new RegExp(i, "g"), "$&" + (n || ","))
    },
    Object.size = function(e) {
        var t, n = 0;
        for (t in e)
            e.hasOwnProperty(t) && n++;
        return n
    };
var RespTabs = {};
! function(e) {
    "use strict";
    jQuery.fn.responsiveTabs = function() {
        return this.each(function() {
            Object.create(RespTabs).init(this)
        })
    }
}(),
RespTabs.init = function(e) {
        var i = $(e).children();
        $headers = i.children(":header"),
            $contents = i.children("div"),
            $headers.addClass("resp-headings"),
            $contents.addClass("resp-contents");
        var t = $('[resp-tab="default"]');
        t.length || (t = i.first()),
            t.children("div").addClass("resp-content__active").attr("aria-hidden", "false"),
            $contents.not(".resp-content__active").hide().attr("aria-hidden", "true"),
            t.children(":header").addClass("resp-heading__active");
        var a = $("<ul></ul>", {
                class: "resp-tablist"
            }),
            o = $("<select></select>", {
                class: "resp-tabDrop"
            }),
            s = 1;
        $headers.each(function() {
                var e = $(this),
                    t = $(this).next(),
                    n = $("<option></option>", {
                        class: "resp-tabDropOption",
                        id: "tabOption" + s,
                        text: e.text()
                    }),
                    r = $("<li></li>", {
                        class: "resp-tablistItem",
                        id: "tablistItem" + s,
                        text: e.text(),
                        click: function() {
                            i.find(".resp-content__active").toggle().removeClass("resp-content__active").attr("aria-hidden", "true").prev().removeClass("resp-heading__active"),
                                t.toggle().addClass("resp-content__active").attr("aria-hidden", "false"),
                                e.addClass("resp-heading__active"),
                                a.find(".resp-tablistItem__active").removeClass("resp-tablistItem__active"),
                                r.addClass("resp-tablistItem__active"),
                                $(".resp-tabDropOption").removeAttr("selected"),
                                $(".resp-tabDropOption").eq($(".resp-tablistItem__active").index()).attr("selected", "selected")
                        }
                    });
                t.hasClass("resp-content__active") && r.addClass("resp-tablistItem__active"),
                    a.append(r),
                    o.append(n),
                    o.change(function() {
                        i.find(".resp-content__active").toggle().removeClass("resp-content__active").attr("aria-hidden", "true").prev().removeClass("resp-heading__active"),
                            $(".resp-contents").eq($(".resp-tabDrop :selected").index()).toggle().addClass("resp-content__active").attr("aria-hidden", "false"),
                            $(".resp-headings").eq($(".resp-tabDrop :selected").index()).addClass("resp-heading__active"),
                            $(".resp-tablistItem").removeClass("resp-tablistItem__active"),
                            $(".resp-tablistItem").eq($(".resp-tabDrop :selected").index()).addClass("resp-tablistItem__active")
                    }),
                    s++
            }),
            $(document).ready(function() {
                $(".resp-tabDropOption").eq(t.index()).attr("selected", "selected")
            }),
            i.parent().before(a),
            i.parent().before(o)
    },
    function(r, n, q, d) {
        var i = q("html"),
            a = q(r),
            c = q(n),
            S = q.fancybox = function() {
                S.open.apply(this, arguments)
            },
            o = navigator.userAgent.match(/msie/i),
            s = null,
            l = n.createTouch !== d,
            u = function(e) {
                return e && e.hasOwnProperty && e instanceof q
            },
            p = function(e) {
                return e && "string" === q.type(e)
            },
            T = function(e) {
                return p(e) && 0 < e.indexOf("%")
            },
            P = function(e, t) {
                var n = parseInt(e, 10) || 0;
                return t && T(e) && (n *= S.getViewport()[t] / 100),
                    Math.ceil(n)
            },
            j = function(e, t) {
                return P(e, t) + "px"
            };
        q.extend(S, {
                version: "2.1.5",
                defaults: {
                    padding: 15,
                    margin: 20,
                    width: 800,
                    height: 600,
                    minWidth: 100,
                    minHeight: 100,
                    maxWidth: 9999,
                    maxHeight: 9999,
                    pixelRatio: 1,
                    autoSize: !0,
                    autoHeight: !1,
                    autoWidth: !1,
                    autoResize: !0,
                    autoCenter: !l,
                    fitToView: !0,
                    aspectRatio: !1,
                    topRatio: .5,
                    leftRatio: .5,
                    scrolling: "auto",
                    wrapCSS: "",
                    arrows: !0,
                    closeBtn: !0,
                    closeClick: !1,
                    nextClick: !1,
                    mouseWheel: !0,
                    autoPlay: !1,
                    playSpeed: 3e3,
                    preload: 3,
                    modal: !1,
                    loop: !0,
                    ajax: {
                        dataType: "html",
                        headers: {
                            "X-fancyBox": !0
                        }
                    },
                    iframe: {
                        scrolling: "auto",
                        preload: !0
                    },
                    swf: {
                        wmode: "transparent",
                        allowfullscreen: "true",
                        allowscriptaccess: "always"
                    },
                    keys: {
                        next: {
                            13: "left",
                            34: "up",
                            39: "left",
                            40: "up"
                        },
                        prev: {
                            8: "right",
                            33: "down",
                            37: "right",
                            38: "down"
                        },
                        close: [27],
                        play: [32],
                        toggle: [70]
                    },
                    direction: {
                        next: "left",
                        prev: "right"
                    },
                    scrollOutside: !0,
                    index: 0,
                    type: null,
                    href: null,
                    content: null,
                    title: null,
                    tpl: {
                        wrap: '<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>',
                        image: '<img class="fancybox-image" src="{href}" alt="" />',
                        iframe: '<iframe id="fancybox-frame{rnd}" name="fancybox-frame{rnd}" class="fancybox-iframe" frameborder="0" vspace="0" hspace="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen' + (o ? ' allowtransparency="true"' : "") + "></iframe>",
                        error: '<p class="fancybox-error">The requested content cannot be loaded.<br/>Please try again later.</p>',
                        closeBtn: '<a title="Close" class="fancybox-item fancybox-close" href="javascript:;"></a>',
                        next: '<a title="Next" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',
                        prev: '<a title="Previous" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'
                    },
                    openEffect: "fade",
                    openSpeed: 250,
                    openEasing: "swing",
                    openOpacity: !0,
                    openMethod: "zoomIn",
                    closeEffect: "fade",
                    closeSpeed: 250,
                    closeEasing: "swing",
                    closeOpacity: !0,
                    closeMethod: "zoomOut",
                    nextEffect: "elastic",
                    nextSpeed: 250,
                    nextEasing: "swing",
                    nextMethod: "changeIn",
                    prevEffect: "elastic",
                    prevSpeed: 250,
                    prevEasing: "swing",
                    prevMethod: "changeOut",
                    helpers: {
                        overlay: !0,
                        title: !0
                    },
                    onCancel: q.noop,
                    beforeLoad: q.noop,
                    afterLoad: q.noop,
                    beforeShow: q.noop,
                    afterShow: q.noop,
                    beforeChange: q.noop,
                    beforeClose: q.noop,
                    afterClose: q.noop
                },
                group: {},
                opts: {},
                previous: null,
                coming: null,
                current: null,
                isActive: !1,
                isOpen: !1,
                isOpened: !1,
                wrap: null,
                skin: null,
                outer: null,
                inner: null,
                player: {
                    timer: null,
                    isActive: !1
                },
                ajaxLoad: null,
                imgPreload: null,
                transitions: {},
                helpers: {},
                open: function(l, c) {
                    if (l && (q.isPlainObject(c) || (c = {}),
                            !1 !== S.close(!0)))
                        return q.isArray(l) || (l = u(l) ? q(l).get() : [l]),
                            q.each(l, function(e, t) {
                                var n, r, i, a, o, s = {};
                                "object" === q.type(t) && (t.nodeType && (t = q(t)),
                                        u(t) ? (s = {
                                                href: t.data("fancybox-href") || t.attr("href"),
                                                title: t.data("fancybox-title") || t.attr("title"),
                                                isDom: !0,
                                                element: t
                                            },
                                            q.metadata && q.extend(!0, s, t.metadata())) : s = t),
                                    n = c.href || s.href || (p(t) ? t : null),
                                    r = c.title !== d ? c.title : s.title || "",
                                    !(a = (i = c.content || s.content) ? "html" : c.type || s.type) && s.isDom && ((a = t.data("fancybox-type")) || (a = (a = t.prop("class").match(/fancybox\.(\w+)/)) ? a[1] : null)),
                                    p(n) && (a || (S.isImage(n) ? a = "image" : S.isSWF(n) ? a = "swf" : "#" === n.charAt(0) ? a = "inline" : p(t) && (a = "html",
                                            i = t)),
                                        "ajax" === a && (n = (o = n.split(/\s+/, 2)).shift(),
                                            o = o.shift())),
                                    i || ("inline" === a ? n ? i = q(p(n) ? n.replace(/.*(?=#[^\s]+$)/, "") : n) : s.isDom && (i = t) : "html" === a ? i = n : !a && !n && s.isDom && (a = "inline",
                                        i = t)),
                                    q.extend(s, {
                                        href: n,
                                        type: a,
                                        content: i,
                                        title: r,
                                        selector: o
                                    }),
                                    l[e] = s
                            }),
                            S.opts = q.extend(!0, {}, S.defaults, c),
                            c.keys !== d && (S.opts.keys = !!c.keys && q.extend({}, S.defaults.keys, c.keys)),
                            S.group = l,
                            S._start(S.opts.index)
                },
                cancel: function() {
                    var e = S.coming;
                    e && !1 !== S.trigger("onCancel") && (S.hideLoading(),
                        S.ajaxLoad && S.ajaxLoad.abort(),
                        S.ajaxLoad = null,
                        S.imgPreload && (S.imgPreload.onload = S.imgPreload.onerror = null),
                        e.wrap && e.wrap.stop(!0, !0).trigger("onReset").remove(),
                        S.coming = null,
                        S.current || S._afterZoomOut(e))
                },
                close: function(e) {
                    S.cancel(),
                        !1 !== S.trigger("beforeClose") && (S.unbindEvents(),
                            S.isActive && (S.isOpen && !0 !== e ? (S.isOpen = S.isOpened = !1,
                                S.isClosing = !0,
                                q(".fancybox-item, .fancybox-nav").remove(),
                                S.wrap.stop(!0, !0).removeClass("fancybox-opened"),
                                S.transitions[S.current.closeMethod]()) : (q(".fancybox-wrap").stop(!0).trigger("onReset").remove(),
                                S._afterZoomOut())))
                },
                play: function(e) {
                    var t = function() {
                            clearTimeout(S.player.timer)
                        },
                        n = function() {
                            t(),
                                S.current && S.player.isActive && (S.player.timer = setTimeout(S.next, S.current.playSpeed))
                        },
                        r = function() {
                            t(),
                                c.unbind(".player"),
                                S.player.isActive = !1,
                                S.trigger("onPlayEnd")
                        };
                    !0 === e || !S.player.isActive && !1 !== e ? S.current && (S.current.loop || S.current.index < S.group.length - 1) && (S.player.isActive = !0,
                        c.bind({
                            "onCancel.player beforeClose.player": r,
                            "onUpdate.player": n,
                            "beforeLoad.player": t
                        }),
                        n(),
                        S.trigger("onPlayStart")) : r()
                },
                next: function(e) {
                    var t = S.current;
                    t && (p(e) || (e = t.direction.next),
                        S.jumpto(t.index + 1, e, "next"))
                },
                prev: function(e) {
                    var t = S.current;
                    t && (p(e) || (e = t.direction.prev),
                        S.jumpto(t.index - 1, e, "prev"))
                },
                jumpto: function(e, t, n) {
                    var r = S.current;
                    r && (e = P(e),
                        S.direction = t || r.direction[e >= r.index ? "next" : "prev"],
                        S.router = n || "jumpto",
                        r.loop && (e < 0 && (e = r.group.length + e % r.group.length),
                            e %= r.group.length),
                        r.group[e] !== d && (S.cancel(),
                            S._start(e)))
                },
                reposition: function(e, t) {
                    var n, r = S.current,
                        i = r ? r.wrap : null;
                    i && (n = S._getPosition(t),
                        e && "scroll" === e.type ? (delete n.position,
                            i.stop(!0, !0).animate(n, 200)) : (i.css(n),
                            r.pos = q.extend({}, r.dim, n)))
                },
                update: function(t) {
                    var n = t && t.type,
                        r = !n || "orientationchange" === n;
                    r && (clearTimeout(s),
                            s = null),
                        S.isOpen && !s && (s = setTimeout(function() {
                            var e = S.current;
                            e && !S.isClosing && (S.wrap.removeClass("fancybox-tmp"),
                                (r || "load" === n || "resize" === n && e.autoResize) && S._setDimension(),
                                "scroll" === n && e.canShrink || S.reposition(t),
                                S.trigger("onUpdate"),
                                s = null)
                        }, r && !l ? 0 : 300))
                },
                toggle: function(e) {
                    S.isOpen && (S.current.fitToView = "boolean" === q.type(e) ? e : !S.current.fitToView,
                        l && (S.wrap.removeAttr("style").addClass("fancybox-tmp"),
                            S.trigger("onUpdate")),
                        S.update())
                },
                hideLoading: function() {
                    c.unbind(".loading"),
                        q("#fancybox-loading").remove()
                },
                showLoading: function() {
                    var e, t;
                    S.hideLoading(),
                        e = q('<div id="fancybox-loading"><div></div></div>').click(S.cancel).appendTo("body"),
                        c.bind("keydown.loading", function(e) {
                            27 === (e.which || e.keyCode) && (e.preventDefault(),
                                S.cancel())
                        }),
                        S.defaults.fixed || (t = S.getViewport(),
                            e.css({
                                position: "absolute",
                                top: .5 * t.h + t.y,
                                left: .5 * t.w + t.x
                            }))
                },
                getViewport: function() {
                    var e = S.current && S.current.locked || !1,
                        t = {
                            x: a.scrollLeft(),
                            y: a.scrollTop()
                        };
                    return e ? (t.w = e[0].clientWidth,
                            t.h = e[0].clientHeight) : (t.w = l && r.innerWidth ? r.innerWidth : a.width(),
                            t.h = l && r.innerHeight ? r.innerHeight : a.height()),
                        t
                },
                unbindEvents: function() {
                    S.wrap && u(S.wrap) && S.wrap.unbind(".fb"),
                        c.unbind(".fb"),
                        a.unbind(".fb")
                },
                bindEvents: function() {
                    var t, o = S.current;
                    o && (a.bind("orientationchange.fb" + (l ? "" : " resize.fb") + (o.autoCenter && !o.locked ? " scroll.fb" : ""), S.update),
                        (t = o.keys) && c.bind("keydown.fb", function(n) {
                            var r = n.which || n.keyCode,
                                e = n.target || n.srcElement;
                            if (27 === r && S.coming)
                                return !1;
                            !n.ctrlKey && !n.altKey && !n.shiftKey && !n.metaKey && (!e || !e.type && !q(e).is("[contenteditable]")) && q.each(t, function(e, t) {
                                return 1 < o.group.length && t[r] !== d ? (S[e](t[r]),
                                    n.preventDefault(),
                                    !1) : -1 < q.inArray(r, t) ? (S[e](),
                                    n.preventDefault(),
                                    !1) : void 0
                            })
                        }),
                        q.fn.mousewheel && o.mouseWheel && S.wrap.bind("mousewheel.fb", function(e, t, n, r) {
                            for (var i = q(e.target || null), a = !1; i.length && !a && !i.is(".fancybox-skin") && !i.is(".fancybox-wrap");)
                                a = i[0] && !(i[0].style.overflow && "hidden" === i[0].style.overflow) && (i[0].clientWidth && i[0].scrollWidth > i[0].clientWidth || i[0].clientHeight && i[0].scrollHeight > i[0].clientHeight),
                                i = q(i).parent();
                            0 !== t && !a && 1 < S.group.length && !o.canShrink && (0 < r || 0 < n ? S.prev(0 < r ? "down" : "left") : (r < 0 || n < 0) && S.next(r < 0 ? "up" : "right"),
                                e.preventDefault())
                        }))
                },
                trigger: function(n, e) {
                    var t, r = e || S.coming || S.current;
                    if (r) {
                        if (q.isFunction(r[n]) && (t = r[n].apply(r, Array.prototype.slice.call(arguments, 1))),
                            !1 === t)
                            return !1;
                        r.helpers && q.each(r.helpers, function(e, t) {
                                t && S.helpers[e] && q.isFunction(S.helpers[e][n]) && S.helpers[e][n](q.extend(!0, {}, S.helpers[e].defaults, t), r)
                            }),
                            c.trigger(n)
                    }
                },
                isImage: function(e) {
                    return p(e) && e.match(/(^data:image\/.*,)|(\.(jp(e|g|eg)|gif|png|bmp|webp|svg)((\?|#).*)?$)/i)
                },
                isSWF: function(e) {
                    return p(e) && e.match(/\.(swf)((\?|#).*)?$/i)
                },
                _start: function(e) {
                    var t, n, r = {};
                    if (e = P(e),
                        !(t = S.group[e] || null))
                        return !1;
                    if (t = (r = q.extend(!0, {}, S.opts, t)).margin,
                        n = r.padding,
                        "number" === q.type(t) && (r.margin = [t, t, t, t]),
                        "number" === q.type(n) && (r.padding = [n, n, n, n]),
                        r.modal && q.extend(!0, r, {
                            closeBtn: !1,
                            closeClick: !1,
                            nextClick: !1,
                            arrows: !1,
                            mouseWheel: !1,
                            keys: null,
                            helpers: {
                                overlay: {
                                    closeClick: !1
                                }
                            }
                        }),
                        r.autoSize && (r.autoWidth = r.autoHeight = !0),
                        "auto" === r.width && (r.autoWidth = !0),
                        "auto" === r.height && (r.autoHeight = !0),
                        r.group = S.group,
                        r.index = e,
                        S.coming = r,
                        !1 === S.trigger("beforeLoad"))
                        S.coming = null;
                    else {
                        if (n = r.type,
                            t = r.href,
                            !n)
                            return S.coming = null,
                                !(!S.current || !S.router || "jumpto" === S.router) && (S.current.index = e,
                                    S[S.router](S.direction));
                        if (S.isActive = !0,
                            "image" !== n && "swf" !== n || (r.autoHeight = r.autoWidth = !1,
                                r.scrolling = "visible"),
                            "image" === n && (r.aspectRatio = !0),
                            "iframe" === n && l && (r.scrolling = "scroll"),
                            r.wrap = q(r.tpl.wrap).addClass("fancybox-" + (l ? "mobile" : "desktop") + " fancybox-type-" + n + " fancybox-tmp " + r.wrapCSS).appendTo(r.parent || "body"),
                            q.extend(r, {
                                skin: q(".fancybox-skin", r.wrap),
                                outer: q(".fancybox-outer", r.wrap),
                                inner: q(".fancybox-inner", r.wrap)
                            }),
                            q.each(["Top", "Right", "Bottom", "Left"], function(e, t) {
                                r.skin.css("padding" + t, j(r.padding[e]))
                            }),
                            S.trigger("onReady"),
                            "inline" === n || "html" === n) {
                            if (!r.content || !r.content.length)
                                return S._error("content")
                        } else if (!t)
                            return S._error("href");
                        "image" === n ? S._loadImage() : "ajax" === n ? S._loadAjax() : "iframe" === n ? S._loadIframe() : S._afterLoad()
                    }
                },
                _error: function(e) {
                    q.extend(S.coming, {
                            type: "html",
                            autoWidth: !0,
                            autoHeight: !0,
                            minWidth: 0,
                            minHeight: 0,
                            scrolling: "no",
                            hasError: e,
                            content: S.coming.tpl.error
                        }),
                        S._afterLoad()
                },
                _loadImage: function() {
                    var e = S.imgPreload = new Image;
                    e.onload = function() {
                            this.onload = this.onerror = null,
                                S.coming.width = this.width / S.opts.pixelRatio,
                                S.coming.height = this.height / S.opts.pixelRatio,
                                S._afterLoad()
                        },
                        e.onerror = function() {
                            this.onload = this.onerror = null,
                                S._error("image")
                        },
                        e.src = S.coming.href,
                        !0 !== e.complete && S.showLoading()
                },
                _loadAjax: function() {
                    var n = S.coming;
                    S.showLoading(),
                        S.ajaxLoad = q.ajax(q.extend({}, n.ajax, {
                            url: n.href,
                            error: function(e, t) {
                                S.coming && "abort" !== t ? S._error("ajax", e) : S.hideLoading()
                            },
                            success: function(e, t) {
                                "success" === t && (n.content = e,
                                    S._afterLoad())
                            }
                        }))
                },
                _loadIframe: function() {
                    var e = S.coming,
                        t = q(e.tpl.iframe.replace(/\{rnd\}/g, (new Date).getTime())).attr("scrolling", l ? "auto" : e.iframe.scrolling).attr("src", e.href);
                    q(e.wrap).bind("onReset", function() {
                            try {
                                q(this).find("iframe").hide().attr("src", "//about:blank").end().empty()
                            } catch (e) {}
                        }),
                        e.iframe.preload && (S.showLoading(),
                            t.one("load", function() {
                                q(this).data("ready", 1),
                                    l || q(this).bind("load.fb", S.update),
                                    q(this).parents(".fancybox-wrap").width("100%").removeClass("fancybox-tmp").show(),
                                    S._afterLoad()
                            })),
                        e.content = t.appendTo(e.inner),
                        e.iframe.preload || S._afterLoad()
                },
                _preloadImages: function() {
                    var e, t, n = S.group,
                        r = S.current,
                        i = n.length,
                        a = r.preload ? Math.min(r.preload, i - 1) : 0;
                    for (t = 1; t <= a; t += 1)
                        "image" === (e = n[(r.index + t) % i]).type && e.href && ((new Image).src = e.href)
                },
                _afterLoad: function() {
                    var n, e, t, r, i, a = S.coming,
                        o = S.current;
                    if (S.hideLoading(),
                        a && !1 !== S.isActive)
                        if (!1 === S.trigger("afterLoad", a, o))
                            a.wrap.stop(!0).trigger("onReset").remove(),
                            S.coming = null;
                        else {
                            switch (o && (S.trigger("beforeChange", o),
                                    o.wrap.stop(!0).removeClass("fancybox-opened").find(".fancybox-item, .fancybox-nav").remove()),
                                S.unbindEvents(),
                                n = a.content,
                                e = a.type,
                                t = a.scrolling,
                                q.extend(S, {
                                    wrap: a.wrap,
                                    skin: a.skin,
                                    outer: a.outer,
                                    inner: a.inner,
                                    current: a,
                                    previous: o
                                }),
                                r = a.href,
                                e) {
                                case "inline":
                                case "ajax":
                                case "html":
                                    a.selector ? n = q("<div>").html(n).find(a.selector) : u(n) && (n.data("fancybox-placeholder") || n.data("fancybox-placeholder", q('<div class="fancybox-placeholder"></div>').insertAfter(n).hide()),
                                        n = n.show().detach(),
                                        a.wrap.bind("onReset", function() {
                                            q(this).find(n).length && n.hide().replaceAll(n.data("fancybox-placeholder")).data("fancybox-placeholder", !1)
                                        }));
                                    break;
                                case "image":
                                    n = a.tpl.image.replace("{href}", r);
                                    break;
                                case "swf":
                                    n = '<object id="fancybox-swf" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%"><param name="movie" value="' + r + '"></param>',
                                        i = "",
                                        q.each(a.swf, function(e, t) {
                                            n += '<param name="' + e + '" value="' + t + '"></param>',
                                                i += " " + e + '="' + t + '"'
                                        }),
                                        n += '<embed src="' + r + '" type="application/x-shockwave-flash" width="100%" height="100%"' + i + "></embed></object>"
                            }
                            (!u(n) || !n.parent().is(a.inner)) && a.inner.append(n),
                                S.trigger("beforeShow"),
                                a.inner.css("overflow", "yes" === t ? "scroll" : "no" === t ? "hidden" : t),
                                S._setDimension(),
                                S.reposition(),
                                S.isOpen = !1,
                                S.coming = null,
                                S.bindEvents(),
                                S.isOpened ? o.prevMethod && S.transitions[o.prevMethod]() : q(".fancybox-wrap").not(a.wrap).stop(!0).trigger("onReset").remove(),
                                S.transitions[S.isOpened ? a.nextMethod : a.openMethod](),
                                S._preloadImages()
                        }
                },
                _setDimension: function() {
                    var e, t, n, r, i, a, o, s, l, c = S.getViewport(),
                        d = 0,
                        u = !1,
                        p = !1,
                        m = (u = S.wrap,
                            S.skin),
                        h = S.inner,
                        f = S.current,
                        g = (p = f.width,
                            f.height),
                        v = f.minWidth,
                        _ = f.minHeight,
                        y = f.maxWidth,
                        b = f.maxHeight,
                        w = f.scrolling,
                        k = f.scrollOutside ? f.scrollbarWidth : 0,
                        x = f.margin,
                        $ = P(x[1] + x[3]),
                        C = P(x[0] + x[2]);
                    if (u.add(m).add(h).width("auto").height("auto").removeClass("fancybox-tmp"),
                        t = $ + (x = P(m.outerWidth(!0) - m.width())),
                        n = C + (e = P(m.outerHeight(!0) - m.height())),
                        r = T(p) ? (c.w - t) * P(p) / 100 : p,
                        i = T(g) ? (c.h - n) * P(g) / 100 : g,
                        "iframe" === f.type) {
                        if (l = f.content,
                            f.autoHeight && 1 === l.data("ready"))
                            try {
                                l[0].contentWindow.document.location && (h.width(r).height(9999),
                                    a = l.contents().find("body"),
                                    k && a.css("overflow-x", "hidden"),
                                    i = a.outerHeight(!0))
                            } catch (e) {}
                    } else
                        (f.autoWidth || f.autoHeight) && (h.addClass("fancybox-tmp"),
                            f.autoWidth || h.width(r),
                            f.autoHeight || h.height(i),
                            f.autoWidth && (r = h.width()),
                            f.autoHeight && (i = h.height()),
                            h.removeClass("fancybox-tmp"));
                    if (p = P(r),
                        g = P(i),
                        s = r / i,
                        v = P(T(v) ? P(v, "w") - t : v),
                        y = P(T(y) ? P(y, "w") - t : y),
                        _ = P(T(_) ? P(_, "h") - n : _),
                        a = y,
                        o = b = P(T(b) ? P(b, "h") - n : b),
                        f.fitToView && (y = Math.min(c.w - t, y),
                            b = Math.min(c.h - n, b)),
                        t = c.w - $,
                        C = c.h - C,
                        f.aspectRatio ? (y < p && (g = P((p = y) / s)),
                            b < g && (p = P((g = b) * s)),
                            p < v && (g = P((p = v) / s)),
                            g < _ && (p = P((g = _) * s))) : (p = Math.max(v, Math.min(p, y)),
                            f.autoHeight && "iframe" !== f.type && (h.width(p),
                                g = h.height()),
                            g = Math.max(_, Math.min(g, b))),
                        f.fitToView)
                        if (h.width(p).height(g),
                            u.width(p + x),
                            c = u.width(),
                            $ = u.height(),
                            f.aspectRatio)
                            for (;
                                (t < c || C < $) && v < p && _ < g && !(19 < d++);)
                                g = Math.max(_, Math.min(b, g - 10)),
                                (p = P(g * s)) < v && (g = P((p = v) / s)),
                                y < p && (g = P((p = y) / s)),
                                h.width(p).height(g),
                                u.width(p + x),
                                c = u.width(),
                                $ = u.height();
                        else
                            p = Math.max(v, Math.min(p, p - (c - t))),
                            g = Math.max(_, Math.min(g, g - ($ - C)));
                    k && "auto" === w && g < i && p + x + k < t && (p += k),
                        h.width(p).height(g),
                        u.width(p + x),
                        c = u.width(),
                        $ = u.height(),
                        u = (t < c || C < $) && v < p && _ < g,
                        p = f.aspectRatio ? p < a && g < o && p < r && g < i : (p < a || g < o) && (p < r || g < i),
                        q.extend(f, {
                            dim: {
                                width: j(c),
                                height: j($)
                            },
                            origWidth: r,
                            origHeight: i,
                            canShrink: u,
                            canExpand: p,
                            wPadding: x,
                            hPadding: e,
                            wrapSpace: $ - m.outerHeight(!0),
                            skinSpace: m.height() - g
                        }),
                        !l && f.autoHeight && _ < g && g < b && !p && h.height("auto")
                },
                _getPosition: function(e) {
                    var t = S.current,
                        n = S.getViewport(),
                        r = t.margin,
                        i = S.wrap.width() + r[1] + r[3],
                        a = S.wrap.height() + r[0] + r[2];
                    r = {
                        position: "absolute",
                        top: r[0],
                        left: r[3]
                    };
                    return t.autoCenter && t.fixed && !e && a <= n.h && i <= n.w ? r.position = "fixed" : t.locked || (r.top += n.y,
                            r.left += n.x),
                        r.top = j(Math.max(r.top, r.top + (n.h - a) * t.topRatio)),
                        r.left = j(Math.max(r.left, r.left + (n.w - i) * t.leftRatio)),
                        r
                },
                _afterZoomIn: function() {
                    var t = S.current;
                    t && (S.isOpen = S.isOpened = !0,
                        S.wrap.css("overflow", "visible").addClass("fancybox-opened"),
                        S.update(),
                        (t.closeClick || t.nextClick && 1 < S.group.length) && S.inner.css("cursor", "pointer").bind("click.fb", function(e) {
                            !q(e.target).is("a") && !q(e.target).parent().is("a") && (e.preventDefault(),
                                S[t.closeClick ? "close" : "next"]())
                        }),
                        t.closeBtn && q(t.tpl.closeBtn).appendTo(S.skin).bind("click.fb", function(e) {
                            e.preventDefault(),
                                S.close()
                        }),
                        t.arrows && 1 < S.group.length && ((t.loop || 0 < t.index) && q(t.tpl.prev).appendTo(S.outer).bind("click.fb", S.prev),
                            (t.loop || t.index < S.group.length - 1) && q(t.tpl.next).appendTo(S.outer).bind("click.fb", S.next)),
                        S.trigger("afterShow"),
                        t.loop || t.index !== t.group.length - 1 ? S.opts.autoPlay && !S.player.isActive && (S.opts.autoPlay = !1,
                            S.play()) : S.play(!1))
                },
                _afterZoomOut: function(e) {
                    e = e || S.current,
                        q(".fancybox-wrap").trigger("onReset").remove(),
                        q.extend(S, {
                            group: {},
                            opts: {},
                            router: !1,
                            current: null,
                            isActive: !1,
                            isOpened: !1,
                            isOpen: !1,
                            isClosing: !1,
                            wrap: null,
                            skin: null,
                            outer: null,
                            inner: null
                        }),
                        S.trigger("afterClose", e)
                }
            }),
            S.transitions = {
                getOrigPosition: function() {
                    var e = S.current,
                        t = e.element,
                        n = e.orig,
                        r = {},
                        i = 50,
                        a = 50,
                        o = e.hPadding,
                        s = e.wPadding,
                        l = S.getViewport();
                    return !n && e.isDom && t.is(":visible") && ((n = t.find("img:first")).length || (n = t)),
                        u(n) ? (r = n.offset(),
                            n.is("img") && (i = n.outerWidth(),
                                a = n.outerHeight())) : (r.top = l.y + (l.h - a) * e.topRatio,
                            r.left = l.x + (l.w - i) * e.leftRatio),
                        ("fixed" === S.wrap.css("position") || e.locked) && (r.top -= l.y,
                            r.left -= l.x), {
                            top: j(r.top - o * e.topRatio),
                            left: j(r.left - s * e.leftRatio),
                            width: j(i + s),
                            height: j(a + o)
                        }
                },
                step: function(e, t) {
                    var n, r, i = t.prop,
                        a = (r = S.current).wrapSpace,
                        o = r.skinSpace;
                    "width" !== i && "height" !== i || (n = t.end === t.start ? 1 : (e - t.start) / (t.end - t.start),
                        S.isClosing && (n = 1 - n),
                        r = e - (r = "width" === i ? r.wPadding : r.hPadding),
                        S.skin[i](P("width" === i ? r : r - a * n)),
                        S.inner[i](P("width" === i ? r : r - a * n - o * n)))
                },
                zoomIn: function() {
                    var e = S.current,
                        t = e.pos,
                        n = e.openEffect,
                        r = "elastic" === n,
                        i = q.extend({
                            opacity: 1
                        }, t);
                    delete i.position,
                        r ? (t = this.getOrigPosition(),
                            e.openOpacity && (t.opacity = .1)) : "fade" === n && (t.opacity = .1),
                        S.wrap.css(t).animate(i, {
                            duration: "none" === n ? 0 : e.openSpeed,
                            easing: e.openEasing,
                            step: r ? this.step : null,
                            complete: S._afterZoomIn
                        })
                },
                zoomOut: function() {
                    var e = S.current,
                        t = e.closeEffect,
                        n = "elastic" === t,
                        r = {
                            opacity: .1
                        };
                    n && (r = this.getOrigPosition(),
                            e.closeOpacity && (r.opacity = .1)),
                        S.wrap.animate(r, {
                            duration: "none" === t ? 0 : e.closeSpeed,
                            easing: e.closeEasing,
                            step: n ? this.step : null,
                            complete: S._afterZoomOut
                        })
                },
                changeIn: function() {
                    var e, t = S.current,
                        n = t.nextEffect,
                        r = t.pos,
                        i = {
                            opacity: 1
                        },
                        a = S.direction;
                    r.opacity = .1,
                        "elastic" === n && (e = "down" === a || "up" === a ? "top" : "left",
                            "down" === a || "right" === a ? (r[e] = j(P(r[e]) - 200),
                                i[e] = "+=200px") : (r[e] = j(P(r[e]) + 200),
                                i[e] = "-=200px")),
                        "none" === n ? S._afterZoomIn() : S.wrap.css(r).animate(i, {
                            duration: t.nextSpeed,
                            easing: t.nextEasing,
                            complete: S._afterZoomIn
                        })
                },
                changeOut: function() {
                    var e = S.previous,
                        t = e.prevEffect,
                        n = {
                            opacity: .1
                        },
                        r = S.direction;
                    "elastic" === t && (n["down" === r || "up" === r ? "top" : "left"] = ("up" === r || "left" === r ? "-" : "+") + "=200px"),
                        e.wrap.animate(n, {
                            duration: "none" === t ? 0 : e.prevSpeed,
                            easing: e.prevEasing,
                            complete: function() {
                                q(this).trigger("onReset").remove()
                            }
                        })
                }
            },
            S.helpers.overlay = {
                defaults: {
                    closeClick: !0,
                    speedOut: 200,
                    showEarly: !0,
                    css: {},
                    locked: !l,
                    fixed: !0
                },
                overlay: null,
                fixed: !1,
                el: q("html"),
                create: function(e) {
                    e = q.extend({}, this.defaults, e),
                        this.overlay && this.close(),
                        this.overlay = q('<div class="fancybox-overlay"></div>').appendTo(S.coming ? S.coming.parent : e.parent),
                        this.fixed = !1,
                        e.fixed && S.defaults.fixed && (this.overlay.addClass("fancybox-overlay-fixed"),
                            this.fixed = !0)
                },
                open: function(e) {
                    var t = this;
                    e = q.extend({}, this.defaults, e),
                        this.overlay ? this.overlay.unbind(".overlay").width("auto").height("auto") : this.create(e),
                        this.fixed || (a.bind("resize.overlay", q.proxy(this.update, this)),
                            this.update()),
                        e.closeClick && this.overlay.bind("click.overlay", function(e) {
                            if (q(e.target).hasClass("fancybox-overlay"))
                                return S.isActive ? S.close() : t.close(),
                                    !1
                        }),
                        this.overlay.css(e.css).show()
                },
                close: function() {
                    var e, t;
                    a.unbind("resize.overlay"),
                        this.el.hasClass("fancybox-lock") && (q(".fancybox-margin").removeClass("fancybox-margin"),
                            e = a.scrollTop(),
                            t = a.scrollLeft(),
                            this.el.removeClass("fancybox-lock"),
                            a.scrollTop(e).scrollLeft(t)),
                        q(".fancybox-overlay").remove().hide(),
                        q.extend(this, {
                            overlay: null,
                            fixed: !1
                        })
                },
                update: function() {
                    var e, t = "100%";
                    this.overlay.width(t).height("100%"),
                        o ? (e = Math.max(n.documentElement.offsetWidth, n.body.offsetWidth),
                            c.width() > e && (t = c.width())) : c.width() > a.width() && (t = c.width()),
                        this.overlay.width(t).height(c.height())
                },
                onReady: function(e, t) {
                    var n = this.overlay;
                    q(".fancybox-overlay").stop(!0, !0),
                        n || this.create(e),
                        e.locked && this.fixed && t.fixed && (n || (this.margin = c.height() > a.height() && q("html").css("margin-right").replace("px", "")),
                            t.locked = this.overlay.append(t.wrap),
                            t.fixed = !1),
                        !0 === e.showEarly && this.beforeShow.apply(this, arguments)
                },
                beforeShow: function(e, t) {
                    var n, r;
                    t.locked && (!1 !== this.margin && (q("*").filter(function() {
                                    return "fixed" === q(this).css("position") && !q(this).hasClass("fancybox-overlay") && !q(this).hasClass("fancybox-wrap")
                                }).addClass("fancybox-margin"),
                                this.el.addClass("fancybox-margin")),
                            n = a.scrollTop(),
                            r = a.scrollLeft(),
                            this.el.addClass("fancybox-lock"),
                            a.scrollTop(n).scrollLeft(r)),
                        this.open(e)
                },
                onUpdate: function() {
                    this.fixed || this.update()
                },
                afterClose: function(e) {
                    this.overlay && !S.coming && this.overlay.fadeOut(e.speedOut, q.proxy(this.close, this))
                }
            },
            S.helpers.title = {
                defaults: {
                    type: "float",
                    position: "bottom"
                },
                beforeShow: function(e) {
                    var t = S.current,
                        n = t.title,
                        r = e.type;
                    if (q.isFunction(n) && (n = n.call(t.element, t)),
                        p(n) && "" !== q.trim(n)) {
                        switch (t = q('<div class="fancybox-title fancybox-title-' + r + '-wrap">' + n + "</div>"),
                            r) {
                            case "inside":
                                r = S.skin;
                                break;
                            case "outside":
                                r = S.wrap;
                                break;
                            case "over":
                                r = S.inner;
                                break;
                            default:
                                r = S.skin,
                                    t.appendTo("body"),
                                    o && t.width(t.width()),
                                    t.wrapInner('<span class="child"></span>'),
                                    S.current.margin[2] += Math.abs(P(t.css("margin-bottom")))
                        }
                        t["top" === e.position ? "prependTo" : "appendTo"](r)
                    }
                }
            },
            q.fn.fancybox = function(a) {
                var o, s = q(this),
                    l = this.selector || "",
                    e = function(e) {
                        var t, n, r = q(this).blur(),
                            i = o;
                        !e.ctrlKey && !e.altKey && !e.shiftKey && !e.metaKey && !r.is(".fancybox-wrap") && (t = a.groupAttr || "data-fancybox-group",
                            (n = r.attr(t)) || (t = "rel",
                                n = r.get(0)[t]),
                            n && "" !== n && "nofollow" !== n && (i = (r = (r = l.length ? q(l) : s).filter("[" + t + '="' + n + '"]')).index(this)),
                            a.index = i,
                            !1 !== S.open(r, a) && e.preventDefault())
                    };
                return o = (a = a || {}).index || 0,
                    l && !1 !== a.live ? c.undelegate(l, "click.fb-start").delegate(l + ":not('.fancybox-item, .fancybox-nav')", "click.fb-start", e) : s.unbind("click.fb-start").bind("click.fb-start", e),
                    this.filter("[data-fancybox-start=1]").trigger("click"),
                    this
            },
            c.ready(function() {
                var e, t;
                if (q.scrollbarWidth === d && (q.scrollbarWidth = function() {
                        var e = q('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo("body"),
                            t = (t = e.children()).innerWidth() - t.height(99).innerWidth();
                        return e.remove(),
                            t
                    }),
                    q.support.fixedPosition === d) {
                    e = q.support;
                    var n = 20 === (t = q('<div style="position:fixed;top:20px;"></div>').appendTo("body"))[0].offsetTop || 15 === t[0].offsetTop;
                    t.remove(),
                        e.fixedPosition = n
                }
                q.extend(S.defaults, {
                        scrollbarWidth: q.scrollbarWidth(),
                        fixed: q.support.fixedPosition,
                        parent: q("body")
                    }),
                    e = q(r).width(),
                    i.addClass("fancybox-lock-test"),
                    t = q(r).width(),
                    i.removeClass("fancybox-lock-test"),
                    q("<style type='text/css'>.fancybox-margin{margin-right:" + (t - e) + "px;}</style>").appendTo("head")
            })
    }(window, document, jQuery),
    function() {
        var l, c, i, d, e, t, n, r, a, o, s, u, p, m, h, f, g, v, _, y, b, w, k, x, $ = [].slice,
            C = [].indexOf || function(e) {
                for (var t = 0, n = this.length; t < n; t++)
                    if (t in this && this[t] === e)
                        return t;
                return -1
            };
        (l = window.jQuery || window.Zepto || window.$).payment = {},
            l.payment.fn = {},
            l.fn.payment = function() {
                var e, t;
                return t = arguments[0],
                    e = 2 <= arguments.length ? $.call(arguments, 1) : [],
                    l.payment.fn[t].apply(this, e)
            },
            e = /(\d{1,4})/g,
            l.payment.cards = d = [{
                type: "visaelectron",
                patterns: [4026, 417500, 4405, 4508, 4844, 4913, 4917],
                format: e,
                length: [16],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "maestro",
                patterns: [5018, 502, 503, 56, 58, 639, 6220, 67],
                format: e,
                length: [12, 13, 14, 15, 16, 17, 18, 19],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "forbrugsforeningen",
                patterns: [600],
                format: e,
                length: [16],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "dankort",
                patterns: [5019],
                format: e,
                length: [16],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "visa",
                patterns: [4],
                format: e,
                length: [13, 16],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "mastercard",
                patterns: [51, 52, 53, 54, 55, 22, 23, 24, 25, 26, 27],
                format: e,
                length: [16],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "amex",
                patterns: [34, 37],
                format: /(\d{1,4})(\d{1,6})?(\d{1,5})?/,
                length: [15],
                cvcLength: [3, 4],
                luhn: !0
            }, {
                type: "dinersclub",
                patterns: [30, 36, 38, 39],
                format: /(\d{1,4})(\d{1,6})?(\d{1,4})?/,
                length: [14],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "discover",
                patterns: [60, 64, 65, 622],
                format: e,
                length: [16],
                cvcLength: [3],
                luhn: !0
            }, {
                type: "unionpay",
                patterns: [62, 88],
                format: e,
                length: [16, 17, 18, 19],
                cvcLength: [3],
                luhn: !1
            }, {
                type: "jcb",
                patterns: [35],
                format: e,
                length: [16],
                cvcLength: [3],
                luhn: !0
            }],
            c = function(e) {
                var t, n, r, i, a, o, s;
                for (e = (e + "").replace(/\D/g, ""),
                    r = 0,
                    a = d.length; r < a; r++)
                    for (i = 0,
                        o = (s = (t = d[r]).patterns).length; i < o; i++)
                        if (n = s[i] + "",
                            e.substr(0, n.length) === n)
                            return t
            },
            i = function(e) {
                var t, n, r;
                for (n = 0,
                    r = d.length; n < r; n++)
                    if ((t = d[n]).type === e)
                        return t
            },
            p = function(e) {
                var t, n, r, i, a, o;
                for (r = !0,
                    a = i = 0,
                    o = (n = (e + "").split("").reverse()).length; a < o; a++)
                    t = n[a],
                    t = parseInt(t, 10),
                    (r = !r) && (t *= 2),
                    9 < t && (t -= 9),
                    i += t;
                return i % 10 == 0
            },
            u = function(e) {
                var t;
                return null != e.prop("selectionStart") && e.prop("selectionStart") !== e.prop("selectionEnd") || !(null == ("undefined" != typeof document && null !== document && null != (t = document.selection) ? t.createRange : void 0) || !document.selection.createRange().text)
            },
            k = function(e, t) {
                var n, r;
                try {
                    n = t.prop("selectionStart")
                } catch (e) {
                    e,
                    n = null
                }
                return r = t.val(),
                    t.val(e),
                    null !== n && t.is(":focus") ? (n === r.length && (n = e.length),
                        t.prop("selectionStart", n),
                        t.prop("selectionEnd", n)) : void 0
            },
            v = function(e) {
                var t, n, r, i, a, o;
                for (null == e && (e = ""),
                    "０１２３４５６７８９",
                    "0123456789",
                    i = "",
                    a = 0,
                    o = (t = e.split("")).length; a < o; a++)
                    n = t[a],
                    -1 < (r = "０１２３４５６７８９".indexOf(n)) && (n = "0123456789" [r]),
                    i += n;
                return i
            },
            g = function(n) {
                return setTimeout(function() {
                    var e, t;
                    return t = (e = l(n.currentTarget)).val(),
                        t = (t = v(t)).replace(/\D/g, ""),
                        k(t, e)
                })
            },
            h = function(n) {
                return setTimeout(function() {
                    var e, t;
                    return t = (e = l(n.currentTarget)).val(),
                        t = v(t),
                        t = l.payment.formatCardNumber(t),
                        k(t, e)
                })
            },
            r = function(e) {
                var t, n, r, i, a, o, s;
                return r = String.fromCharCode(e.which),
                    !/^\d+$/.test(r) || (t = l(e.currentTarget),
                        s = t.val(),
                        n = c(s + r),
                        i = (s.replace(/\D/g, "") + r).length,
                        o = 16,
                        n && (o = n.length[n.length.length - 1]),
                        o <= i || null != t.prop("selectionStart") && t.prop("selectionStart") !== s.length) ? void 0 : (a = n && "amex" === n.type ? /^(\d{4}|\d{4}\s\d{6})$/ : /(?:^|\s)(\d{4})$/).test(s) ? (e.preventDefault(),
                        setTimeout(function() {
                            return t.val(s + " " + r)
                        })) : a.test(s + r) ? (e.preventDefault(),
                        setTimeout(function() {
                            return t.val(s + r + " ")
                        })) : void 0
            },
            t = function(e) {
                var t, n;
                return t = l(e.currentTarget),
                    n = t.val(),
                    8 !== e.which || null != t.prop("selectionStart") && t.prop("selectionStart") !== n.length ? void 0 : /\d\s$/.test(n) ? (e.preventDefault(),
                        setTimeout(function() {
                            return t.val(n.replace(/\d\s$/, ""))
                        })) : /\s\d?$/.test(n) ? (e.preventDefault(),
                        setTimeout(function() {
                            return t.val(n.replace(/\d$/, ""))
                        })) : void 0
            },
            f = function(n) {
                return setTimeout(function() {
                    var e, t;
                    return t = (e = l(n.currentTarget)).val(),
                        t = v(t),
                        t = l.payment.formatExpiry(t),
                        k(t, e)
                })
            },
            a = function(e) {
                var n, t, r;
                return t = String.fromCharCode(e.which),
                    /^\d+$/.test(t) ? (n = l(e.currentTarget),
                        r = n.val() + t,
                        /^\d$/.test(r) && "0" !== r && "1" !== r ? (e.preventDefault(),
                            setTimeout(function() {
                                return n.val("0" + r + " / ")
                            })) : /^\d\d$/.test(r) ? (e.preventDefault(),
                            setTimeout(function() {
                                var e, t;
                                return e = parseInt(r[0], 10),
                                    2 < (t = parseInt(r[1], 10)) && 0 !== e ? n.val("0" + e + " / " + t) : n.val(r + " / ")
                            })) : void 0) : void 0
            },
            o = function(e) {
                var t, n, r;
                return n = String.fromCharCode(e.which),
                    /^\d+$/.test(n) ? (r = (t = l(e.currentTarget)).val(),
                        /^\d\d$/.test(r) ? t.val(r + " / ") : void 0) : void 0
            },
            s = function(e) {
                var t, n, r;
                return "/" === (r = String.fromCharCode(e.which)) || " " === r ? (n = (t = l(e.currentTarget)).val(),
                    /^\d$/.test(n) && "0" !== n ? t.val("0" + n + " / ") : void 0) : void 0
            },
            n = function(e) {
                var t, n;
                return t = l(e.currentTarget),
                    n = t.val(),
                    8 !== e.which || null != t.prop("selectionStart") && t.prop("selectionStart") !== n.length ? void 0 : /\d\s\/\s$/.test(n) ? (e.preventDefault(),
                        setTimeout(function() {
                            return t.val(n.replace(/\d\s\/\s$/, ""))
                        })) : void 0
            },
            m = function(n) {
                return setTimeout(function() {
                    var e, t;
                    return t = (e = l(n.currentTarget)).val(),
                        t = (t = v(t)).replace(/\D/g, "").slice(0, 4),
                        k(t, e)
                })
            },
            w = function(e) {
                var t;
                return !(!e.metaKey && !e.ctrlKey) || 32 !== e.which && (0 === e.which || (e.which < 33 || (t = String.fromCharCode(e.which),
                    !!/[\d\s]/.test(t))))
            },
            y = function(e) {
                var t, n, r, i;
                return t = l(e.currentTarget),
                    r = String.fromCharCode(e.which),
                    /^\d+$/.test(r) && !u(t) ? (i = (t.val() + r).replace(/\D/g, ""),
                        (n = c(i)) ? i.length <= n.length[n.length.length - 1] : i.length <= 16) : void 0
            },
            b = function(e) {
                var t, n;
                return t = l(e.currentTarget),
                    n = String.fromCharCode(e.which),
                    /^\d+$/.test(n) && !u(t) ? !(6 < (t.val() + n).replace(/\D/g, "").length) && void 0 : void 0
            },
            _ = function(e) {
                var t, n;
                return t = l(e.currentTarget),
                    n = String.fromCharCode(e.which),
                    /^\d+$/.test(n) && !u(t) ? (t.val() + n).length <= 4 : void 0
            },
            x = function(e) {
                var t, n, r, i, a;
                return a = (t = l(e.currentTarget)).val(),
                    i = l.payment.cardType(a) || "unknown",
                    t.hasClass(i) ? void 0 : (n = function() {
                            var e, t, n;
                            for (n = [],
                                e = 0,
                                t = d.length; e < t; e++)
                                r = d[e],
                                n.push(r.type);
                            return n
                        }(),
                        t.removeClass("unknown"),
                        t.removeClass(n.join(" ")),
                        t.addClass(i),
                        t.toggleClass("identified", "unknown" !== i),
                        t.trigger("payment.cardType", i))
            },
            l.payment.fn.formatCardCVC = function() {
                return this.on("keypress", w),
                    this.on("keypress", _),
                    this.on("paste", m),
                    this.on("change", m),
                    this.on("input", m),
                    this
            },
            l.payment.fn.formatCardExpiry = function() {
                return this.on("keypress", w),
                    this.on("keypress", b),
                    this.on("keypress", a),
                    this.on("keypress", s),
                    this.on("keypress", o),
                    this.on("keydown", n),
                    this.on("change", f),
                    this.on("input", f),
                    this
            },
            l.payment.fn.formatCardNumber = function() {
                return this.on("keypress", w),
                    this.on("keypress", y),
                    this.on("keypress", r),
                    this.on("keydown", t),
                    this.on("keyup", x),
                    this.on("paste", h),
                    this.on("change", h),
                    this.on("input", h),
                    this.on("input", x),
                    this
            },
            l.payment.fn.restrictNumeric = function() {
                return this.on("keypress", w),
                    this.on("paste", g),
                    this.on("change", g),
                    this.on("input", g),
                    this
            },
            l.payment.fn.cardExpiryVal = function() {
                return l.payment.cardExpiryVal(l(this).val())
            },
            l.payment.cardExpiryVal = function(e) {
                var t, n, r;
                return t = (r = e.split(/[\s\/]+/, 2))[0],
                    2 === (null != (n = r[1]) ? n.length : void 0) && /^\d+$/.test(n) && (n = (new Date).getFullYear().toString().slice(0, 2) + n), {
                        month: t = parseInt(t, 10),
                        year: n = parseInt(n, 10)
                    }
            },
            l.payment.validateCardNumber = function(e) {
                var t, n;
                return e = (e + "").replace(/\s+|-/g, ""),
                    !!/^\d+$/.test(e) && (!!(t = c(e)) && (n = e.length,
                        0 <= C.call(t.length, n) && (!1 === t.luhn || p(e))))
            },
            l.payment.validateCardExpiry = function(e, t) {
                var n, r, i;
                return "object" == typeof e && "month" in e && (e = (i = e).month,
                        t = i.year),
                    !(!e || !t) && (e = l.trim(e),
                        t = l.trim(t),
                        !!(/^\d+$/.test(e) && /^\d+$/.test(t) && 1 <= e && e <= 12) && (2 === t.length && (t = t < 70 ? "20" + t : "19" + t),
                            4 === t.length && (r = new Date(t, e),
                                n = new Date,
                                r.setMonth(r.getMonth() - 1),
                                r.setMonth(r.getMonth() + 1, 1),
                                n < r)))
            },
            l.payment.validateCardCVC = function(e, t) {
                var n, r;
                return e = l.trim(e),
                    !!/^\d+$/.test(e) && (null != (n = i(t)) ? (r = e.length,
                        0 <= C.call(n.cvcLength, r)) : 3 <= e.length && e.length <= 4)
            },
            l.payment.cardType = function(e) {
                var t;
                return e && (null != (t = c(e)) ? t.type : void 0) || null
            },
            l.payment.formatCardNumber = function(e) {
                var t, n, r, i;
                return e = e.replace(/\D/g, ""),
                    (t = c(e)) ? (r = t.length[t.length.length - 1],
                        e = e.slice(0, r),
                        t.format.global ? null != (i = e.match(t.format)) ? i.join(" - ") : void 0 : null != (n = t.format.exec(e)) ? (n.shift(),
                            (n = l.grep(n, function(e) {
                                return e
                            })).join(" - ")) : void 0) : e
            },
            l.payment.formatExpiry = function(e) {
                var t, n, r, i;
                return (n = e.match(/^\D*(\d{1,2})(\D+)?(\d{1,4})?/)) ? (t = n[1] || "",
                    r = n[2] || "",
                    0 < (i = n[3] || "").length ? r = " / " : " /" === r ? (t = t.substring(0, 1),
                        r = "") : 2 === t.length || 0 < r.length ? r = " / " : 1 === t.length && "0" !== t && "1" !== t && (t = "0" + t,
                        r = " / "),
                    t + r + i) : ""
            }
    }
    .call(this),
    function(e) {
        var t, r, n = function() {
            return t = (new(window.UAParser || exports.UAParser)).getResult(),
                r = new Detector,
                this
        };
        n.prototype = {
                getSoftwareVersion: function() {
                    return "0.1.11"
                },
                getBrowserData: function() {
                    return t
                },
                getFingerprint: function() {
                    return murmurhash3_32_gc(t.ua + "|" + this.getScreenPrint() + "|" + this.getPlugins() + "|" + this.getFonts() + "|" + this.isLocalStorage() + "|" + this.isSessionStorage() + "|" + this.getTimeZone() + "|" + this.getLanguage() + "|" + this.getSystemLanguage() + "|" + this.isCookie() + "|" + this.getCanvasPrint(), 256)
                },
                getCustomFingerprint: function() {
                    for (var e = "", t = 0; t < arguments.length; t++)
                        e += arguments[t] + "|";
                    return murmurhash3_32_gc(e, 256)
                },
                getUserAgent: function() {
                    return t.ua
                },
                getUserAgentLowerCase: function() {
                    return t.ua.toLowerCase()
                },
                getBrowser: function() {
                    return t.browser.name
                },
                getBrowserVersion: function() {
                    return t.browser.version
                },
                getBrowserMajorVersion: function() {
                    return t.browser.major
                },
                isIE: function() {
                    return /IE/i.test(t.browser.name)
                },
                isChrome: function() {
                    return /Chrome/i.test(t.browser.name)
                },
                isFirefox: function() {
                    return /Firefox/i.test(t.browser.name)
                },
                isSafari: function() {
                    return /Safari/i.test(t.browser.name)
                },
                isMobileSafari: function() {
                    return /Mobile\sSafari/i.test(t.browser.name)
                },
                isOpera: function() {
                    return /Opera/i.test(t.browser.name)
                },
                getEngine: function() {
                    return t.engine.name
                },
                getEngineVersion: function() {
                    return t.engine.version
                },
                getOS: function() {
                    return t.os.name
                },
                getOSVersion: function() {
                    return t.os.version
                },
                isWindows: function() {
                    return /Windows/i.test(t.os.name)
                },
                isMac: function() {
                    return /Mac/i.test(t.os.name)
                },
                isLinux: function() {
                    return /Linux/i.test(t.os.name)
                },
                isUbuntu: function() {
                    return /Ubuntu/i.test(t.os.name)
                },
                isSolaris: function() {
                    return /Solaris/i.test(t.os.name)
                },
                getDevice: function() {
                    return t.device.model
                },
                getDeviceType: function() {
                    return t.device.type
                },
                getDeviceVendor: function() {
                    return t.device.vendor
                },
                getCPU: function() {
                    return t.cpu.architecture
                },
                isMobile: function() {
                    var e = t.ua || navigator.vendor || window.opera;
                    return /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(e) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(e.substr(0, 4))
                },
                isMobileMajor: function() {
                    return this.isMobileAndroid() || this.isMobileBlackBerry() || this.isMobileIOS() || this.isMobileOpera() || this.isMobileWindows()
                },
                isMobileAndroid: function() {
                    return !!t.ua.match(/Android/i)
                },
                isMobileOpera: function() {
                    return !!t.ua.match(/Opera Mini/i)
                },
                isMobileWindows: function() {
                    return !!t.ua.match(/IEMobile/i)
                },
                isMobileBlackBerry: function() {
                    return !!t.ua.match(/BlackBerry/i)
                },
                isMobileIOS: function() {
                    return !!t.ua.match(/iPhone|iPad|iPod/i)
                },
                isIphone: function() {
                    return !!t.ua.match(/iPhone/i)
                },
                isIpad: function() {
                    return !!t.ua.match(/iPad/i)
                },
                isIpod: function() {
                    return !!t.ua.match(/iPod/i)
                },
                getScreenPrint: function() {
                    return "Current Resolution: " + this.getCurrentResolution() + ", Available Resolution: " + this.getAvailableResolution() + ", Color Depth: " + this.getColorDepth() + ", Device XDPI: " + this.getDeviceXDPI() + ", Device YDPI: " + this.getDeviceYDPI()
                },
                getColorDepth: function() {
                    return screen.colorDepth
                },
                getCurrentResolution: function() {
                    return screen.width + "x" + screen.height
                },
                getAvailableResolution: function() {
                    return screen.availWidth + "x" + screen.availHeight
                },
                getDeviceXDPI: function() {
                    return screen.deviceXDPI
                },
                getDeviceYDPI: function() {
                    return screen.deviceYDPI
                },
                getPlugins: function() {
                    for (var e = "", t = 0; t < navigator.plugins.length; t++)
                        e = t == navigator.plugins.length - 1 ? e + navigator.plugins[t].name : e + (navigator.plugins[t].name + ", ");
                    return e
                },
                isJava: function() {
                    return navigator.javaEnabled()
                },
                getJavaVersion: function() {
                    return deployJava.getJREs().toString()
                },
                isFlash: function() {
                    return !!navigator.plugins["Shockwave Flash"]
                },
                getFlashVersion: function() {
                    return this.isFlash() ? (objPlayerVersion = swfobject.getFlashPlayerVersion(),
                        objPlayerVersion.major + "." + objPlayerVersion.minor + "." + objPlayerVersion.release) : ""
                },
                isSilverlight: function() {
                    return !!navigator.plugins["Silverlight Plug-In"]
                },
                getSilverlightVersion: function() {
                    return this.isSilverlight() ? navigator.plugins["Silverlight Plug-In"].description : ""
                },
                isMimeTypes: function() {
                    return !!navigator.mimeTypes.length
                },
                getMimeTypes: function() {
                    for (var e = "", t = 0; t < navigator.mimeTypes.length; t++)
                        e = t == navigator.mimeTypes.length - 1 ? e + navigator.mimeTypes[t].description : e + (navigator.mimeTypes[t].description + ", ");
                    return e
                },
                isFont: function(e) {
                    return r.detect(e)
                },
                getFonts: function() {
                    for (var e = "Abadi MT Condensed Light;Adobe Fangsong Std;Adobe Hebrew;Adobe Ming Std;Agency FB;Aharoni;Andalus;Angsana New;AngsanaUPC;Aparajita;Arab;Arabic Transparent;Arabic Typesetting;Arial Baltic;Arial Black;Arial CE;Arial CYR;Arial Greek;Arial TUR;Arial;Batang;BatangChe;Bauhaus 93;Bell MT;Bitstream Vera Serif;Bodoni MT;Bookman Old Style;Braggadocio;Broadway;Browallia New;BrowalliaUPC;Calibri Light;Calibri;Californian FB;Cambria Math;Cambria;Candara;Castellar;Casual;Centaur;Century Gothic;Chalkduster;Colonna MT;Comic Sans MS;Consolas;Constantia;Copperplate Gothic Light;Corbel;Cordia New;CordiaUPC;Courier New Baltic;Courier New CE;Courier New CYR;Courier New Greek;Courier New TUR;Courier New;DFKai-SB;DaunPenh;David;DejaVu LGC Sans Mono;Desdemona;DilleniaUPC;DokChampa;Dotum;DotumChe;Ebrima;Engravers MT;Eras Bold ITC;Estrangelo Edessa;EucrosiaUPC;Euphemia;Eurostile;FangSong;Forte;FrankRuehl;Franklin Gothic Heavy;Franklin Gothic Medium;FreesiaUPC;French Script MT;Gabriola;Gautami;Georgia;Gigi;Gisha;Goudy Old Style;Gulim;GulimChe;GungSeo;Gungsuh;GungsuhChe;Haettenschweiler;Harrington;Hei S;HeiT;Heisei Kaku Gothic;Hiragino Sans GB;Impact;Informal Roman;IrisUPC;Iskoola Pota;JasmineUPC;KacstOne;KaiTi;Kalinga;Kartika;Khmer UI;Kino MT;KodchiangUPC;Kokila;Kozuka Gothic Pr6N;Lao UI;Latha;Leelawadee;Levenim MT;LilyUPC;Lohit Gujarati;Loma;Lucida Bright;Lucida Console;Lucida Fax;Lucida Sans Unicode;MS Gothic;MS Mincho;MS PGothic;MS PMincho;MS Reference Sans Serif;MS UI Gothic;MV Boli;Magneto;Malgun Gothic;Mangal;Marlett;Matura MT Script Capitals;Meiryo UI;Meiryo;Menlo;Microsoft Himalaya;Microsoft JhengHei;Microsoft New Tai Lue;Microsoft PhagsPa;Microsoft Sans Serif;Microsoft Tai Le;Microsoft Uighur;Microsoft YaHei;Microsoft Yi Baiti;MingLiU;MingLiU-ExtB;MingLiU_HKSCS;MingLiU_HKSCS-ExtB;Miriam Fixed;Miriam;Mongolian Baiti;MoolBoran;NSimSun;Narkisim;News Gothic MT;Niagara Solid;Nyala;PMingLiU;PMingLiU-ExtB;Palace Script MT;Palatino Linotype;Papyrus;Perpetua;Plantagenet Cherokee;Playbill;Prelude Bold;Prelude Condensed Bold;Prelude Condensed Medium;Prelude Medium;PreludeCompressedWGL Black;PreludeCompressedWGL Bold;PreludeCompressedWGL Light;PreludeCompressedWGL Medium;PreludeCondensedWGL Black;PreludeCondensedWGL Bold;PreludeCondensedWGL Light;PreludeCondensedWGL Medium;PreludeWGL Black;PreludeWGL Bold;PreludeWGL Light;PreludeWGL Medium;Raavi;Rachana;Rockwell;Rod;Sakkal Majalla;Sawasdee;Script MT Bold;Segoe Print;Segoe Script;Segoe UI Light;Segoe UI Semibold;Segoe UI Symbol;Segoe UI;Shonar Bangla;Showcard Gothic;Shruti;SimHei;SimSun;SimSun-ExtB;Simplified Arabic Fixed;Simplified Arabic;Snap ITC;Sylfaen;Symbol;Tahoma;Times New Roman Baltic;Times New Roman CE;Times New Roman CYR;Times New Roman Greek;Times New Roman TUR;Times New Roman;TlwgMono;Traditional Arabic;Trebuchet MS;Tunga;Tw Cen MT Condensed Extra Bold;Ubuntu;Umpush;Univers;Utopia;Utsaah;Vani;Verdana;Vijaya;Vladimir Script;Vrinda;Webdings;Wide Latin;Wingdings".split(";"), t = "", n = 0; n < e.length; n++)
                        r.detect(e[n]) && (t = n == e.length - 1 ? t + e[n] : t + (e[n] + ", "));
                    return t
                },
                isLocalStorage: function() {
                    try {
                        return !!e.localStorage
                    } catch (e) {
                        return !0
                    }
                },
                isSessionStorage: function() {
                    try {
                        return !!e.sessionStorage
                    } catch (e) {
                        return !0
                    }
                },
                isCookie: function() {
                    return navigator.cookieEnabled
                },
                getTimeZone: function() {
                    return String(String(new Date).split("(")[1]).split(")")[0]
                },
                getLanguage: function() {
                    return navigator.language
                },
                getSystemLanguage: function() {
                    return navigator.systemLanguage
                },
                isCanvas: function() {
                    var e = document.createElement("canvas");
                    try {
                        return !(!e.getContext || !e.getContext("2d"))
                    } catch (e) {
                        return !1
                    }
                },
                getCanvasPrint: function() {
                    var e, t = document.createElement("canvas");
                    try {
                        e = t.getContext("2d")
                    } catch (e) {
                        return ""
                    }
                    return e.textBaseline = "top",
                        e.font = "14px 'Arial'",
                        e.textBaseline = "alphabetic",
                        e.fillStyle = "#f60",
                        e.fillRect(125, 1, 62, 20),
                        e.fillStyle = "#069",
                        e.fillText("ClientJS,org <canvas> 1.0", 2, 15),
                        e.fillStyle = "rgba(102, 204, 0, 0.7)",
                        e.fillText("ClientJS,org <canvas> 1.0", 4, 17),
                        t.toDataURL()
                }
            },
            "object" == typeof module && "undefined" != typeof exports && (module.exports = n),
            e.ClientJS = n
    }(window);
var deployJava = function() {
        function o(e) {
            r.debug && (console.log ? console.log(e) : alert(e))
        }

        function n(e) {
            return null == e || 0 == e.length ? "http://java.com/dt-redirect" : ("&" == e.charAt(0) && (e = e.substring(1, e.length)),
                "http://java.com/dt-redirect?" + e)
        }
        var e = ["id", "class", "title", "style"];
        "classid codebase codetype data type archive declare standby height width usemap name tabindex align border hspace vspace".split(" ").concat(e, ["lang", "dir"], "onclick ondblclick onmousedown onmouseup onmouseover onmousemove onmouseout onkeypress onkeydown onkeyup".split(" "));
        var t, d = "codebase code name archive object width height alt align hspace vspace".split(" ").concat(e);
        try {
            t = -1 != document.location.protocol.indexOf("http") ? "//java.com/js/webstart.png" : "http://java.com/js/webstart.png"
        } catch (e) {
            t = "http://java.com/js/webstart.png"
        }
        var r = {
            debug: null,
            version: "20120801",
            firefoxJavaVersion: null,
            myInterval: null,
            preInstallJREList: null,
            returnPage: null,
            brand: null,
            locale: null,
            installType: null,
            EAInstallEnabled: !1,
            EarlyAccessURL: null,
            oldMimeType: "application/npruntime-scriptable-plugin;DeploymentToolkit",
            mimeType: "application/java-deployment-toolkit",
            launchButtonPNG: t,
            browserName: null,
            browserName2: null,
            getJREs: function() {
                var e = [];
                if (this.isPluginInstalled())
                    for (var t = this.getPlugin().jvms, n = 0; n < t.getLength(); n++)
                        e[n] = t.get(n).version;
                else
                    "MSIE" == (t = this.getBrowser()) ? this.testUsingActiveX("1.7.0") ? e[0] = "1.7.0" : this.testUsingActiveX("1.6.0") ? e[0] = "1.6.0" : this.testUsingActiveX("1.5.0") ? e[0] = "1.5.0" : this.testUsingActiveX("1.4.2") ? e[0] = "1.4.2" : this.testForMSVM() && (e[0] = "1.1") : "Netscape Family" == t && (this.getJPIVersionUsingMimeType(),
                        null != this.firefoxJavaVersion ? e[0] = this.firefoxJavaVersion : this.testUsingMimeTypes("1.7") ? e[0] = "1.7.0" : this.testUsingMimeTypes("1.6") ? e[0] = "1.6.0" : this.testUsingMimeTypes("1.5") ? e[0] = "1.5.0" : this.testUsingMimeTypes("1.4.2") ? e[0] = "1.4.2" : "Safari" == this.browserName2 && (this.testUsingPluginsArray("1.7.0") ? e[0] = "1.7.0" : this.testUsingPluginsArray("1.6") ? e[0] = "1.6.0" : this.testUsingPluginsArray("1.5") ? e[0] = "1.5.0" : this.testUsingPluginsArray("1.4.2") && (e[0] = "1.4.2")));
                if (this.debug)
                    for (n = 0; n < e.length; ++n)
                        o("[getJREs()] We claim to have detected Java SE " + e[n]);
                return e
            },
            installJRE: function(e, t) {
                if (this.isPluginInstalled() && this.isAutoInstallEnabled(e)) {
                    var n;
                    return (n = this.isCallbackSupported() ? this.getPlugin().installJRE(e, t) : this.getPlugin().installJRE(e)) && (this.refresh(),
                            null != this.returnPage && (document.location = this.returnPage)),
                        n
                }
                return this.installLatestJRE()
            },
            isAutoInstallEnabled: function(e) {
                if (!this.isPluginInstalled())
                    return !1;
                if (void 0 === e && (e = null),
                    "MSIE" != deployJava.browserName || deployJava.compareVersionToPattern(deployJava.getPlugin().version, ["10", "0", "0"], !1, !0))
                    e = !0;
                else if (null == e)
                    e = !1;
                else {
                    var t = "1.6.0_33+";
                    if (null == t || 0 == t.length)
                        e = !0;
                    else {
                        var n = t.charAt(t.length - 1);
                        if ("+" != n && "*" != n && -1 != t.indexOf("_") && "_" != n && (t += "*",
                                n = "*"),
                            0 < (t = t.substring(0, t.length - 1)).length) {
                            var r = t.charAt(t.length - 1);
                            "." != r && "_" != r || (t = t.substring(0, t.length - 1))
                        }
                        e = "*" == n ? 0 == e.indexOf(t) : "+" == n && t <= e
                    }
                    e = !e
                }
                return e
            },
            isCallbackSupported: function() {
                return this.isPluginInstalled() && this.compareVersionToPattern(this.getPlugin().version, ["10", "2", "0"], !1, !0)
            },
            installLatestJRE: function(e) {
                if (this.isPluginInstalled() && this.isAutoInstallEnabled()) {
                    var t = !1;
                    return (t = this.isCallbackSupported() ? this.getPlugin().installLatestJRE(e) : this.getPlugin().installLatestJRE()) && (this.refresh(),
                            null != this.returnPage && (document.location = this.returnPage)),
                        t
                }
                if (e = this.getBrowser(),
                    t = navigator.platform.toLowerCase(),
                    "true" == this.EAInstallEnabled && -1 != t.indexOf("win") && null != this.EarlyAccessURL)
                    this.preInstallJREList = this.getJREs(),
                    null != this.returnPage && (this.myInterval = setInterval("deployJava.poll()", 3e3)),
                    location.href = this.EarlyAccessURL;
                else {
                    if ("MSIE" == e)
                        return this.IEInstall();
                    if ("Netscape Family" == e && -1 != t.indexOf("win32"))
                        return this.FFInstall();
                    location.href = n((null != this.returnPage ? "&returnPage=" + this.returnPage : "") + (null != this.locale ? "&locale=" + this.locale : "") + (null != this.brand ? "&brand=" + this.brand : ""))
                }
                return !1
            },
            runApplet: function(e, t, n) {
                "undefined" != n && null != n || (n = "1.1");
                var r = n.match("^(\\d+)(?:\\.(\\d+)(?:\\.(\\d+)(?:_(\\d+))?)?)?$");
                null == this.returnPage && (this.returnPage = document.location),
                    null != r ? "?" != this.getBrowser() ? this.versionCheck(n + "+") ? this.writeAppletTag(e, t) : this.installJRE(n + "+") && (this.refresh(),
                        location.href = document.location,
                        this.writeAppletTag(e, t)) : this.writeAppletTag(e, t) : o("[runApplet()] Invalid minimumVersion argument to runApplet():" + n)
            },
            writeAppletTag: function(e, t) {
                var n = "<applet ",
                    r = "",
                    i = !0;
                for (var a in null != t && "object" == typeof t || (t = {}),
                        e) {
                    var o;
                    e: {
                        o = a.toLowerCase();
                        for (var s = d.length, l = 0; l < s; l++)
                            if (d[l] === o) {
                                o = !0;
                                break e
                            }
                        o = !1
                    }
                    o ? (n += " " + a + '="' + e[a] + '"',
                        "code" == a && (i = !1)) : t[a] = e[a]
                }
                for (var c in a = !1,
                        t)
                    "codebase_lookup" == c && (a = !0),
                    "object" != c && "java_object" != c && "java_code" != c || (i = !1),
                    r += '<param name="' + c + '" value="' + t[c] + '"/>';
                a || (r += '<param name="codebase_lookup" value="false"/>'),
                    i && (n += ' code="dummy"'),
                    document.write(n + ">\n" + r + "\n</applet>")
            },
            versionCheck: function(e) {
                var t = 0,
                    n = e.match("^(\\d+)(?:\\.(\\d+)(?:\\.(\\d+)(?:_(\\d+))?)?)?(\\*|\\+)?$");
                if (null != n) {
                    for (var r = e = !1, i = [], a = 1; a < n.length; ++a)
                        "string" == typeof n[a] && "" != n[a] && (i[t] = n[a],
                            t++);
                    for ("+" == i[i.length - 1] ? (e = !(r = !0),
                            i.length--) : "*" == i[i.length - 1] ? (e = !(r = !1),
                            i.length--) : i.length < 4 && (e = !(r = !1)),
                        t = this.getJREs(),
                        a = 0; a < t.length; ++a)
                        if (this.compareVersionToPattern(t[a], i, e, r))
                            return !0
                } else
                    o("[versionCheck()] " + (t = "Invalid versionPattern passed to versionCheck: " + e)),
                    alert(t);
                return !1
            },
            isWebStartInstalled: function(e) {
                if ("?" == this.getBrowser())
                    return !0;
                "undefined" != e && null != e || (e = "1.4.2");
                var t = !1;
                return null != e.match("^(\\d+)(?:\\.(\\d+)(?:\\.(\\d+)(?:_(\\d+))?)?)?$") ? t = this.versionCheck(e + "+") : (o("[isWebStartInstaller()] Invalid minimumVersion argument to isWebStartInstalled(): " + e),
                        t = this.versionCheck("1.4.2+")),
                    t
            },
            getJPIVersionUsingMimeType: function() {
                for (var e = 0; e < navigator.mimeTypes.length; ++e) {
                    var t = navigator.mimeTypes[e].type.match(/^application\/x-java-applet;jpi-version=(.*)$/);
                    if (null != t && (this.firefoxJavaVersion = t[1],
                            "Opera" != this.browserName2))
                        break
                }
            },
            launchWebStartApplication: function(e) {
                if (navigator.userAgent.toLowerCase(),
                    this.getJPIVersionUsingMimeType(),
                    0 == this.isWebStartInstalled("1.7.0") && (0 == this.installJRE("1.7.0+") || 0 == this.isWebStartInstalled("1.7.0")))
                    return !1;
                var t = null;
                document.documentURI && (t = document.documentURI),
                    null == t && (t = document.URL);
                var n, r = this.getBrowser();
                "MSIE" == r ? n = '<object classid="clsid:8AD9C840-044E-11D1-B3E9-00805F499D93" width="0" height="0"><PARAM name="launchjnlp" value="' + e + '"><PARAM name="docbase" value="' + t + '"></object>' : "Netscape Family" == r && (n = '<embed type="application/x-java-applet;jpi-version=' + this.firefoxJavaVersion + '" width="0" height="0" launchjnlp="' + e + '"docbase="' + t + '" />'),
                    "undefined" == document.body || null == document.body ? (document.write(n),
                        document.location = t) : ((e = document.createElement("div")).id = "div1",
                        e.style.position = "relative",
                        e.style.left = "-10000px",
                        e.style.margin = "0px auto",
                        e.className = "dynamicDiv",
                        e.innerHTML = n,
                        document.body.appendChild(e))
            },
            createWebStartLaunchButtonEx: function(e, t) {
                null == this.returnPage && (this.returnPage = e),
                    document.write("<a href=\"javascript:deployJava.launchWebStartApplication('" + e + '\');" onMouseOver="window.status=\'\'; return true;"><img src="' + this.launchButtonPNG + '" border="0" /></a>')
            },
            createWebStartLaunchButton: function(e, t) {
                null == this.returnPage && (this.returnPage = e),
                    document.write('<a href="javascript:if (!deployJava.isWebStartInstalled(&quot;' + t + "&quot;)) {if (deployJava.installLatestJRE()) {if (deployJava.launch(&quot;" + e + "&quot;)) {}}} else {if (deployJava.launch(&quot;" + e + '&quot;)) {}}" onMouseOver="window.status=\'\'; return true;"><img src="' + this.launchButtonPNG + '" border="0" /></a>')
            },
            launch: function(e) {
                return document.location = e,
                    !0
            },
            isPluginInstalled: function() {
                var e = this.getPlugin();
                return !(!e || !e.jvms)
            },
            isAutoUpdateEnabled: function() {
                return !!this.isPluginInstalled() && this.getPlugin().isAutoUpdateEnabled()
            },
            setAutoUpdateEnabled: function() {
                return !!this.isPluginInstalled() && this.getPlugin().setAutoUpdateEnabled()
            },
            setInstallerType: function(e) {
                return this.installType = e,
                    !!this.isPluginInstalled() && this.getPlugin().setInstallerType(e)
            },
            setAdditionalPackages: function(e) {
                return !!this.isPluginInstalled() && this.getPlugin().setAdditionalPackages(e)
            },
            setEarlyAccess: function(e) {
                this.EAInstallEnabled = e
            },
            isPlugin2: function() {
                if (this.isPluginInstalled() && this.versionCheck("1.6.0_10+"))
                    try {
                        return this.getPlugin().isPlugin2()
                    } catch (e) {}
                return !1
            },
            allowPlugin: function() {
                return this.getBrowser(),
                    "Safari" != this.browserName2 && "Opera" != this.browserName2
            },
            getPlugin: function() {
                this.refresh();
                var e = null;
                return this.allowPlugin() && (e = document.getElementById("deployJavaPlugin")),
                    e
            },
            compareVersionToPattern: function(e, t, n, r) {
                if (null == e || null == t)
                    return !1;
                var i = e.match("^(\\d+)(?:\\.(\\d+)(?:\\.(\\d+)(?:_(\\d+))?)?)?$");
                if (null != i) {
                    var a = 0;
                    e = [];
                    for (var o = 1; o < i.length; ++o)
                        "string" == typeof i[o] && "" != i[o] && (e[a] = i[o],
                            a++);
                    if (i = Math.min(e.length, t.length),
                        r) {
                        for (o = 0; o < i; ++o) {
                            if (e[o] < t[o])
                                return !1;
                            if (e[o] > t[o])
                                break
                        }
                        return !0
                    }
                    for (o = 0; o < i; ++o)
                        if (e[o] != t[o])
                            return !1;
                    return !!n || e.length == t.length
                }
                return !1
            },
            getBrowser: function() {
                if (null == this.browserName) {
                    var e = navigator.userAgent.toLowerCase();
                    o("[getBrowser()] navigator.userAgent.toLowerCase() -> " + e),
                        -1 != e.indexOf("msie") && -1 == e.indexOf("opera") ? this.browserName2 = this.browserName = "MSIE" : -1 != e.indexOf("iphone") ? (this.browserName = "Netscape Family",
                            this.browserName2 = "iPhone") : -1 != e.indexOf("firefox") && -1 == e.indexOf("opera") ? (this.browserName = "Netscape Family",
                            this.browserName2 = "Firefox") : -1 != e.indexOf("chrome") ? (this.browserName = "Netscape Family",
                            this.browserName2 = "Chrome") : -1 != e.indexOf("safari") ? (this.browserName = "Netscape Family",
                            this.browserName2 = "Safari") : -1 != e.indexOf("mozilla") && -1 == e.indexOf("opera") ? (this.browserName = "Netscape Family",
                            this.browserName2 = "Other") : -1 != e.indexOf("opera") ? (this.browserName = "Netscape Family",
                            this.browserName2 = "Opera") : (this.browserName = "?",
                            this.browserName2 = "unknown"),
                        o("[getBrowser()] Detected browser name:" + this.browserName + ", " + this.browserName2)
                }
                return this.browserName
            },
            testUsingActiveX: function(e) {
                if (e = "JavaWebStart.isInstalled." + e + ".0",
                    "undefined" == typeof ActiveXObject || !ActiveXObject)
                    return o("[testUsingActiveX()] Browser claims to be IE, but no ActiveXObject object?"),
                        !1;
                try {
                    return null != new ActiveXObject(e)
                } catch (e) {
                    return !1
                }
            },
            testForMSVM: function() {
                if ("undefined" != typeof oClientCaps) {
                    var e = oClientCaps.getComponentVersion("{08B0E5C0-4FCB-11CF-AAA5-00401C608500}", "ComponentID");
                    return "" != e && "5,0,5000,0" != e
                }
                return !1
            },
            testUsingMimeTypes: function(e) {
                if (!navigator.mimeTypes)
                    return o("[testUsingMimeTypes()] Browser claims to be Netscape family, but no mimeTypes[] array?"),
                        !1;
                for (var t = 0; t < navigator.mimeTypes.length; ++t) {
                    s = navigator.mimeTypes[t].type;
                    var n = s.match(/^application\/x-java-applet\x3Bversion=(1\.8|1\.7|1\.6|1\.5|1\.4\.2)$/);
                    if (null != n && this.compareVersions(n[1], e))
                        return !0
                }
                return !1
            },
            testUsingPluginsArray: function(e) {
                if (!navigator.plugins || !navigator.plugins.length)
                    return !1;
                for (var t = navigator.platform.toLowerCase(), n = 0; n < navigator.plugins.length; ++n)
                    if (s = navigator.plugins[n].description,
                        -1 != s.search(/^Java Switchable Plug-in (Cocoa)/)) {
                        if (this.compareVersions("1.5.0", e))
                            return !0
                    } else if (-1 != s.search(/^Java/) && -1 != t.indexOf("win") && (this.compareVersions("1.5.0", e) || this.compareVersions("1.6.0", e)))
                    return !0;
                return !!this.compareVersions("1.5.0", e)
            },
            IEInstall: function() {
                return location.href = n((null != this.returnPage ? "&returnPage=" + this.returnPage : "") + (null != this.locale ? "&locale=" + this.locale : "") + (null != this.brand ? "&brand=" + this.brand : "")),
                    !1
            },
            done: function(e, t) {},
            FFInstall: function() {
                return location.href = n((null != this.returnPage ? "&returnPage=" + this.returnPage : "") + (null != this.locale ? "&locale=" + this.locale : "") + (null != this.brand ? "&brand=" + this.brand : "") + (null != this.installType ? "&type=" + this.installType : "")),
                    !1
            },
            compareVersions: function(e, t) {
                for (var n = e.split("."), r = t.split("."), i = 0; i < n.length; ++i)
                    n[i] = Number(n[i]);
                for (i = 0; i < r.length; ++i)
                    r[i] = Number(r[i]);
                return 2 == n.length && (n[2] = 0),
                    n[0] > r[0] || !(n[0] < r[0]) && (n[1] > r[1] || !(n[1] < r[1]) && (n[2] > r[2] || !(n[2] < r[2])))
            },
            enableAlerts: function() {
                this.browserName = null,
                    this.debug = !0
            },
            poll: function() {
                this.refresh();
                var e = this.getJREs();
                0 == this.preInstallJREList.length && 0 != e.length && (clearInterval(this.myInterval),
                        null != this.returnPage && (location.href = this.returnPage)),
                    0 != this.preInstallJREList.length && 0 != e.length && this.preInstallJREList[0] != e[0] && (clearInterval(this.myInterval),
                        null != this.returnPage && (location.href = this.returnPage))
            },
            writePluginTag: function() {
                var e = this.getBrowser();
                "MSIE" == e ? document.write('<object classid="clsid:CAFEEFAC-DEC7-0000-0001-ABCDEFFEDCBA" id="deployJavaPlugin" width="0" height="0"></object>') : "Netscape Family" == e && this.allowPlugin() && this.writeEmbedTag()
            },
            refresh: function() {
                navigator.plugins.refresh(!1),
                    "Netscape Family" == this.getBrowser() && this.allowPlugin() && null == document.getElementById("deployJavaPlugin") && this.writeEmbedTag()
            },
            writeEmbedTag: function() {
                var e = !1;
                if (null != navigator.mimeTypes) {
                    for (var t = 0; t < navigator.mimeTypes.length; t++)
                        navigator.mimeTypes[t].type == this.mimeType && navigator.mimeTypes[t].enabledPlugin && (document.write('<embed id="deployJavaPlugin" type="' + this.mimeType + '" hidden="true" />'),
                            e = !0);
                    if (!e)
                        for (t = 0; t < navigator.mimeTypes.length; t++)
                            navigator.mimeTypes[t].type == this.oldMimeType && navigator.mimeTypes[t].enabledPlugin && document.write('<embed id="deployJavaPlugin" type="' + this.oldMimeType + '" hidden="true" />')
                }
            }
        };
        if (r.writePluginTag(),
            null == r.locale) {
            if ((e = null) == e)
                try {
                    e = navigator.userLanguage
                } catch (e) {}
            if (null == e)
                try {
                    e = navigator.systemLanguage
                } catch (e) {}
            if (null == e)
                try {
                    e = navigator.language
                } catch (e) {}
            null != e && (e.replace("-", "_"),
                r.locale = e)
        }
        return r
    }(),
    Detector = function() {
        var i = ["monospace", "sans-serif", "serif"],
            a = document.getElementsByTagName("body")[0],
            o = document.createElement("span");
        o.style.fontSize = "72px",
            o.innerHTML = "mmmmmmmmmmlli";
        var e, s = {},
            l = {};
        for (e in i)
            o.style.fontFamily = i[e],
            a.appendChild(o),
            s[i[e]] = o.offsetWidth,
            l[i[e]] = o.offsetHeight,
            a.removeChild(o);
        this.detect = function(e) {
            var t, n = !1;
            for (t in i) {
                o.style.fontFamily = e + "," + i[t],
                    a.appendChild(o);
                var r = o.offsetWidth != s[i[t]] || o.offsetHeight != l[i[t]];
                a.removeChild(o),
                    n = n || r
            }
            return n
        }
    };

function murmurhash3_32_gc(e, t) {
    var n, r, i, a, o;
    for (n = 3 & e.length,
        r = e.length - n,
        i = t,
        o = 0; o < r;)
        a = 255 & e.charCodeAt(o) | (255 & e.charCodeAt(++o)) << 8 | (255 & e.charCodeAt(++o)) << 16 | (255 & e.charCodeAt(++o)) << 24,
        ++o,
        i = 27492 + (65535 & (i = 5 * (65535 & (i = (i ^= a = 461845907 * (65535 & (a = (a = 3432918353 * (65535 & a) + ((3432918353 * (a >>> 16) & 65535) << 16) & 4294967295) << 15 | a >>> 17)) + ((461845907 * (a >>> 16) & 65535) << 16) & 4294967295) << 13 | i >>> 19)) + ((5 * (i >>> 16) & 65535) << 16) & 4294967295)) + ((58964 + (i >>> 16) & 65535) << 16);
    switch (a = 0,
        n) {
        case 3:
            a ^= (255 & e.charCodeAt(o + 2)) << 16;
        case 2:
            a ^= (255 & e.charCodeAt(o + 1)) << 8;
        case 1:
            i ^= 461845907 * (65535 & (a = (a = 3432918353 * (65535 & (a ^= 255 & e.charCodeAt(o))) + ((3432918353 * (a >>> 16) & 65535) << 16) & 4294967295) << 15 | a >>> 17)) + ((461845907 * (a >>> 16) & 65535) << 16) & 4294967295
    }
    return i ^= e.length,
        i = 2246822507 * (65535 & (i ^= i >>> 16)) + ((2246822507 * (i >>> 16) & 65535) << 16) & 4294967295,
        ((i = 3266489909 * (65535 & (i ^= i >>> 13)) + ((3266489909 * (i >>> 16) & 65535) << 16) & 4294967295) ^ i >>> 16) >>> 0
}
var swfobject = function() {
    function e() {
        if (!j) {
            try {
                (e = x.getElementsByTagName("body")[0].appendChild(x.createElement("span"))).parentNode.removeChild(e)
            } catch (e) {
                return
            }
            j = !0;
            for (var e = q.length, t = 0; t < e; t++)
                q[t]()
        }
    }

    function t(e) {
        j ? e() : q[q.length] = e
    }

    function n(e) {
        if (void 0 !== k.addEventListener)
            k.addEventListener("load", e, !1);
        else if (void 0 !== x.addEventListener)
            x.addEventListener("load", e, !1);
        else if (void 0 !== k.attachEvent)
            r = "onload",
            i = e,
            (n = k).attachEvent(r, i),
            P[P.length] = [n, r, i];
        else if ("function" == typeof k.onload) {
            var t = k.onload;
            k.onload = function() {
                t(),
                    e()
            }
        } else
            k.onload = e;
        var n, r, i
    }

    function a() {
        var e = S.length;
        if (0 < e)
            for (var t = 0; t < e; t++) {
                var n = S[t].id,
                    r = S[t].callbackFn,
                    i = {
                        success: !1,
                        id: n
                    };
                if (0 < M.pv[0]) {
                    if (a = u(n))
                        if (!_(S[t].swfVersion) || M.wk && M.wk < 312)
                            if (S[t].expressInstall && f()) {
                                (i = {}).data = S[t].expressInstall,
                                    i.width = a.getAttribute("width") || "0",
                                    i.height = a.getAttribute("height") || "0",
                                    a.getAttribute("class") && (i.styleclass = a.getAttribute("class")),
                                    a.getAttribute("align") && (i.align = a.getAttribute("align"));
                                for (var a, o = {}, s = (a = a.getElementsByTagName("param")).length, l = 0; l < s; l++)
                                    "movie" != a[l].getAttribute("name").toLowerCase() && (o[a[l].getAttribute("name")] = a[l].getAttribute("value"));
                                g(i, o, n, r)
                            } else
                                d(a),
                                r && r(i);
                    else
                        y(n, !0),
                        r && (i.success = !0,
                            i.ref = c(n),
                            r(i))
                } else
                    y(n, !0),
                    r && ((n = c(n)) && void 0 !== n.SetVariable && (i.success = !0,
                            i.ref = n),
                        r(i))
            }
    }

    function c(e) {
        var t = null;
        return (e = u(e)) && "OBJECT" == e.nodeName && (void 0 !== e.SetVariable ? t = e : (e = e.getElementsByTagName("object")[0]) && (t = e)),
            t
    }

    function f() {
        return !E && _("6.0.65") && (M.win || M.mac) && !(M.wk && M.wk < 312)
    }

    function g(e, t, n, r) {
        m = r || null,
            h = {
                success: !(E = !0),
                id: n
            };
        var i = u(n);
        i && ("OBJECT" == i.nodeName ? (l = o(i),
                p = null) : (l = i,
                p = n),
            e.id = "SWFObjectExprInst",
            (void 0 === e.width || !/%$/.test(e.width) && parseInt(e.width, 10) < 310) && (e.width = "310"),
            (void 0 === e.height || !/%$/.test(e.height) && parseInt(e.height, 10) < 137) && (e.height = "137"),
            x.title = x.title.slice(0, 47) + " - Flash Player Installation",
            r = M.ie && M.win ? "ActiveX" : "PlugIn",
            r = "MMredirectURL=" + k.location.toString().replace(/&/g, "%26") + "&MMplayerType=" + r + "&MMdoctitle=" + x.title,
            t.flashvars = void 0 !== t.flashvars ? t.flashvars + "&" + r : r,
            M.ie && M.win && 4 != i.readyState && (n += "SWFObjectNew",
                (r = x.createElement("div")).setAttribute("id", n),
                i.parentNode.insertBefore(r, i),
                i.style.display = "none",
                function() {
                    4 == i.readyState ? i.parentNode.removeChild(i) : setTimeout(arguments.callee, 10)
                }()),
            v(e, t, n))
    }

    function d(e) {
        if (M.ie && M.win && 4 != e.readyState) {
            var t = x.createElement("div");
            e.parentNode.insertBefore(t, e),
                t.parentNode.replaceChild(o(e), t),
                e.style.display = "none",
                function() {
                    4 == e.readyState ? e.parentNode.removeChild(e) : setTimeout(arguments.callee, 10)
                }()
        } else
            e.parentNode.replaceChild(o(e), e)
    }

    function o(e) {
        var t = x.createElement("div");
        if (M.win && M.ie)
            t.innerHTML = e.innerHTML;
        else if ((e = e.getElementsByTagName("object")[0]) && (e = e.childNodes))
            for (var n = e.length, r = 0; r < n; r++)
                1 == e[r].nodeType && "PARAM" == e[r].nodeName || 8 == e[r].nodeType || t.appendChild(e[r].cloneNode(!0));
        return t
    }

    function v(e, t, n) {
        var r, i = u(n);
        if (M.wk && M.wk < 312)
            return r;
        if (i)
            if (void 0 === e.id && (e.id = n),
                M.ie && M.win) {
                var a, o = "";
                for (a in e)
                    e[a] != Object.prototype[a] && ("data" == a.toLowerCase() ? t.movie = e[a] : "styleclass" == a.toLowerCase() ? o += ' class="' + e[a] + '"' : "classid" != a.toLowerCase() && (o += " " + a + '="' + e[a] + '"'));
                for (var s in a = "",
                        t)
                    t[s] != Object.prototype[s] && (a += '<param name="' + s + '" value="' + t[s] + '" />');
                i.outerHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"' + o + ">" + a + "</object>",
                    T[T.length] = e.id,
                    r = u(e.id)
            } else {
                for (var l in (s = x.createElement("object")).setAttribute("type", "application/x-shockwave-flash"),
                        e)
                    e[l] != Object.prototype[l] && ("styleclass" == l.toLowerCase() ? s.setAttribute("class", e[l]) : "classid" != l.toLowerCase() && s.setAttribute(l, e[l]));
                for (o in t)
                    t[o] != Object.prototype[o] && "movie" != o.toLowerCase() && (e = s,
                        l = t[a = o],
                        (n = x.createElement("param")).setAttribute("name", a),
                        n.setAttribute("value", l),
                        e.appendChild(n));
                i.parentNode.replaceChild(s, i),
                    r = s
            }
        return r
    }

    function i(n) {
        var r = u(n);
        r && "OBJECT" == r.nodeName && (M.ie && M.win ? (r.style.display = "none",
            function() {
                if (4 == r.readyState) {
                    var e = u(n);
                    if (e) {
                        for (var t in e)
                            "function" == typeof e[t] && (e[t] = null);
                        e.parentNode.removeChild(e)
                    }
                } else
                    setTimeout(arguments.callee, 10)
            }()) : r.parentNode.removeChild(r))
    }

    function u(e) {
        var t = null;
        try {
            t = x.getElementById(e)
        } catch (e) {}
        return t
    }

    function _(e) {
        var t = M.pv;
        return (e = e.split("."))[0] = parseInt(e[0], 10),
            e[1] = parseInt(e[1], 10) || 0,
            e[2] = parseInt(e[2], 10) || 0,
            t[0] > e[0] || t[0] == e[0] && t[1] > e[1] || t[0] == e[0] && t[1] == e[1] && t[2] >= e[2]
    }

    function s(e, t, n, r) {
        if (!M.ie || !M.mac) {
            var i = x.getElementsByTagName("head")[0];
            i && (n = n && "string" == typeof n ? n : "screen",
                r && (w = b = null),
                b && w == n || ((r = x.createElement("style")).setAttribute("type", "text/css"),
                    r.setAttribute("media", n),
                    b = i.appendChild(r),
                    M.ie && M.win && void 0 !== x.styleSheets && 0 < x.styleSheets.length && (b = x.styleSheets[x.styleSheets.length - 1]),
                    w = n),
                M.ie && M.win ? b && "object" == typeof b.addRule && b.addRule(e, t) : b && void 0 !== x.createTextNode && b.appendChild(x.createTextNode(e + " {" + t + "}")))
        }
    }

    function y(e, t) {
        if (I) {
            var n = t ? "visible" : "hidden";
            j && u(e) ? u(e).style.visibility = n : s("#" + e, "visibility:" + n)
        }
    }

    function r(e) {
        return null != /[\\\"<>\.;]/.exec(e) && "undefined" != typeof encodeURIComponent ? encodeURIComponent(e) : e
    }
    var l, p, m, h, b, w, k = window,
        x = document,
        $ = navigator,
        C = !1,
        q = [function() {
            C ? function() {
                var t = x.getElementsByTagName("body")[0],
                    n = x.createElement("object");
                n.setAttribute("type", "application/x-shockwave-flash");
                var r = t.appendChild(n);
                if (r) {
                    var i = 0;
                    ! function() {
                        if (void 0 !== r.GetVariable) {
                            var e = r.GetVariable("$version");
                            e && (e = e.split(" ")[1].split(","),
                                M.pv = [parseInt(e[0], 10), parseInt(e[1], 10), parseInt(e[2], 10)])
                        } else if (i < 10)
                            return i++,
                                setTimeout(arguments.callee, 10);
                        t.removeChild(n),
                            r = null,
                            a()
                    }()
                } else
                    a()
            }() : a()
        }],
        S = [],
        T = [],
        P = [],
        j = !1,
        E = !1,
        I = !0,
        M = function() {
            var e = void 0 !== x.getElementById && void 0 !== x.getElementsByTagName && void 0 !== x.createElement,
                t = $.userAgent.toLowerCase(),
                n = $.platform.toLowerCase(),
                r = /win/.test(n || t),
                i = (n = /mac/.test(n || t),
                    t = !!/webkit/.test(t) && parseFloat(t.replace(/^.*webkit\/(\d+(\.\d+)?).*$/, "$1")),
                    !1),
                a = [0, 0, 0],
                o = null;
            if (void 0 !== $.plugins && "object" == typeof $.plugins["Shockwave Flash"])
                !(o = $.plugins["Shockwave Flash"].description) || void 0 !== $.mimeTypes && $.mimeTypes["application/x-shockwave-flash"] && !$.mimeTypes["application/x-shockwave-flash"].enabledPlugin || (i = !(C = !0),
                    o = o.replace(/^.*\s+(\S+\s+\S+$)/, "$1"),
                    a[0] = parseInt(o.replace(/^(.*)\..*$/, "$1"), 10),
                    a[1] = parseInt(o.replace(/^.*\.(.*)\s.*$/, "$1"), 10),
                    a[2] = /[a-zA-Z]/.test(o) ? parseInt(o.replace(/^.*[a-zA-Z]+(.*)$/, "$1"), 10) : 0);
            else if (void 0 !== k.ActiveXObject)
                try {
                    var s = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
                    s && (o = s.GetVariable("$version")) && (i = !0,
                        o = o.split(" ")[1].split(","),
                        a = [parseInt(o[0], 10), parseInt(o[1], 10), parseInt(o[2], 10)])
                } catch (e) {}
            return {
                w3: e,
                pv: a,
                wk: t,
                ie: i,
                win: r,
                mac: n
            }
        }();
    return M.w3 && ((void 0 !== x.readyState && "complete" == x.readyState || void 0 === x.readyState && (x.getElementsByTagName("body")[0] || x.body)) && e(),
            j || (void 0 !== x.addEventListener && x.addEventListener("DOMContentLoaded", e, !1),
                M.ie && M.win && (x.attachEvent("onreadystatechange", function() {
                        "complete" == x.readyState && (x.detachEvent("onreadystatechange", arguments.callee),
                            e())
                    }),
                    k == top && function() {
                        if (!j) {
                            try {
                                x.documentElement.doScroll("left")
                            } catch (e) {
                                return setTimeout(arguments.callee, 0)
                            }
                            e()
                        }
                    }()),
                M.wk && function() {
                    j || (/loaded|complete/.test(x.readyState) ? e() : setTimeout(arguments.callee, 0))
                }(),
                n(e))),
        M.ie && M.win && window.attachEvent("onunload", function() {
            for (var e = P.length, t = 0; t < e; t++)
                P[t][0].detachEvent(P[t][1], P[t][2]);
            for (e = T.length,
                t = 0; t < e; t++)
                i(T[t]);
            for (var n in M)
                M[n] = null;
            for (var r in M = null,
                    swfobject)
                swfobject[r] = null;
            swfobject = null
        }), {
            registerObject: function(e, t, n, r) {
                if (M.w3 && e && t) {
                    var i = {};
                    i.id = e,
                        i.swfVersion = t,
                        i.expressInstall = n,
                        i.callbackFn = r,
                        S[S.length] = i,
                        y(e, !1)
                } else
                    r && r({
                        success: !1,
                        id: e
                    })
            },
            getObjectById: function(e) {
                if (M.w3)
                    return c(e)
            },
            embedSWF: function(i, a, o, s, l, c, d, u, p, m) {
                var h = {
                    success: !1,
                    id: a
                };
                M.w3 && !(M.wk && M.wk < 312) && i && a && o && s && l ? (y(a, !1),
                    t(function() {
                        o += "",
                            s += "";
                        var e = {};
                        if (p && "object" == typeof p)
                            for (var t in p)
                                e[t] = p[t];
                        if (e.data = i,
                            e.width = o,
                            e.height = s,
                            t = {},
                            u && "object" == typeof u)
                            for (var n in u)
                                t[n] = u[n];
                        if (d && "object" == typeof d)
                            for (var r in d)
                                t.flashvars = void 0 !== t.flashvars ? t.flashvars + "&" + r + "=" + d[r] : r + "=" + d[r];
                        if (_(l))
                            n = v(e, t, a),
                            e.id == a && y(a, !0),
                            h.success = !0,
                            h.ref = n;
                        else {
                            if (c && f())
                                return e.data = c,
                                    void g(e, t, a, m);
                            y(a, !0)
                        }
                        m && m(h)
                    })) : m && m(h)
            },
            switchOffAutoHideShow: function() {
                I = !1
            },
            ua: M,
            getFlashPlayerVersion: function() {
                return {
                    major: M.pv[0],
                    minor: M.pv[1],
                    release: M.pv[2]
                }
            },
            hasFlashPlayerVersion: _,
            createSWF: function(e, t, n) {
                if (M.w3)
                    return v(e, t, n)
            },
            showExpressInstall: function(e, t, n, r) {
                M.w3 && f() && g(e, t, n, r)
            },
            removeSWF: function(e) {
                M.w3 && i(e)
            },
            createCSS: function(e, t, n, r) {
                M.w3 && s(e, t, n, r)
            },
            addDomLoadEvent: t,
            addLoadEvent: n,
            getQueryParamValue: function(e) {
                if (t = x.location.search || x.location.hash) {
                    if (/\?/.test(t) && (t = t.split("?")[1]),
                        null == e)
                        return r(t);
                    for (var t = t.split("&"), n = 0; n < t.length; n++)
                        if (t[n].substring(0, t[n].indexOf("=")) == e)
                            return r(t[n].substring(t[n].indexOf("=") + 1))
                }
                return ""
            },
            expressInstallCallback: function() {
                if (E) {
                    var e = u("SWFObjectExprInst");
                    e && l && (e.parentNode.replaceChild(l, e),
                            p && (y(p, !0),
                                M.ie && M.win && (l.style.display = "block")),
                            m && m(h)),
                        E = !1
                }
            }
        }
}();
! function(i, u) {
    var a = {
            extend: function(e, t) {
                for (var n in t)
                    -
                    1 !== "browser cpu device engine os".indexOf(n) && 0 == t[n].length % 2 && (e[n] = t[n].concat(e[n]));
                return e
            },
            has: function(e, t) {
                return "string" == typeof e && -1 !== t.toLowerCase().indexOf(e.toLowerCase())
            },
            lowerize: function(e) {
                return e.toLowerCase()
            },
            major: function(e) {
                return "string" == typeof e ? e.split(".")[0] : u
            }
        },
        o = function() {
            for (var e, t, n, r, i, a, o, s = 0, l = arguments; s < l.length && !a;) {
                var c = l[s],
                    d = l[s + 1];
                if (void 0 === e)
                    for (r in e = {},
                        d)
                        d.hasOwnProperty(r) && ("object" == typeof(i = d[r]) ? e[i[0]] = u : e[i] = u);
                for (t = n = 0; t < c.length && !a;)
                    if (a = c[t++].exec(this.getUA()))
                        for (r = 0; r < d.length; r++)
                            o = a[++n],
                            "object" == typeof(i = d[r]) && 0 < i.length ? 2 == i.length ? e[i[0]] = "function" == typeof i[1] ? i[1].call(this, o) : i[1] : 3 == i.length ? e[i[0]] = "function" != typeof i[1] || i[1].exec && i[1].test ? o ? o.replace(i[1], i[2]) : u : o ? i[1].call(this, o, i[2]) : u : 4 == i.length && (e[i[0]] = o ? i[3].call(this, o.replace(i[1], i[2])) : u) : e[i] = o || u;
                s += 2
            }
            return e
        },
        e = function(e, t) {
            for (var n in t)
                if ("object" == typeof t[n] && 0 < t[n].length) {
                    for (var r = 0; r < t[n].length; r++)
                        if (a.has(t[n][r], e))
                            return "?" === n ? u : n
                } else if (a.has(t[n], e))
                return "?" === n ? u : n;
            return e
        },
        t = {
            ME: "4.90",
            "NT 3.11": "NT3.51",
            "NT 4.0": "NT4.0",
            2e3: "NT 5.0",
            XP: ["NT 5.1", "NT 5.2"],
            Vista: "NT 6.0",
            7: "NT 6.1",
            8: "NT 6.2",
            8.1: "NT 6.3",
            10: ["NT 6.4", "NT 10.0"],
            RT: "ARM"
        },
        s = {
            browser: [
                [/(opera\smini)\/([\w\.-]+)/i, /(opera\s[mobiletab]+).+version\/([\w\.-]+)/i, /(opera).+version\/([\w\.]+)/i, /(opera)[\/\s]+([\w\.]+)/i],
                ["name", "version"],
                [/\s(opr)\/([\w\.]+)/i],
                [
                    ["name", "Opera"], "version"
                ],
                [/(kindle)\/([\w\.]+)/i, /(lunascape|maxthon|netfront|jasmine|blazer)[\/\s]?([\w\.]+)*/i, /(avant\s|iemobile|slim|baidu)(?:browser)?[\/\s]?([\w\.]*)/i, /(?:ms|\()(ie)\s([\w\.]+)/i, /(rekonq)\/([\w\.]+)*/i, /(chromium|flock|rockmelt|midori|epiphany|silk|skyfire|ovibrowser|bolt|iron|vivaldi|iridium|phantomjs)\/([\w\.-]+)/i],
                ["name", "version"],
                [/(trident).+rv[:\s]([\w\.]+).+like\sgecko/i],
                [
                    ["name", "IE"], "version"
                ],
                [/(edge)\/((\d+)?[\w\.]+)/i],
                ["name", "version"],
                [/(yabrowser)\/([\w\.]+)/i],
                [
                    ["name", "Yandex"], "version"
                ],
                [/(comodo_dragon)\/([\w\.]+)/i],
                [
                    ["name", /_/g, " "], "version"
                ],
                [/(chrome|omniweb|arora|[tizenoka]{5}\s?browser)\/v?([\w\.]+)/i, /(qqbrowser)[\/\s]?([\w\.]+)/i],
                ["name", "version"],
                [/(uc\s?browser)[\/\s]?([\w\.]+)/i, /ucweb.+(ucbrowser)[\/\s]?([\w\.]+)/i, /JUC.+(ucweb)[\/\s]?([\w\.]+)/i],
                [
                    ["name", "UCBrowser"], "version"
                ],
                [/(dolfin)\/([\w\.]+)/i],
                [
                    ["name", "Dolphin"], "version"
                ],
                [/((?:android.+)crmo|crios)\/([\w\.]+)/i],
                [
                    ["name", "Chrome"], "version"
                ],
                [/XiaoMi\/MiuiBrowser\/([\w\.]+)/i],
                ["version", ["name", "MIUI Browser"]],
                [/android.+version\/([\w\.]+)\s+(?:mobile\s?safari|safari)/i],
                ["version", ["name", "Android Browser"]],
                [/FBAV\/([\w\.]+);/i],
                ["version", ["name", "Facebook"]],
                [/fxios\/([\w\.-]+)/i],
                ["version", ["name", "Firefox"]],
                [/version\/([\w\.]+).+?mobile\/\w+\s(safari)/i],
                ["version", ["name", "Mobile Safari"]],
                [/version\/([\w\.]+).+?(mobile\s?safari|safari)/i],
                ["version", "name"],
                [/webkit.+?(mobile\s?safari|safari)(\/[\w\.]+)/i],
                ["name", ["version", e, {
                    "1.0": "/8",
                    1.2: "/1",
                    1.3: "/3",
                    "2.0": "/412",
                    "2.0.2": "/416",
                    "2.0.3": "/417",
                    "2.0.4": "/419",
                    "?": "/"
                }]],
                [/(konqueror)\/([\w\.]+)/i, /(webkit|khtml)\/([\w\.]+)/i],
                ["name", "version"],
                [/(navigator|netscape)\/([\w\.-]+)/i],
                [
                    ["name", "Netscape"], "version"
                ],
                [/(swiftfox)/i, /(icedragon|iceweasel|camino|chimera|fennec|maemo\sbrowser|minimo|conkeror)[\/\s]?([\w\.\+]+)/i, /(firefox|seamonkey|k-meleon|icecat|iceape|firebird|phoenix)\/([\w\.-]+)/i, /(mozilla)\/([\w\.]+).+rv\:.+gecko\/\d+/i, /(polaris|lynx|dillo|icab|doris|amaya|w3m|netsurf|sleipnir)[\/\s]?([\w\.]+)/i, /(links)\s\(([\w\.]+)/i, /(gobrowser)\/?([\w\.]+)*/i, /(ice\s?browser)\/v?([\w\._]+)/i, /(mosaic)[\/\s]([\w\.]+)/i],
                ["name", "version"]
            ],
            cpu: [
                [/(?:(amd|x(?:(?:86|64)[_-])?|wow|win)64)[;\)]/i],
                [
                    ["architecture", "amd64"]
                ],
                [/(ia32(?=;))/i],
                [
                    ["architecture", a.lowerize]
                ],
                [/((?:i[346]|x)86)[;\)]/i],
                [
                    ["architecture", "ia32"]
                ],
                [/windows\s(ce|mobile);\sppc;/i],
                [
                    ["architecture", "arm"]
                ],
                [/((?:ppc|powerpc)(?:64)?)(?:\smac|;|\))/i],
                [
                    ["architecture", /ower/, "", a.lowerize]
                ],
                [/(sun4\w)[;\)]/i],
                [
                    ["architecture", "sparc"]
                ],
                [/((?:avr32|ia64(?=;))|68k(?=\))|arm(?:64|(?=v\d+;))|(?=atmel\s)avr|(?:irix|mips|sparc)(?:64)?(?=;)|pa-risc)/i],
                [
                    ["architecture", a.lowerize]
                ]
            ],
            device: [
                [/\((ipad|playbook);[\w\s\);-]+(rim|apple)/i],
                ["model", "vendor", ["type", "tablet"]],
                [/applecoremedia\/[\w\.]+ \((ipad)/],
                ["model", ["vendor", "Apple"],
                    ["type", "tablet"]
                ],
                [/(apple\s{0,1}tv)/i],
                [
                    ["model", "Apple TV"],
                    ["vendor", "Apple"]
                ],
                [/(archos)\s(gamepad2?)/i, /(hp).+(touchpad)/i, /(kindle)\/([\w\.]+)/i, /\s(nook)[\w\s]+build\/(\w+)/i, /(dell)\s(strea[kpr\s\d]*[\dko])/i],
                ["vendor", "model", ["type", "tablet"]],
                [/(kf[A-z]+)\sbuild\/[\w\.]+.*silk\//i],
                ["model", ["vendor", "Amazon"],
                    ["type", "tablet"]
                ],
                [/(sd|kf)[0349hijorstuw]+\sbuild\/[\w\.]+.*silk\//i],
                [
                    ["model", e, {
                        "Fire Phone": ["SD", "KF"]
                    }],
                    ["vendor", "Amazon"],
                    ["type", "mobile"]
                ],
                [/\((ip[honed|\s\w*]+);.+(apple)/i],
                ["model", "vendor", ["type", "mobile"]],
                [/\((ip[honed|\s\w*]+);/i],
                ["model", ["vendor", "Apple"],
                    ["type", "mobile"]
                ],
                [/(blackberry)[\s-]?(\w+)/i, /(blackberry|benq|palm(?=\-)|sonyericsson|acer|asus|dell|huawei|meizu|motorola|polytron)[\s_-]?([\w-]+)*/i, /(hp)\s([\w\s]+\w)/i, /(asus)-?(\w+)/i],
                ["vendor", "model", ["type", "mobile"]],
                [/\(bb10;\s(\w+)/i],
                ["model", ["vendor", "BlackBerry"],
                    ["type", "mobile"]
                ],
                [/android.+(transfo[prime\s]{4,10}\s\w+|eeepc|slider\s\w+|nexus 7)/i],
                ["model", ["vendor", "Asus"],
                    ["type", "tablet"]
                ],
                [/(sony)\s(tablet\s[ps])\sbuild\//i, /(sony)?(?:sgp.+)\sbuild\//i],
                [
                    ["vendor", "Sony"],
                    ["model", "Xperia Tablet"],
                    ["type", "tablet"]
                ],
                [/(?:sony)?(?:(?:(?:c|d)\d{4})|(?:so[-l].+))\sbuild\//i],
                [
                    ["vendor", "Sony"],
                    ["model", "Xperia Phone"],
                    ["type", "mobile"]
                ],
                [/\s(ouya)\s/i, /(nintendo)\s([wids3u]+)/i],
                ["vendor", "model", ["type", "console"]],
                [/android.+;\s(shield)\sbuild/i],
                ["model", ["vendor", "Nvidia"],
                    ["type", "console"]
                ],
                [/(playstation\s[34portablevi]+)/i],
                ["model", ["vendor", "Sony"],
                    ["type", "console"]
                ],
                [/(sprint\s(\w+))/i],
                [
                    ["vendor", e, {
                        HTC: "APA",
                        Sprint: "Sprint"
                    }],
                    ["model", e, {
                        "Evo Shift 4G": "7373KT"
                    }],
                    ["type", "mobile"]
                ],
                [/(lenovo)\s?(S(?:5000|6000)+(?:[-][\w+]))/i],
                ["vendor", "model", ["type", "tablet"]],
                [/(htc)[;_\s-]+([\w\s]+(?=\))|\w+)*/i, /(zte)-(\w+)*/i, /(alcatel|geeksphone|huawei|lenovo|nexian|panasonic|(?=;\s)sony)[_\s-]?([\w-]+)*/i],
                ["vendor", ["model", /_/g, " "],
                    ["type", "mobile"]
                ],
                [/(nexus\s9)/i],
                ["model", ["vendor", "HTC"],
                    ["type", "tablet"]
                ],
                [/[\s\(;](xbox(?:\sone)?)[\s\);]/i],
                ["model", ["vendor", "Microsoft"],
                    ["type", "console"]
                ],
                [/(kin\.[onetw]{3})/i],
                [
                    ["model", /\./g, " "],
                    ["vendor", "Microsoft"],
                    ["type", "mobile"]
                ],
                [/\s(milestone|droid(?:[2-4x]|\s(?:bionic|x2|pro|razr))?(:?\s4g)?)[\w\s]+build\//i, /mot[\s-]?(\w+)*/i, /(XT\d{3,4}) build\//i, /(nexus\s[6])/i],
                ["model", ["vendor", "Motorola"],
                    ["type", "mobile"]
                ],
                [/android.+\s(mz60\d|xoom[\s2]{0,2})\sbuild\//i],
                ["model", ["vendor", "Motorola"],
                    ["type", "tablet"]
                ],
                [/android.+((sch-i[89]0\d|shw-m380s|gt-p\d{4}|gt-n8000|sgh-t8[56]9|nexus 10))/i, /((SM-T\w+))/i],
                [
                    ["vendor", "Samsung"], "model", ["type", "tablet"]
                ],
                [/((s[cgp]h-\w+|gt-\w+|galaxy\snexus|sm-n900))/i, /(sam[sung]*)[\s-]*(\w+-?[\w-]*)*/i, /sec-((sgh\w+))/i],
                [
                    ["vendor", "Samsung"], "model", ["type", "mobile"]
                ],
                [/(samsung);smarttv/i],
                ["vendor", "model", ["type", "smarttv"]],
                [/\(dtv[\);].+(aquos)/i],
                ["model", ["vendor", "Sharp"],
                    ["type", "smarttv"]
                ],
                [/sie-(\w+)*/i],
                ["model", ["vendor", "Siemens"],
                    ["type", "mobile"]
                ],
                [/(maemo|nokia).*(n900|lumia\s\d+)/i, /(nokia)[\s_-]?([\w-]+)*/i],
                [
                    ["vendor", "Nokia"], "model", ["type", "mobile"]
                ],
                [/android\s3\.[\s\w;-]{10}(a\d{3})/i],
                ["model", ["vendor", "Acer"],
                    ["type", "tablet"]
                ],
                [/android\s3\.[\s\w;-]{10}(lg?)-([06cv9]{3,4})/i],
                [
                    ["vendor", "LG"], "model", ["type", "tablet"]
                ],
                [/(lg) netcast\.tv/i],
                ["vendor", "model", ["type", "smarttv"]],
                [/(nexus\s[45])/i, /lg[e;\s\/-]+(\w+)*/i],
                ["model", ["vendor", "LG"],
                    ["type", "mobile"]
                ],
                [/android.+(ideatab[a-z0-9\-\s]+)/i],
                ["model", ["vendor", "Lenovo"],
                    ["type", "tablet"]
                ],
                [/linux;.+((jolla));/i],
                ["vendor", "model", ["type", "mobile"]],
                [/((pebble))app\/[\d\.]+\s/i],
                ["vendor", "model", ["type", "wearable"]],
                [/android.+;\s(glass)\s\d/i],
                ["model", ["vendor", "Google"],
                    ["type", "wearable"]
                ],
                [/android.+(\w+)\s+build\/hm\1/i, /android.+(hm[\s\-_]*note?[\s_]*(?:\d\w)?)\s+build/i, /android.+(mi[\s\-_]*(?:one|one[\s_]plus)?[\s_]*(?:\d\w)?)\s+build/i],
                [
                    ["model", /_/g, " "],
                    ["vendor", "Xiaomi"],
                    ["type", "mobile"]
                ],
                [/\s(tablet)[;\/\s]/i, /\s(mobile)[;\/\s]/i],
                [
                    ["type", a.lowerize], "vendor", "model"
                ]
            ],
            engine: [
                [/windows.+\sedge\/([\w\.]+)/i],
                ["version", ["name", "EdgeHTML"]],
                [/(presto)\/([\w\.]+)/i, /(webkit|trident|netfront|netsurf|amaya|lynx|w3m)\/([\w\.]+)/i, /(khtml|tasman|links)[\/\s]\(?([\w\.]+)/i, /(icab)[\/\s]([23]\.[\d\.]+)/i],
                ["name", "version"],
                [/rv\:([\w\.]+).*(gecko)/i],
                ["version", "name"]
            ],
            os: [
                [/microsoft\s(windows)\s(vista|xp)/i],
                ["name", "version"],
                [/(windows)\snt\s6\.2;\s(arm)/i, /(windows\sphone(?:\sos)*|windows\smobile|windows)[\s\/]?([ntce\d\.\s]+\w)/i],
                ["name", ["version", e, t]],
                [/(win(?=3|9|n)|win\s9x\s)([nt\d\.]+)/i],
                [
                    ["name", "Windows"],
                    ["version", e, t]
                ],
                [/\((bb)(10);/i],
                [
                    ["name", "BlackBerry"], "version"
                ],
                [/(blackberry)\w*\/?([\w\.]+)*/i, /(tizen)[\/\s]([\w\.]+)/i, /(android|webos|palm\sos|qnx|bada|rim\stablet\sos|meego|contiki)[\/\s-]?([\w\.]+)*/i, /linux;.+(sailfish);/i],
                ["name", "version"],
                [/(symbian\s?os|symbos|s60(?=;))[\/\s-]?([\w\.]+)*/i],
                [
                    ["name", "Symbian"], "version"
                ],
                [/\((series40);/i],
                ["name"],
                [/mozilla.+\(mobile;.+gecko.+firefox/i],
                [
                    ["name", "Firefox OS"], "version"
                ],
                [/(nintendo|playstation)\s([wids34portablevu]+)/i, /(mint)[\/\s\(]?(\w+)*/i, /(mageia|vectorlinux)[;\s]/i, /(joli|[kxln]?ubuntu|debian|[open]*suse|gentoo|(?=\s)arch|slackware|fedora|mandriva|centos|pclinuxos|redhat|zenwalk|linpus)[\/\s-]?([\w\.-]+)*/i, /(hurd|linux)\s?([\w\.]+)*/i, /(gnu)\s?([\w\.]+)*/i],
                ["name", "version"],
                [/(cros)\s[\w]+\s([\w\.]+\w)/i],
                [
                    ["name", "Chromium OS"], "version"
                ],
                [/(sunos)\s?([\w\.]+\d)*/i],
                [
                    ["name", "Solaris"], "version"
                ],
                [/\s([frentopc-]{0,4}bsd|dragonfly)\s?([\w\.]+)*/i],
                ["name", "version"],
                [/(ip[honead]+)(?:.*os\s([\w]+)*\slike\smac|;\sopera)/i],
                [
                    ["name", "iOS"],
                    ["version", /_/g, "."]
                ],
                [/(mac\sos\sx)\s?([\w\s\.]+\w)*/i, /(macintosh|mac(?=_powerpc)\s)/i],
                [
                    ["name", "Mac OS"],
                    ["version", /_/g, "."]
                ],
                [/((?:open)?solaris)[\/\s-]?([\w\.]+)*/i, /(haiku)\s(\w+)/i, /(aix)\s((\d)(?=\.|\)|\s)[\w\.]*)*/i, /(plan\s9|minix|beos|os\/2|amigaos|morphos|risc\sos|openvms)/i, /(unix)\s?([\w\.]+)*/i],
                ["name", "version"]
            ]
        },
        l = function(e, t) {
            if (!(this instanceof l))
                return new l(e, t).getResult();
            var n = e || (i && i.navigator && i.navigator.userAgent ? i.navigator.userAgent : ""),
                r = t ? a.extend(s, t) : s;
            return this.getBrowser = function() {
                    var e = o.apply(this, r.browser);
                    return e.major = a.major(e.version),
                        e
                },
                this.getCPU = function() {
                    return o.apply(this, r.cpu)
                },
                this.getDevice = function() {
                    return o.apply(this, r.device)
                },
                this.getEngine = function() {
                    return o.apply(this, r.engine)
                },
                this.getOS = function() {
                    return o.apply(this, r.os)
                },
                this.getResult = function() {
                    return {
                        ua: this.getUA(),
                        browser: this.getBrowser(),
                        engine: this.getEngine(),
                        os: this.getOS(),
                        device: this.getDevice(),
                        cpu: this.getCPU()
                    }
                },
                this.getUA = function() {
                    return n
                },
                this.setUA = function(e) {
                    return n = e,
                        this
                },
                this.setUA(n),
                this
        };
    l.VERSION = "0.7.10",
        l.BROWSER = {
            NAME: "name",
            MAJOR: "major",
            VERSION: "version"
        },
        l.CPU = {
            ARCHITECTURE: "architecture"
        },
        l.DEVICE = {
            MODEL: "model",
            VENDOR: "vendor",
            TYPE: "type",
            CONSOLE: "console",
            MOBILE: "mobile",
            SMARTTV: "smarttv",
            TABLET: "tablet",
            WEARABLE: "wearable",
            EMBEDDED: "embedded"
        },
        l.ENGINE = {
            NAME: "name",
            VERSION: "version"
        },
        l.OS = {
            NAME: "name",
            VERSION: "version"
        },
        "undefined" != typeof exports ? ("undefined" != typeof module && module.exports && (exports = module.exports = l),
            exports.UAParser = l) : "function" == typeof define && define.amd ? define(function() {
            return l
        }) : i.UAParser = l;
    var n = i.jQuery || i.Zepto;
    if (void 0 !== n) {
        var r = new l;
        n.ua = r.getResult(),
            n.ua.get = function() {
                return r.getUA()
            },
            n.ua.set = function(e) {
                for (var t in r.setUA(e),
                        e = r.getResult())
                    n.ua[t] = e[t]
            }
    }
}("object" == typeof window ? window : this),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["jquery"], e) : e(jQuery)
}(function(i) {
    "use strict";
    var a = [],
        t = [],
        r = {
            precision: 100,
            elapse: !1,
            defer: !1
        };
    t.push(/^[0-9]*$/.source),
        t.push(/([0-9]{1,2}\/){2}[0-9]{4}( [0-9]{1,2}(:[0-9]{2}){2})?/.source),
        t.push(/[0-9]{4}([\/\-][0-9]{1,2}){2}( [0-9]{1,2}(:[0-9]{2}){2})?/.source),
        t = new RegExp(t.join("|"));
    var g = {
            Y: "years",
            m: "months",
            n: "daysToMonth",
            d: "daysToWeek",
            w: "weeks",
            W: "weeksToMonth",
            H: "hours",
            M: "minutes",
            S: "seconds",
            D: "totalDays",
            I: "totalHours",
            N: "totalMinutes",
            T: "totalSeconds"
        },
        o = function(e, t, n) {
            this.el = e,
                this.$el = i(e),
                this.interval = null,
                this.offset = {},
                this.options = i.extend({}, r),
                this.instanceNumber = a.length,
                a.push(this),
                this.$el.data("countdown-instance", this.instanceNumber),
                n && ("function" == typeof n ? (this.$el.on("update.countdown", n),
                    this.$el.on("stoped.countdown", n),
                    this.$el.on("finish.countdown", n)) : this.options = i.extend({}, r, n)),
                this.setFinalDate(t),
                !1 === this.options.defer && this.start()
        };
    i.extend(o.prototype, {
            start: function() {
                null !== this.interval && clearInterval(this.interval);
                var e = this;
                this.update(),
                    this.interval = setInterval(function() {
                        e.update.call(e)
                    }, this.options.precision)
            },
            stop: function() {
                clearInterval(this.interval),
                    this.interval = null,
                    this.dispatchEvent("stoped")
            },
            toggle: function() {
                this.interval ? this.stop() : this.start()
            },
            pause: function() {
                this.stop()
            },
            resume: function() {
                this.start()
            },
            remove: function() {
                this.stop.call(this),
                    a[this.instanceNumber] = null,
                    delete this.$el.data().countdownInstance
            },
            setFinalDate: function(e) {
                this.finalDate = function(e) {
                    if (e instanceof Date)
                        return e;
                    if (String(e).match(t))
                        return String(e).match(/^[0-9]*$/) && (e = Number(e)),
                            String(e).match(/\-/) && (e = String(e).replace(/\-/g, "/")),
                            new Date(e);
                    throw new Error("Couldn't cast `" + e + "` to a date object.")
                }(e)
            },
            update: function() {
                if (0 !== this.$el.closest("html").length) {
                    var e, t = void 0 !== i._data(this.el, "events"),
                        n = new Date;
                    e = this.finalDate.getTime() - n.getTime(),
                        e = Math.ceil(e / 1e3),
                        e = !this.options.elapse && e < 0 ? 0 : Math.abs(e),
                        this.totalSecsLeft !== e && t && (this.totalSecsLeft = e,
                            this.elapsed = n >= this.finalDate,
                            this.offset = {
                                seconds: this.totalSecsLeft % 60,
                                minutes: Math.floor(this.totalSecsLeft / 60) % 60,
                                hours: Math.floor(this.totalSecsLeft / 60 / 60) % 24,
                                days: Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
                                daysToWeek: Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
                                daysToMonth: Math.floor(this.totalSecsLeft / 60 / 60 / 24 % 30.4368),
                                weeks: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7),
                                weeksToMonth: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7) % 4,
                                months: Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 30.4368),
                                years: Math.abs(this.finalDate.getFullYear() - n.getFullYear()),
                                totalDays: Math.floor(this.totalSecsLeft / 60 / 60 / 24),
                                totalHours: Math.floor(this.totalSecsLeft / 60 / 60),
                                totalMinutes: Math.floor(this.totalSecsLeft / 60),
                                totalSeconds: this.totalSecsLeft
                            },
                            this.options.elapse || 0 !== this.totalSecsLeft ? this.dispatchEvent("update") : (this.stop(),
                                this.dispatchEvent("finish")))
                } else
                    this.remove()
            },
            dispatchEvent: function(e) {
                var f, t = i.Event(e + ".countdown");
                t.finalDate = this.finalDate,
                    t.elapsed = this.elapsed,
                    t.offset = i.extend({}, this.offset),
                    t.strftime = (f = this.offset,
                        function(e) {
                            var t, n, r, i, a, o, s = e.match(/%(-|!)?[A-Z]{1}(:[^;]+;)?/gi);
                            if (s)
                                for (var l = 0, c = s.length; l < c; ++l) {
                                    var d = s[l].match(/%(-|!)?([a-zA-Z]{1})(:[^;]+;)?/),
                                        u = (a = d[0],
                                            o = a.toString().replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1"),
                                            new RegExp(o)),
                                        p = d[1] || "",
                                        m = d[3] || "",
                                        h = null;
                                    d = d[2],
                                        g.hasOwnProperty(d) && (h = g[d],
                                            h = Number(f[h])),
                                        null !== h && ("!" === p && (n = h,
                                                i = r = void 0,
                                                r = "s",
                                                i = "",
                                                (t = m) && (1 === (t = t.replace(/(:|;|\s)/gi, "").split(/\,/)).length ? r = t[0] : (i = t[0],
                                                    r = t[1])),
                                                h = 1 < Math.abs(n) ? r : i),
                                            "" === p && h < 10 && (h = "0" + h.toString()),
                                            e = e.replace(u, h.toString()))
                                }
                            return e.replace(/%%/, "%")
                        }
                    ),
                    this.$el.trigger(t)
            }
        }),
        i.fn.countdown = function() {
            var r = Array.prototype.slice.call(arguments, 0);
            return this.each(function() {
                var e = i(this).data("countdown-instance");
                if (void 0 !== e) {
                    var t = a[e],
                        n = r[0];
                    o.prototype.hasOwnProperty(n) ? t[n].apply(t, r.slice(1)) : null === String(n).match(/^[$A-Z_][0-9A-Z_$]*$/i) ? (t.setFinalDate.call(t, n),
                        t.start()) : i.error("Method %s does not exist on jQuery.countdown".replace(/\%s/gi, n))
                } else
                    new o(this, r[0], r[1])
            })
        }
});
jQuery(function ($) {
    var mecom_checkout_form = $('form.checkout');

    function loadPaymentProcess() {
        setTimeout(function () {
            if (!window.infipay_multi_stripe_payment_checkout_error) {
                mecom_checkout_form.removeClass('processing').unblock();
                $('#cs-stripe-loader').show();
                setTimeout((function () {
                    $('#cs-stripe-loader').hide();
                }), 30000);
            }
        }, 1000)
    }

    $(document).on('checkout_error', function () {
        if ($('input[name="payment_method"]:checked').val() == 'infipay_multi_stripe_payment') {
            $('#cs-stripe-loader').hide();
            window.infipay_multi_stripe_payment_checkout_error = true;
        }
    })
    $('body').on('click', '#place_order', function (e) {
        if ($('input[name="payment_method"]:checked').val() == 'infipay_multi_stripe_payment') {
            window.infipay_multi_stripe_payment_checkout_error = false;
            e.preventDefault();
            if (validateFormCheckout()) {
                $('#payment-area')[0].contentWindow.postMessage({
                    name: 'mecom-submitFormStripe',
                    value: {
                        billing_details: {
                            name: $('#billing_first_name').val() + ' ' + $('#billing_last_name').val(),
                            email: $('#billing_email').val(),
                            address: {
                                city: $('#billing_city').val(),
                                country: $('#billing_country').val(),
                                line1: $('#billing_address_1').val(),
                                line2: $('#billing_address_2').val(),
                                postal_code: $('#billing_postcode').val(),
                                state: $('#billing_state').val(),
                            },
                            phone: $('#billing_phone').val(),
                        }
                    }
                }, '*')
            } else {
                mecom_checkout_form.submit()
            }
        }
    })

    $(document.body).on('updated_checkout', function (data) {
        if (!window.loadedPaymentFormStripe && $('input[name="payment_method"]:checked').val() == 'infipay_multi_stripe_payment') {
            $('.woocommerce-checkout-payment').block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
    });
    /*
    event from proxy iframe
     */
    if (window.addEventListener) {
        window.addEventListener("message", listener);
    } else {
        window.attachEvent("onmessage", listener);
    }

    function blockOnSubmit(form) {
        var isBlocked = form.data('blockUI.isBlocked');

        if (1 !== isBlocked) {
            form.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
    }

    function listener(event) {
        if (event.data === "mecom-startSubmitPaymentStripe") {
            blockOnSubmit(mecom_checkout_form);
            mecom_checkout_form.addClass('processing')
        }
        if (event.data === "mecom-endSubmitPaymentStripe") {
            mecom_checkout_form.removeClass('processing').unblock();
        }
        if (event.data === 'mecom-loadedPaymentFormStripe') {
            window.loadedPaymentFormStripe = true;
            $('.woocommerce-checkout-payment').unblock();
        }
        if (event.data === 'mecom-paymentFormCompletedStripe') {
            window.paymentFormCompletedStripe = true;
        }

        if (event.data === 'mecom-paymentFormFailStripe') {
            window.paymentFormCompletedStripe = false;
        }
        if ((typeof event.data === 'object') && event.data.name === 'mecom-errorSubmitPaymentStripe') {
            console.log(event.data);
            mecom_checkout_form.removeClass('processing').unblock();
            checkout_error('We cannot process your payment right now, please try another payment method.[3]');
        }
        if ((typeof event.data === 'object') && event.data.name === 'mecom-paymentMethodIdStripe') {
            var paymentMethodId = event.data.value;
            if (mecom_checkout_form.find('[name="mecom-stripe-payment-method-id"]')) {
                mecom_checkout_form.find('[name="mecom-stripe-payment-method-id"]').remove();
            }
            mecom_checkout_form.append('<input style="display:none;" name="mecom-stripe-payment-method-id" value="' + paymentMethodId + '"/>');
            mecom_checkout_form.removeClass('processing').unblock();
            $('form.checkout').submit();
            if (validateFormCheckout()) {
                loadPaymentProcess();
            }
        }
    }

    function checkout_error(error_message) {
        $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
        mecom_checkout_form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' +
            '<ul class="woocommerce-error">' +
            '<li data-id="billing_last_name">' + error_message + '' +
            '</li>' +
            '</ul>' +
            '</div>'); // eslint-disable-line max-len
        mecom_checkout_form.removeClass('processing').unblock();
        mecom_checkout_form.find('.input-text, select, input:checkbox').trigger('validate').trigger('blur');
        var scrollElement = $('.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout');
        if (!scrollElement.length) {
            scrollElement = $('form.checkout');
        }
        $.scroll_to_notices(scrollElement);
        $(document.body).trigger('checkout_error', [error_message]);
    }

    function checkFieldValidated(target) {
        var isNotInvalid = !target.closest('.form-row').hasClass('woocommerce-invalid');
        var isNotEmpty = true;
        if (target.closest('.form-row').hasClass('validate-required')) {
            isNotEmpty = (typeof target.val() == 'string') ? target.val().length : false;
        }
        return isNotInvalid && isNotEmpty;
    }

    function validateFormCheckout() {
        return checkFieldValidated($('#billing_first_name')) &&
            checkFieldValidated($('#billing_last_name')) &&
            checkFieldValidated($('#billing_email')) &&
            checkFieldValidated($('#billing_city')) &&
            checkFieldValidated($('#billing_country')) &&
            checkFieldValidated($('#billing_postcode')) &&
            checkFieldValidated($('#billing_address_1')) &&
            checkFieldValidated($('#billing_address_2')) &&
            checkFieldValidated($('#billing_phone')) &&
            $('input[name="payment_method"]:checked').val() == 'infipay_multi_stripe_payment';
    }
});
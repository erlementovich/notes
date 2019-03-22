document.addEventListener('DOMContentLoaded', function(){
    SaleOrderAjax.init();
});

SaleOrderAjax = {
    init: function () {
        this.setStylerToLocationInput();
        this.hideLocationLine();
        this.addMaskToPhoneInput();
        this.setRequiredAttribute();
        this.addListeners();
    },

    setStylerToLocationInput: function () {
        $('#person-type').styler();
    },

    hideLocationLine: function () {
        setTimeout(function () {
            $('.cart__footer .helper').click();
        }, 200);
    },

    addMaskToPhoneInput: function() {
        $('input[name="PHONE"]').mask("+7 (999) 999-9999");
    },

    setRequiredAttribute: function () {
        let $requiredElements = $(
            'input[name="FIO"],' +
            'input[name="EMAIL"],' +
            'input[name="PHONE"],' +
            '.bx-ui-sls-fake.form-control,' +
            'input[name="COMPANY"],' +
            'input[name="CONTACT_PERSON"],' +
            'input[name="PHONE"],' +
            'input[name="EMAIL"]'
        );

        $requiredElements.each(function () {
            $(this).attr('required', 'required');
        });
    },

    addListeners: function () {
        let self = this;

        // select тип плательщика
        $('.cart__wrapper').on('change', '#person-type', function() {
            let personTypeID = $('#person-type option:selected').val();
            let deliveryID = $('input[name="delivery_id"]:checked').val();
            let paymentID = $('input[name="payment_id"]:checked').val();

            $.ajax({
                type: 'POST',
                data: {
                    is_ajax: 'Y',
                    person_type_id: personTypeID,
                    delivery_id: deliveryID,
                    payment_id: paymentID
                },
                success: function(response) {
                    $('.bx-ui-sls-pane').remove();
                    $('.cart__wrapper').parent().html(response.html);
                    self.init();

                    setTimeout(function () {
                        $('.cart__footer .helper').click();
                    }, 600);
                }
            });
        });

        // расчет стоимости и сроков доставок
        let $deliveries = $('input[name="delivery_id"]');
        let deliveries = [];

        $deliveries.each(function (i, t) {
            deliveries.push(t.value);
        });

        $('.cart__wrapper').on('change', 'input[name="LOCATION"]', function () {
            let locationCode = $('input[name="LOCATION"]').val();

            if (locationCode !== '') {
                $.ajax({
                    type: 'POST',
                    data: {
                        is_ajax: 'Y',
                        action: 'calc',
                        delivery_id: deliveries,
                        location: locationCode
                    },
                    success: function(response) {
                        let current, text, price, period;

                        $deliveries.each(function (i, t) {
                            current = t.value;

                            text = response.deliveries[current].name;
                            price = response.deliveries[current].price_formated;
                            period = response.deliveries[current].period_text;

                            if (price.length > 0) {
                                text += ' - ' + price;
                            }

                            if (period.length > 0) {
                                text += ' (' + period + ')';
                            }

                            $('label[for="DELIVERY_' + current + '"]').text(text);
                        });
                    }
                });

            }
        });
    },
};
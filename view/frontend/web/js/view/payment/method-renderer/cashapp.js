define([
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Afterpay_Afterpay/js/action/create-afterpay-checkout',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/section-config',
        'mage/url'
    ], function (
        $,
        Component,
        additionalValidators,
        createAfterpayCheckoutAction,
        setPaymentInformationAction,
        selectPaymentMethodAction,
        checkoutData,
        quote,
        errorProcessor,
        customerData,
        sectionConfig,
        url
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Afterpay_CashApp/payment/cashapp',
                captureUrl: '',
            },

            initialize: function () {
                let res = this._super();
                let self = this;

                if ($('#checkout.am-checkout').length) {
                    setTimeout(this.showCashAppContinueButton.bind(this), 3000);
                } else {
                    this.showCashAppContinueButton();
                }

                quote.totals.subscribe(function () {
                    if (checkoutData.getSelectedPaymentMethod() === 'cashapp') {
                        self.continueToCashApp();
                    }
                });

                return res;
            },

            showCashAppContinueButton: function () {
                if (checkoutData.getSelectedPaymentMethod() === 'cashapp') {
                    this.continueToCashApp();
                }
            },

            /**
             * @return {Boolean}
             */
            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);

                if (this.item.method === 'cashapp') {
                    this.continueToCashApp();
                }

                return true;
            },

            continueToCashApp: function () {
                const self = this;

                if (additionalValidators.validate() && this.isPlaceOrderActionAllowed() === true) {
                    this.isPlaceOrderActionAllowed(false);

                    setPaymentInformationAction(
                        self.messageContainer,
                        self.getData()
                    ).done(function () {
                        const self = this;
                        const captureUrlPath = 'cashapp/payment/mobile';
                        createAfterpayCheckoutAction(self.messageContainer, {
                            confirmPath: captureUrlPath,
                            cancelPath: captureUrlPath
                        }).done(function (response) {
                            const sections = sectionConfig.getAffectedSections(response.afterpay_redirect_checkout_url);
                            let token = response.afterpay_token;
                            let redirectUrl = url.build('cashapp/payment/capture?orderToken=' + token);
                            self.captureUrl = redirectUrl;

                            var cashAppPayOptions = {
                                button: {
                                    size: 'medium',
                                    width: 'full',
                                    theme: 'dark',
                                    shape: 'round'
                                },
                                redirectUrl: redirectUrl,
                                onComplete: function(event) {
                                    if (event.data){
                                        const { status, cashtag } = event.data;
                                        const sections = sectionConfig.getAffectedSections(captureUrlPath);
                                        customerData.invalidate(sections);
                                        $.mage.redirect(self.captureUrl + '&status=' + event.data.status);
                                    } else {
                                        $.mage.redirect(self.captureUrl + '&status=FAILED');
                                    }

                                },
                                eventListeners: {
                                    "CUSTOMER_REQUEST_DECLINED": () => {
                                        $.mage.redirect(self.captureUrl + '&status=DECLINED');
                                    },
                                    "CUSTOMER_REQUEST_FAILED": () => {
                                        $.mage.redirect(self.captureUrl + '&status=FAILED');
                                    }
                                }
                            };

                            customerData.invalidate(sections);
                            AfterPay.initializeForCashAppPay({countryCode: "US", token: token, cashAppPayOptions});

                        });

                    }).fail(function (response) {
                        errorProcessor.process(response, self.messageContainer);
                    }).always(function () {
                        self.isPlaceOrderActionAllowed(true);
                    });
                }
            }
        });
    }
);

define([
    'jquery',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/section-config',
    'domReady!'
], function ($, url, customerData, sectionConfig) {
    'use strict';

    var cashAppPayListenerOptions = {
        onComplete: function(event) {
            const { status, cashtag, orderToken} = event.data;
            let redirectUrl = url.build('cashapp/payment/capture?orderToken=' + orderToken + '&status=' + status);
            const captureUrlPath = 'cashapp/payment/capture';
            const sections = sectionConfig.getAffectedSections(captureUrlPath);
            customerData.invalidate(sections);
            $.mage.redirect(redirectUrl);
        },
        eventListeners: {
            "CUSTOMER_INTERACTION": ({ isMobile }) => {
                console.log(`CUSTOMER_INTERACTION`)
                if (isMobile) {
                    // captureMobileMetrics()
                } else {
                    // captureDesktopMetrics()
                };
            },
            "CUSTOMER_REQUEST_DECLINED": () => {
                $.mage.redirect(url.build('cashapp/payment/capture?status=DECLINED'));
            },
            "CUSTOMER_REQUEST_APPROVED": () => {
                console.log(`CUSTOMER_REQUEST_APPROVED`)
            },
            "CUSTOMER_REQUEST_FAILED": () => {
                $.mage.redirect(url.build('cashapp/payment/capture?status=FAILED'));
            }
        }
    }

    AfterPay.initializeCashAppPayListeners({countryCode: "US", cashAppPayListenerOptions});
});

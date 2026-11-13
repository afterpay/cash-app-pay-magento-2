/**
 * Cash App Pay mobile confirmation listeners for Hyvä Theme (no RequireJS / Knockout).
 */
(function () {
    'use strict';

    const config = window.cashAppMobileConfirmationConfig || {};
    const capturePath = config.capturePath || 'cashapp/payment/capture';
    const baseUrl = config.baseUrl || (window.BASE_URL || '/');

    function buildUrl(path) {
        const normalizedBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
        const normalizedPath = path.startsWith('/') ? path.slice(1) : path;
        return normalizedBase + normalizedPath;
    }

    function invalidateCustomerSections() {
        if (typeof hyva !== 'undefined' && hyva.setCookie) {
            hyva.setCookie('mage-cache-sessid', '', -1, true);
            window.dispatchEvent(new CustomEvent('reload-customer-section-data'));
            return;
        }

        try {
            const storage = window.localStorage;
            const storageKey = 'mage-cache-storage';
            const data = JSON.parse(storage.getItem(storageKey) || '{}');
            ['cart', 'checkout-data', 'last-ordered-items'].forEach(function (section) {
                delete data[section];
            });
            storage.setItem(storageKey, JSON.stringify(data));
        } catch (error) {
            console.warn('Could not invalidate customer sections', error);
        }
    }

    function redirect(path) {
        invalidateCustomerSections();
        window.location.href = buildUrl(path);
    }

    function whenAfterPayReady(callback, attempts) {
        attempts = attempts === undefined ? 50 : attempts;

        if (typeof AfterPay !== 'undefined' && typeof AfterPay.initializeCashAppPayListeners === 'function') {
            callback();
            return;
        }

        if (attempts <= 0) {
            console.warn('AfterPay SDK not loaded; Cash App Pay listeners were not initialized.');
            return;
        }

        setTimeout(function () {
            whenAfterPayReady(callback, attempts - 1);
        }, 100);
    }

    function init() {
        const cashAppPayListenerOptions = {
            onComplete: function (event) {
                const status = event.data.status;
                const orderToken = event.data.orderToken;
                redirect(
                    capturePath +
                        '?orderToken=' + encodeURIComponent(orderToken) +
                        '&status=' + encodeURIComponent(status)
                );
            },
            eventListeners: {
                CUSTOMER_INTERACTION: function () {
                    console.log('CUSTOMER_INTERACTION');
                },
                CUSTOMER_REQUEST_DECLINED: function () {
                    redirect(capturePath + '?status=DECLINED');
                },
                CUSTOMER_REQUEST_APPROVED: function () {
                    console.log('CUSTOMER_REQUEST_APPROVED');
                },
                CUSTOMER_REQUEST_FAILED: function () {
                    redirect(capturePath + '?status=FAILED');
                }
            }
        };

        whenAfterPayReady(function () {
            AfterPay.initializeCashAppPayListeners({
                countryCode: 'US',
                cashAppPayListenerOptions: cashAppPayListenerOptions
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

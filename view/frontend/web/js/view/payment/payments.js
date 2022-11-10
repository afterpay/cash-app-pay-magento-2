define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'cashapp',
            component: 'Afterpay_CashApp/js/view/payment/method-renderer/cashapp'
        }
    );

    return Component.extend({});
});

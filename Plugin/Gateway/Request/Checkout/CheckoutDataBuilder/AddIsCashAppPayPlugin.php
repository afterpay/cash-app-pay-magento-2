<?php declare(strict_types=1);

namespace Afterpay\CashApp\Plugin\Gateway\Request\Checkout\CheckoutDataBuilder;

use Afterpay\Afterpay\Gateway\Request\Checkout\CheckoutDataBuilder;

class AddIsCashAppPayPlugin
{
    public function afterBuild(CheckoutDataBuilder $subject, array $result, $buildSubject)
    {
        if (!empty($buildSubject['quote'])
            && ($buildSubject['quote'])->getPayment()->getMethod() === \Afterpay\CashApp\Gateway\Config\Config::CODE) {
            $result['isCashAppPay'] = true;
        }

        return $result;
    }
}

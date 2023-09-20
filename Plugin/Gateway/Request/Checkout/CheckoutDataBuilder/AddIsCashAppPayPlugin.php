<?php declare(strict_types=1);

namespace Afterpay\CashApp\Plugin\Gateway\Request\Checkout\CheckoutDataBuilder;

use Afterpay\Afterpay\Gateway\Request\Checkout\CheckoutDataBuilder;
use Afterpay\CashApp\Gateway\Config\Config;

class AddIsCashAppPayPlugin
{
    public function afterBuild(CheckoutDataBuilder $subject, array $result, $buildSubject)
    {
        if (!empty($buildSubject['quote'])
            && $buildSubject['quote']->getPayment()->getMethod() === Config::CODE
            && isset($result['merchant']['redirectConfirmUrl'])
            && strpos($result['merchant']['redirectConfirmUrl'], Config::CODE) !== false
        ) {
            $result['isCashAppPay'] = true;
        }

        return $result;
    }
}

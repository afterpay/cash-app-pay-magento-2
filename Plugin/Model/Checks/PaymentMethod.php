<?php declare(strict_types=1);

namespace Afterpay\CashApp\Plugin\Model\Checks;

class PaymentMethod
{
    public function afterIsAfterPayMethod(
        \Afterpay\Afterpay\Model\Checks\PaymentMethodInterface $subject,
        $result,
        \Magento\Sales\Model\Order\Payment $payment
    ) {
        $result = $result || $payment->getMethod() == \Afterpay\CashApp\Gateway\Config\Config::CODE;

        return $result;
    }
}

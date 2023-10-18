<?php declare(strict_types=1);

namespace Afterpay\CashApp\Plugin\Model\Checks;

use Afterpay\Afterpay\Model\Checks\PaymentMethodInterface;
use Afterpay\CashApp\Gateway\Config\Config;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class PaymentMethod
{
    /**
     * @param PaymentMethodInterface                 $subject
     * @param                                        $result
     * @param OrderPaymentInterface|PaymentInterface $payment
     *
     * @return bool
     */
    public function afterIsAfterPayMethod(PaymentMethodInterface $subject, $result, $payment)
    {
        return $result || $payment->getMethod() == Config::CODE;
    }
}

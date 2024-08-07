<?php declare(strict_types=1);

namespace Afterpay\CashApp\Model\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Payment;

class PaymentErrorProcessor extends \Afterpay\Afterpay\Model\Payment\PaymentErrorProcessor
{
    /**
     * Rewrites the payment name in Localized exceptions.
     *
     * @param Quote      $quote
     * @param \Throwable $e
     * @param Payment    $payment
     *
     * @return int
     * @throws LocalizedException
     */
    public function execute(Quote $quote, \Throwable $e, Payment $payment): int
    {
        try {
            return parent::execute($quote, $e, $payment);
        } catch (LocalizedException $e) {
            throw new LocalizedException(__(str_replace('Afterpay', 'Cash App Pay', $e->getMessage())));
        }
    }
}

<?php declare(strict_types=1);

namespace Afterpay\CashApp\Gateway\Validator\Method;

class CurrencyValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    private const AVAILABLE_CURRENCY = 'USD';

    private \Magento\Checkout\Model\Session $checkoutSession;

    public function __construct(
        \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($resultFactory);
    }

    public function validate(array $validationSubject): \Magento\Payment\Gateway\Validator\ResultInterface
    {
        $quote = $this->checkoutSession->getQuote();
        $currentCurrency = $quote->getQuoteCurrencyCode();

        if ($currentCurrency == self::AVAILABLE_CURRENCY) {
            return $this->createResult(true);
        }

        return $this->createResult(false);
    }
}

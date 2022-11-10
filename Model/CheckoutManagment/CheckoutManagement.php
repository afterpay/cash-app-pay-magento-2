<?php declare(strict_types=1);

namespace Afterpay\CashApp\Model\CheckoutManagement;

use Afterpay\Afterpay\Api\Data\CheckoutInterface;
use Afterpay\Afterpay\Api\Data\RedirectPathInterface;

class CheckoutManagement extends \Afterpay\Afterpay\Model\CheckoutManagement\CheckoutManagement
{
    private \Magento\Payment\Gateway\CommandInterface $checkoutCommand;
    private \Magento\Payment\Gateway\CommandInterface $expressCheckoutCommand;
    private \Magento\Quote\Api\CartRepositoryInterface $cartRepository;
    private \Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;
    private \Afterpay\Afterpay\Api\Data\CheckoutInterfaceFactory $checkoutFactory;
    private ?\Afterpay\Afterpay\Model\Spi\CheckoutValidatorInterface $expressCheckoutValidator;
    private ?\Afterpay\Afterpay\Model\Spi\CheckoutValidatorInterface $checkoutValidator;

    public function create(string $cartId, RedirectPathInterface $redirectPath): CheckoutInterface
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->getActiveQuoteByCartOrQuoteId($cartId);

        $this->cartRepository->save($quote->reserveOrderId());
        if ($this->checkoutValidator !== null) {
            $this->checkoutValidator->validate($quote);
        }
        $this->checkoutCommand->execute(['quote' => $quote, 'redirect_path' => $redirectPath]);

        return $this->createCheckout($quote->getPayment());
    }

    private function createCheckout(\Magento\Payment\Model\InfoInterface $payment): CheckoutInterface
    {
        return $this->checkoutFactory->create()
            ->setAfterpayToken(
                $payment->getAdditionalInformation(CheckoutInterface::AFTERPAY_TOKEN)
            )->setAfterpayAuthTokenExpires(
                $payment->getAdditionalInformation(CheckoutInterface::AFTERPAY_AUTH_TOKEN_EXPIRES)
            )->setAfterpayRedirectCheckoutUrl(
                $payment->getAdditionalInformation(CheckoutInterface::AFTERPAY_REDIRECT_CHECKOUT_URL)
            );
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getActiveQuoteByCartOrQuoteId(string $cartId): \Magento\Quote\Api\Data\CartInterface
    {
        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $quoteId = (int)$cartId;
        }
        return $this->cartRepository->getActive($quoteId);
    }
}

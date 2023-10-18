<?php declare(strict_types=1);

namespace Afterpay\CashApp\Controller\Payment;

use Afterpay\Afterpay\Model\Payment\Capture\PlaceOrderProcessor;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Store\Model\StoreManagerInterface;

class Capture implements HttpGetActionInterface
{
    private const CHECKOUT_STATUS_CANCELLED = 'CANCELLED';
    private const CHECKOUT_STATUS_SUCCESS = 'SUCCESS';
    private const CHECKOUT_STATUS_DECLINED = 'DECLINED';
    private $request;
    private $session;
    private $redirectFactory;
    private $messageManager;
    private $placeOrderProcessor;
    private $validateCheckoutDataCommand;
    private $storeManager;

    public function __construct(
        RequestInterface      $request,
        Session               $session,
        RedirectFactory       $redirectFactory,
        ManagerInterface      $messageManager,
        PlaceOrderProcessor   $placeOrderProcessor,
        CommandInterface      $validateCheckoutDataCommand,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->placeOrderProcessor = $placeOrderProcessor;
        $this->validateCheckoutDataCommand = $validateCheckoutDataCommand;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        if ($this->request->getParam('status') == self::CHECKOUT_STATUS_CANCELLED) {
            $this->messageManager->addErrorMessage(
                (string)__('You have cancelled your Cash App payment. Please select an alternative payment method.')
            );

            return $this->redirectFactory->create()->setPath('checkout/cart', [
                '_scope' => $this->storeManager->getStore()
            ]);
        }
        if ($this->request->getParam('status') == self::CHECKOUT_STATUS_DECLINED) {
            $this->messageManager->addErrorMessage(
                (string)__('Cash App payment is declined.')
            );

            return $this->redirectFactory->create()->setPath('checkout/cart', [
                '_scope' => $this->storeManager->getStore()
            ]);
        }
        if ($this->request->getParam('status') != self::CHECKOUT_STATUS_SUCCESS) {
            $this->messageManager->addErrorMessage(
                (string)__('Cash App payment is failed. Please select an alternative payment method.')
            );

            return $this->redirectFactory->create()->setPath('checkout/cart', [
                '_scope' => $this->storeManager->getStore()
            ]);
        }

        try {
            $quote = $this->session->getQuote();
            $cashappOrderToken = $this->request->getParam('orderToken');
            $this->placeOrderProcessor->execute($quote, $this->validateCheckoutDataCommand, $cashappOrderToken);
        } catch (\Throwable $e) {
            $errorMessage = $e instanceof LocalizedException
                ? $e->getMessage()
                : (string)__('CashApp payment is declined. Please select an alternative payment method.');
            $this->messageManager->addErrorMessage($errorMessage);

            return $this->redirectFactory->create()->setPath('checkout/cart', [
                '_scope' => $this->storeManager->getStore()
            ]);
        }

        $this->messageManager->addSuccessMessage((string)__('Cash App Transaction Completed'));

        return $this->redirectFactory->create()->setPath('checkout/onepage/success');
    }
}

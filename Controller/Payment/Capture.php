<?php declare(strict_types=1);

namespace Afterpay\CashApp\Controller\Payment;

class Capture implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const CHECKOUT_STATUS_CANCELLED = 'CANCELLED';
    const CHECKOUT_STATUS_SUCCESS = 'SUCCESS';
    const CHECKOUT_STATUS_DECLINED = 'DECLINED';

    /** @var \Magento\Framework\App\RequestInterface */
    private $request;
    /** @var \Magento\Checkout\Model\Session */
    private $session;
    /** @var \Magento\Framework\Controller\Result\RedirectFactory */
    private $redirectFactory;
    /** @var \Magento\Framework\Message\ManagerInterface */
    private $messageManager;
    /** @var \Afterpay\Afterpay\Model\Payment\Capture\PlaceOrderProcessor */
    private $placeOrderProcessor;
    /** @var \Magento\Payment\Gateway\CommandInterface */
    private $validateCheckoutDataCommand;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Afterpay\Afterpay\Model\Payment\Capture\PlaceOrderProcessor $placeOrderProcessor,
        \Magento\Payment\Gateway\CommandInterface $validateCheckoutDataCommand
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->placeOrderProcessor = $placeOrderProcessor;
        $this->validateCheckoutDataCommand = $validateCheckoutDataCommand;
    }

    public function execute()
    {
        if ($this->request->getParam('status') == self::CHECKOUT_STATUS_CANCELLED) {
            $this->messageManager->addErrorMessage(
                (string)__('You have cancelled your Cash App payment. Please select an alternative payment method.')
            );
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }
        if ($this->request->getParam('status') == self::CHECKOUT_STATUS_DECLINED) {
            $this->messageManager->addErrorMessage(
                (string)__('Cash App payment is declined.')
            );
            return $this->redirectFactory->create()->setPath('checkout');
        }
        if ($this->request->getParam('status') != self::CHECKOUT_STATUS_SUCCESS) {
            $this->messageManager->addErrorMessage(
                (string)__('Cash App payment is failed. Please select an alternative payment method.')
            );
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        try {
            $quote = $this->session->getQuote();
            $cashappOrderToken = $this->request->getParam('orderToken');
            $this->placeOrderProcessor->execute($quote, $this->validateCheckoutDataCommand, $cashappOrderToken);
        } catch (\Throwable $e) {
            $errorMessage = $e instanceof \Magento\Framework\Exception\LocalizedException
                ? $e->getMessage()
                : (string)__('Payment is failed');
            $this->messageManager->addErrorMessage($errorMessage);
            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        $this->messageManager->addSuccessMessage((string)__('Cash App Transaction Completed'));
        return $this->redirectFactory->create()->setPath('checkout/onepage/success');
    }
}

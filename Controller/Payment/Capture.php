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
use Psr\Log\LoggerInterface;

class Capture implements HttpGetActionInterface
{
    private const CHECKOUT_STATUS_CANCELLED = 'CANCELLED';
    private const CHECKOUT_STATUS_SUCCESS = 'SUCCESS';
    private const CHECKOUT_STATUS_DECLINED = 'DECLINED';
    private RequestInterface $request;
    private Session $session;
    private RedirectFactory $redirectFactory;
    private ManagerInterface $messageManager;
    private PlaceOrderProcessor $placeOrderProcessor;
    private CommandInterface $validateCheckoutDataCommand;
    private LoggerInterface $logger;

    public function __construct(
        RequestInterface    $request,
        Session             $session,
        RedirectFactory     $redirectFactory,
        ManagerInterface    $messageManager,
        PlaceOrderProcessor $placeOrderProcessor,
        CommandInterface    $validateCheckoutDataCommand,
        LoggerInterface     $logger
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->placeOrderProcessor = $placeOrderProcessor;
        $this->validateCheckoutDataCommand = $validateCheckoutDataCommand;
        $this->logger = $logger;
    }

    public function execute()
    {
        $cashappOrderToken = $this->request->getParam('orderToken');
        if ($this->request->getParam('status') == self::CHECKOUT_STATUS_CANCELLED) {
            $this->messageManager->addErrorMessage(
                (string)__('You have cancelled your Cash App Pay payment. Please select an alternative payment method.')
            );

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }
        if ($this->request->getParam('status') == self::CHECKOUT_STATUS_DECLINED) {
            $this->messageManager->addErrorMessage(
                (string)__('Cash App Pay payment is declined.')
            );

            $this->logger->info(
                'CashApp payment(' . $cashappOrderToken . ') response status is "' . $this->request->getParam('status')
                . '".' . 'Customer has been redirected to the checkout page.'
            );

            return $this->redirectFactory->create()->setPath('checkout');
        }
        if ($this->request->getParam('status') != self::CHECKOUT_STATUS_SUCCESS) {
            $this->messageManager->addErrorMessage(
                (string)__('Cash App Pay payment is failed. Please select an alternative payment method.')
            );

            $this->logger->info(
                'CashApp payment(' . $cashappOrderToken . ') response status is "' . $this->request->getParam('status')
                . '".' . 'Customer has been redirected to the cart page.'
            );

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        try {
            $quote = $this->session->getQuote();
            $this->placeOrderProcessor->execute($quote, $this->validateCheckoutDataCommand, $cashappOrderToken);
        } catch (\Throwable $e) {
            $errorMessage = $e instanceof LocalizedException
                ? $e->getMessage()
                : (string)__('CashApp payment is declined. Please select an alternative payment method.');
            $this->messageManager->addErrorMessage($errorMessage);

            return $this->redirectFactory->create()->setPath('checkout/cart');
        }

        $this->messageManager->addSuccessMessage((string)__('Cash App Pay Transaction Completed'));

        return $this->redirectFactory->create()->setPath('checkout/onepage/success');
    }
}

<?php declare(strict_types=1);
namespace Afterpay\CashApp\Controller\Payment;

class Mobile implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const CASH_REQUEST_ID = 'cash_request_id';
    const IS_REDIRECTED = 'isRedirected';

    private \Magento\Framework\App\RequestInterface $request;
    private \Magento\Framework\Message\ManagerInterface $messageManager;
    private \Magento\Framework\View\Result\PageFactory $resultPageFactory;
    private \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        if ($this->request->getParam(self::IS_REDIRECTED) && !$this->request->getParam(self::CASH_REQUEST_ID)) {
            $this->messageManager->addErrorMessage(
                (string)__('Cash App Pay payment is declined.')
            );
            return $this->redirectFactory->create()->setPath('checkout');
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}

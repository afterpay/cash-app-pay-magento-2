<?php declare(strict_types=1);
namespace Afterpay\CashApp\Controller\Payment;

class Mobile extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const CASH_REQUEST_ID = 'cash_request_id';
    const IS_REDIRECTED = 'isRedirected';

    /** @var \Magento\Framework\App\RequestInterface */
    private $request;
    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messageManager;
    /** @var \Magento\Framework\View\Result\PageFactory */
    private $resultPageFactory;
    /** @var \Magento\Framework\Controller\Result\RedirectFactory */
    private $redirectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
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

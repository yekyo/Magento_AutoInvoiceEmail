<?php
namespace Kyo\AutoInvoice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CaptureInvoiceEmailObserver implements ObserverInterface
{
    const XML_PATH_EMAIL_INVOICE_CAPTURE = 'sales_email/invoice/capture';

    protected $logger;

    protected $invoiceSender;

    protected $_scopeConfig;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    )
    {
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * @param $invoice
     * @return bool
     */
    public function isEnable($invoice){
        return $this->_scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_INVOICE_CAPTURE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $invoice->getStoreId()
        );
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer){
        $invoice = $observer->getEvent()->getInvoice();

        if ($this->isEnable($invoice)){
            try {
                $this->invoiceSender->send($invoice);
            }catch (\Magento\Framework\Exception\MailException $exception) {
                $this->logger->critical($exception);
            }
        }
    }
}
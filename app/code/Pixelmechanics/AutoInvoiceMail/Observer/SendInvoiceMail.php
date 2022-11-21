<?php

/**
 * @author : AA
 * @description : Trigger an observer at event sales_order_invoice_save_after to send automatic mail after invoice
 * @date : 19.11.2019
 * @Trello: https://trello.com/c/dEw7KpFf/28-rechnungsdruck-senden-der-rechnungen#comment-5dd3d76bd22de67129b59560
 */

namespace Pixelmechanics\AutoInvoiceMail\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class SendInvoiceMail implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var Magento\Sales\Model\Order\Email\Sender\InvoiceSender 
     */
    protected $_invoiceSender;

    /**
     * @param InvoiceSender $invoiceSender
     */
    public function __construct(
    InvoiceSender $invoiceSender
    ) {
        $this->_invoiceSender = $invoiceSender;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $invoice = $observer->getEvent()->getInvoice();
        
        /*
         * Check binvoice mail is send or not 
         * if not then send mail to customer
         */
        
        if (!$invoice->getEmailSent()) {
            $this->_invoiceSender->send($invoice);
        }
    }

}

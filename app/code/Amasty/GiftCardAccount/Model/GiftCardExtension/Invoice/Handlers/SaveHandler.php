<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Repository;
use Magento\Sales\Api\Data\InvoiceInterface;

class SaveHandler
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function saveAttributes(InvoiceInterface $invoice): InvoiceInterface
    {
        if (!$invoice->getExtensionAttributes() || !$invoice->getExtensionAttributes()->getAmGiftcardInvoice()) {
            return $invoice;
        }
        $gCardInvoice = $invoice->getExtensionAttributes()->getAmGiftcardInvoice();

        if ($gCardInvoice->getInvoiceId() && $gCardInvoice->getGiftAmount() > 0) {
            $this->repository->save($gCardInvoice);
        }

        return $invoice;
    }
}

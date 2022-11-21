<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Filter;

use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Catalog\Product;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\CollectionModifierInterface;

class FilterProductListing implements CollectionModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function apply(AbstractDb $collection)
    {
        $productId = (int)$this->request->getParam('current_product_id');
        if (!$productId) {
            return;
        }

        $collection->getSelect()->where(sprintf(
            'main_table.%s = \'%s\' AND main_table.%s = %s',
            LogEntry::CATEGORY,
            Product::CATEGORY,
            LogEntry::ELEMENT_ID,
            $productId
        ));
    }
}

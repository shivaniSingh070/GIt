<?php

declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter\Type\Store;

use Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Magento\Framework\Data\Collection;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;

class Filter implements FilterInterface
{
    const TYPE_ID = 'store';

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    public function __construct(
        StoreRepositoryInterface $storeRepository
    ) {
        $this->storeRepository = $storeRepository;
    }

    public function apply(Collection $collection, FieldFilterInterface $filter)
    {
        $config = $filter->getExtensionAttributes()->getStoreFilter();
        if (!$config) {
            return;
        }

        $condition = [$filter->getCondition() => $config->getValue()];
        if (!empty($config->getValue()) && in_array(Store::DEFAULT_STORE_ID, $config->getValue())) {
            $condition = [$filter->getCondition() => $this->getStoreIds()];
        }

        $collection->addFieldToFilter($filter->getField(), $condition);
    }

    private function getStoreIds(): array
    {
        $storeIds = [];
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            if ($store->getId() != Store::DEFAULT_STORE_ID) {
                $storeIds[] = $store->getId();
            }
        }

        return $storeIds;
    }
}

<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Filter\Type\Store;

use Amasty\ImportCore\Api\Config\Profile\FieldFilterInterface;
use Amasty\ImportCore\Import\Filter\AbstractFilter;
use Amasty\ImportCore\Import\Filter\FilterDataInterface;
use Amasty\ImportCore\Import\Filter\FilterDataInterfaceFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;

class Filter extends AbstractFilter
{
    const TYPE_ID = 'store';

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    public function __construct(
        FilterDataInterfaceFactory $filterDataFactory,
        StoreRepositoryInterface $storeRepository
    ) {
        parent::__construct($filterDataFactory);

        $this->storeRepository = $storeRepository;
    }

    protected function getFilterConfig(FieldFilterInterface $filter)
    {
        return $filter->getExtensionAttributes()->getStoreFilter();
    }

    protected function prepareFilterData(FilterDataInterface $filterData)
    {
        if (!empty($filterData->getFilterValue())
            && in_array(Store::DEFAULT_STORE_ID, $filterData->getFilterValue())
        ) {
            $filterData->setFilterValue($this->getStoreIds());
        }
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

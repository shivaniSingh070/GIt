<?php

declare(strict_types=1);

namespace Amasty\AdminActionsLog\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\Store;

class StoreView extends Store
{
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['store_id'])) {
                $storeId = (int)$item['store_id'];
                $item['store_id'] = [];
                $item['store_id'][] = $storeId;
            }
        }

        $dataSource = parent::prepareDataSource($dataSource);

        return $dataSource;
    }
}

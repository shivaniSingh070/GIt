<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Ui\Component;

use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Class DataProvider
 * @package Amasty\Mostviewed\Ui\Component
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $data = $item->getData();
            if (isset($data['stores'])) {
                $stores = explode(',', $data['stores']);
                if (in_array('0', $stores)) {
                    $data['stores'] = ['0'];
                } else {
                    $data['stores'] = $stores;
                }
            }
            if ($data['impression'] != 0) {
                $data['ctr'] = round($data['click'] / $data['impression'], 2) * 100 . '%';
            } else {
                $data['ctr'] = '-';
            }
            $data['revenue'] = $this->data['config']['price_helper']->currency(
                $data['revenue'],
                true,
                false
            );
            $arrItems['items'][] = $data;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }
}

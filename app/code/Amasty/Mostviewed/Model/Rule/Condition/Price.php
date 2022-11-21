<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Rule\Condition;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class Price
 * @package Amasty\Mostviewed\Model\Rule\Condition
 */
class Price extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @param Collection $collection
     * @param ProductModel $product
     */
    public function apply(Collection $collection, ProductModel $product)
    {
        $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();

        $type = $this->getTypeByOperator();

        if ($type) {
            $collection->addFieldToFilter('price', [$type => $price]);
        }
    }

    /**
     * @return null|string
     */
    private function getTypeByOperator()
    {
        $type = null;
        switch ($this->getOperator()) {
            case '==':
                $type = 'eq';
                break;
            case '!=':
                $type = 'neq';
                break;
            case '<':
                $type = 'lt';
                break;
            case '<=':
                $type = 'lteq';
                break;
            case '>':
                $type = 'gt';
                break;
            case '>=':
                $type = 'gteq';
                break;
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        $this->_defaultOperatorInputByType = parent::getDefaultOperatorInputByType();
        $this->_arrayInputTypes[] = 'price';
        $this->_defaultOperatorInputByType['price'] = ['==', '!=', '>=', '>', '<=', '<'];

        return $this->_defaultOperatorInputByType;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'price';
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getAttributeElementHtml()
    {
        return __('Price');
    }

    /**
     * @return string
     */
    public function getValueElementHtml()
    {
        return __(' Current Product Price');
    }

    /**
     * @return string
     */
    protected function _getAttributeCode()
    {
        return 'price';
    }

    /**
     * @return array
     */
    public function getAttributeSelectOptions()
    {
        $opt = [
            [
                'value' => '1',
                'label' => __(' Current Product Price')
            ]
        ];

        return $opt;
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        return $this->getAttributeSelectOptions();
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return \Amasty\Mostviewed\Model\Group::FORM_NAME;
    }
}

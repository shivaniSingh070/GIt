<?php
declare(strict_types=1);

namespace Amasty\ImportCore\Import\Filter;

use Amasty\ImportCore\Import\Filter\Type\Date\Filter as DateFilter;
use Amasty\ImportCore\Import\Filter\Type\Select\Filter as SelectFilter;
use Amasty\ImportCore\Import\Filter\Type\Text\Filter as TextFilter;
use Amasty\ImportCore\Import\Filter\Type\Toggle\Filter as ToggleFilter;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class FilterTypeResolver
{
    /**
     * Get eav attribute filter type
     *
     * @param Attribute $attribute
     * @return string
     */
    public function getEavAttributeFilterType($attribute)
    {
        switch ($attribute->getFrontendInput()) {
            case 'date':
                return DateFilter::TYPE_ID;
            case 'select':
            case 'multiselect':
                return SelectFilter::TYPE_ID;
            case 'boolean':
                return ToggleFilter::TYPE_ID;
            default:
                return TextFilter::TYPE_ID;
        }
    }

    /**
     * Get table column filter type
     *
     * @param array $fieldDetails
     * @return string
     */
    public function getTableColumnFilterType(array $fieldDetails)
    {
        switch (strtolower($fieldDetails['DATA_TYPE'])) {
            case 'date':
            case 'datetime':
            case 'timestamp':
                return DateFilter::TYPE_ID;
            default:
                return TextFilter::TYPE_ID;
        }
    }
}

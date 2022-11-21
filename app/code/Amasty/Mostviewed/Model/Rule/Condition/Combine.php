<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Rule\Condition;

/**
 * Class Combine
 * @package Amasty\Mostviewed\Model\Rule\Condition
 */
class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    /**
     * @return string
     */
    public function getPrefix()
    {
        return 'where_conditions';
    }

    /**
     * @return mixed
     */
    public function getWhereConditions()
    {
        return $this->getData('conditions');
    }
}

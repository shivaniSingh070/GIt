<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Pgrid\Ui\Component\Listing\Attribute;

class Repository extends \Magento\Catalog\Ui\Component\Listing\Attribute\Repository
{
    /**
     * {@inheritdoc}
     */
    protected function buildSearchCriteria()
    {
        return $this->searchCriteriaBuilder
            ->addFilter('frontend_input', array(
                    'textarea',
                    'text',
                    'weight',
                    'price',
                    'date',
                    'boolean',
                    'select',
                    'multiselect'
            ), 'in')
            ->create();
    }
}

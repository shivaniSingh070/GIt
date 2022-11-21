<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;

/**
 * Class ProductProcessor
 * @package Amasty\Mostviewed\Model\Indexer
 */
class ProductProcessor extends AbstractProcessor
{
    /**
     * Indexer id
     */
    const INDEXER_ID = 'amasty_mostviewed_product_rule';
}

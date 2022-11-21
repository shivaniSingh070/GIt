<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Model\Indexer;

/**
 * Class CacheContext
 * @package Amasty\Mostviewed\Model\Indexer
 */
class CacheContext extends \Magento\Framework\Indexer\CacheContext
{
    /**
     * @var int
     */
    private $countElements = 0;

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array $ids
     *
     * @return $this
     */
    public function registerEntities($cacheTag, $ids)
    {
        parent::registerEntities($cacheTag, $ids);
        $this->countElements += count($ids);

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->countElements;
    }

    /**
     * {@inheritDoc}
     */
    public function flush(): void
    {
        $this->countElements = 0;
        $this->entities = [];
    }
}

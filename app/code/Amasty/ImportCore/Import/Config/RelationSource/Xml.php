<?php

namespace Amasty\ImportCore\Import\Config\RelationSource;

use Amasty\ImportCore\Import\Config\RelationSource\Xml\RelationsConfigPreparer;
use Amasty\ImportCore\SchemaReader\Config;

class Xml implements RelationSourceInterface
{
    /**
     * @var Config
     */
    private $entitiesConfigCache;

    /**
     * @var RelationsConfigPreparer
     */
    private $relationsConfigPreparer;

    public function __construct(
        Config $entitiesConfigCache,
        RelationsConfigPreparer $relationsConfigPreparer
    ) {
        $this->entitiesConfigCache = $entitiesConfigCache;
        $this->relationsConfigPreparer = $relationsConfigPreparer;
    }

    public function get()
    {
        $result = [];
        foreach ($this->entitiesConfigCache->get() as $entityCode => $entityConfig) {
            if (!empty($entityConfig['relations'])) {
                $result[$entityCode] = $this->relationsConfigPreparer->execute(
                    $entityConfig['relations']
                );
            }
        }

        return $result;
    }
}

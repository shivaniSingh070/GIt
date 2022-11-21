<?php

namespace Amasty\ExportCore\Export\Config\RelationSource;

use Amasty\ExportCore\Export\Config\RelationSource\Xml\RelationsConfigPrepare;
use Amasty\ExportCore\SchemaReader\Config;

class Xml implements RelationSourceInterface
{
    /**
     * @var Config
     */
    private $entitiesConfigCache;

    /**
     * @var RelationsConfigPrepare
     */
    private $relationsConfigPrepare;

    public function __construct(
        Config $entitiesConfigCache,
        RelationsConfigPrepare $relationsConfigPrepare
    ) {
        $this->entitiesConfigCache = $entitiesConfigCache;
        $this->relationsConfigPrepare = $relationsConfigPrepare;
    }

    public function get()
    {
        $result = [];
        foreach ($this->entitiesConfigCache->get() as $entityCode => $entityConfig) {
            if (!empty($entityConfig['relations'])) {
                $result[$entityCode] = $this->relationsConfigPrepare->execute($entityConfig['relations']);
            }
        }

        return $result;
    }
}

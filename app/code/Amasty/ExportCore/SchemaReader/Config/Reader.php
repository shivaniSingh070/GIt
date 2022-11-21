<?php

namespace Amasty\ExportCore\SchemaReader\Config;

class Reader extends \Amasty\ImportExportCore\Config\SchemaReader\Reader
{
    /**
     * List of id attributes for merge
     *
     * @var array
     */
    protected $_idAttributes = [
        '/config/entity' => 'code',
        '/config/entity/fieldsConfig/fields/field' => 'name',
        '/config/relation' => 'code'
    ];
}

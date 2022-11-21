<?php
return [
    'backend' => [
        'frontName' => 'pmadmin'
    ],
    'crypt' => [
        'key' => 'd7d6128864080a3372e9173ddeec5173'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'shivani_engelsrufer',
                'username' => 'shivani_engelsrufer',
                'password' => 'engelsrufer@3210',
                'active' => '1',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'files'
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'install' => [
        'date' => 'Wed, 19 Dec 2018 07:45:09 +0000'
    ],
    'cron_consumers_runner' => [
        'cron_run' => true,
        'max_messages' => 1000,
        'consumers' => [
            'async.operations.all',
            'codegeneratorProcessor'
        ]
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '501_'
            ],
            'page_cache' => [
                'id_prefix' => '501_'
            ]
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => null
        ]
    ]
];

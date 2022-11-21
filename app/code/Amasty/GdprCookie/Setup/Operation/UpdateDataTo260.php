<?php
declare(strict_types=1);

namespace Amasty\GdprCookie\Setup\Operation;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Exception\LocalizedException;

class UpdateDataTo260
{
    public const COOKIE_WALL_CONFIG_PATHS = [
        'amasty_gdprcookie/cookie_policy/website_interaction',
        'amasty_gdprcookie/cookie_policy/allowed_urls'
    ];

    /**
     * @var Config
     */
    private $scopeConfig;

    public function __construct(
        Config $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function upgrade()
    {
        foreach (self::COOKIE_WALL_CONFIG_PATHS as $path) {
            $configData = $this->getConfigValues($path);

            if (!$configData) {
                continue;
            }

            foreach ($configData as $record) {
                $this->scopeConfig->deleteConfig(
                    $record['path'],
                    $record['scope'],
                    $record['scope_id']
                );
            }
        }
    }

    private function getConfigValues(string $path): array
    {
        $connection = $this->scopeConfig->getConnection();
        $select = $connection->select()->from(
            $this->scopeConfig->getMainTable()
        )->where('path = ?', $path);

        return $connection->fetchAll($select);
    }
}

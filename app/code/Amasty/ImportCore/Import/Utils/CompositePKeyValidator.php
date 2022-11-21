<?php

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Utils;

use Magento\Framework\App\ResourceConnection;

class CompositePKeyValidator
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Checks if data row contains duplicated primary key part value that expected being unique
     *
     * @param array $row
     * @param array $pKeyParts
     * @param array $uniqueKeyParts
     * @param string $tableName
     * @param string $connectionName
     * @return bool
     */
    public function isUniquePartDuplicated(
        array $row,
        array $pKeyParts,
        array $uniqueKeyParts,
        string $tableName,
        string $connectionName = ResourceConnection::DEFAULT_CONNECTION
    ): bool {
        if (empty($row) || empty($pKeyParts)) {
            return false;
        }

        foreach ($uniqueKeyParts as $uniqueKeyPart) {
            if (!isset($row[$uniqueKeyPart])) {
                return false;
            }
        }

        $connection = $this->resourceConnection->getConnection($connectionName);
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName($tableName))
            ->limit(1)
            ->columns($uniqueKeyParts);

        foreach ($pKeyParts as $pKeyPart) {
            if (in_array($pKeyPart, $uniqueKeyParts)) {
                $select->where($pKeyPart . ' = ?', $row[$pKeyPart]);
            } elseif (isset($row[$pKeyPart])) {
                $select->where($pKeyPart . ' != ?', $row[$pKeyPart]);
            }
        }

        return (bool)$connection->fetchOne($select);
    }
}

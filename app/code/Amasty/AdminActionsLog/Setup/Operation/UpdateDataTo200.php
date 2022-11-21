<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup\Operation;

use Amasty\AdminActionsLog\Model\ConfigProvider;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\LogEntry\ResourceModel\LogEntry as LogEntryResource;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpdateDataTo200
{
    private $changedConfigFields = [
        'log/log_enable_visit_history' => ConfigProvider::LOG_ENABLE_VISIT_HISTORY,
        'log/log_all_admins' => ConfigProvider::LOG_ALL_ADMINS,
        'log/log_admin_users' => ConfigProvider::LOG_ADMIN_USERS,
        'log/log_delete_logs_after_days' => ConfigProvider::ACTIONS_LOG_PERIOD,
        'log/log_delete_login_attempts_after_days' => ConfigProvider::LOGIN_ATTEMPTS_LOG_PERIOD,
        'log/log_delete_pages_history_after_days' => ConfigProvider::VISIT_HISTORY_LOG_PERIOD,
        'geolocation/geolocation_enable' => ConfigProvider::GEOLOCATION_ENABLE,
        'restore/restore_popup_message' => ConfigProvider::RESTORE_POPUP_MESSAGE,
        'successful_log_mailing/enabled' => ConfigProvider::SUCCESSFUL_LOG_MAILING_ENABLED,
        'successful_log_mailing/template' => ConfigProvider::SUCCESSFUL_LOG_MAILING_TEMPLATE,
        'successful_log_mailing/send_to_mail' => ConfigProvider::SUCCESSFUL_LOG_MAILING_SEND_TO,
        'unsuccessful_log_mailing/enabled' => ConfigProvider::UNSUCCESSFUL_LOG_MAILING_ENABLED,
        'unsuccessful_log_mailing/template' => ConfigProvider::UNSUCCESSFUL_LOG_MAILING_TEMPLATE,
        'unsuccessful_log_mailing/send_to_mail' => ConfigProvider::UNSUCCESSFUL_LOG_MAILING_SEND_TO,
        'suspicious_log_mailing/enabled' => ConfigProvider::SUSPICIOUS_LOG_MAILING_ENABLED,
        'suspicious_log_mailing/template' => ConfigProvider::SUSPICIOUS_LOG_MAILING_TEMPLATE,
        'suspicious_log_mailing/send_to_mail' => ConfigProvider::SUSPICIOUS_LOG_MAILING_SEND_TO
    ];

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * @var LogEntryTypes
     */
    private $logEntryTypes;

    public function __construct(
        State $appState,
        Config $scopeConfig,
        LogEntryTypes $logEntryTypes
    ) {
        $this->appState = $appState;
        $this->scopeConfig = $scopeConfig;
        $this->logEntryTypes = $logEntryTypes;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup): void
    {
        $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'updateModuleData']);
        $this->migrateLogTypes($setup);
        $this->migrateCategories($setup);
        $this->migrateExportTypes($setup);
    }

    public function updateModuleData(): void
    {
        $this->updateConfig();
    }

    private function migrateExportTypes(ModuleDataSetupInterface $setup): void
    {
        $tableName = $setup->getTable(LogEntryResource::TABLE_NAME);
        foreach (['exportCsv', 'exportXml'] as $oldExportType) {
            $setup->getConnection()->update(
                $tableName,
                [LogEntry::TYPE => 'export'],
                [LogEntry::TYPE . ' = ?' => $oldExportType]
            );
        }
    }

    private function migrateCategories(ModuleDataSetupInterface $setup): void
    {
        $tableName = $setup->getTable(LogEntryResource::TABLE_NAME);
        $categoriesMapping = [
            'catalog/product' => [
                LogEntry::CATEGORY => 'catalog/product/edit',
                LogEntry::CATEGORY_NAME => __('Catalog Product'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'customer' => [
                LogEntry::CATEGORY => 'customer/index/edit',
                LogEntry::CATEGORY_NAME => __('Customer'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'customer/index' => [
                LogEntry::CATEGORY => 'customer/index/edit',
                LogEntry::CATEGORY_NAME => __('Customer'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'customer/group' => [
                LogEntry::CATEGORY => 'customer/group/edit',
                LogEntry::CATEGORY_NAME => __('Customer Group'),
                LogEntry::PARAMETER_NAME => 'id'
            ],
            'catalog/product_attribute' => [
                LogEntry::CATEGORY => 'catalog/product_attribute/edit',
                LogEntry::CATEGORY_NAME => __('Product Attribute'),
                LogEntry::PARAMETER_NAME => 'attribute_id'
            ],
            'sales/order_create' => [
                LogEntry::CATEGORY => 'sales/order/view',
                LogEntry::CATEGORY_NAME => __('Order'),
                LogEntry::PARAMETER_NAME => 'order_id'
            ],
            'sales/order' => [
                LogEntry::CATEGORY => 'sales/order/view',
                LogEntry::CATEGORY_NAME => __('Order'),
                LogEntry::PARAMETER_NAME => 'order_id'
            ],
            'admin/order_shipment' => [
                LogEntry::CATEGORY => 'sales/shipment/view',
                LogEntry::CATEGORY_NAME => __('Shipment'),
                LogEntry::PARAMETER_NAME => 'shipment_id'
            ],
            'admin/order_creditmemo' => [
                LogEntry::CATEGORY => 'sales/creditmemo/view',
                LogEntry::CATEGORY_NAME => __('Credit Memo'),
                LogEntry::PARAMETER_NAME => 'creditmemo_id'
            ],
            'admin/order_invoice' => [
                LogEntry::CATEGORY => 'sales/invoice/view',
                LogEntry::CATEGORY_NAME => __('Invoice'),
                LogEntry::PARAMETER_NAME => 'invoice_id'
            ],
            'catalog_rule/promo_catalog' => [
                LogEntry::CATEGORY => 'catalog_rule/promo_catalog/edit',
                LogEntry::CATEGORY_NAME => __('Catalog Price Rule'),
                LogEntry::PARAMETER_NAME => 'id'
            ]
        ];

        foreach ($categoriesMapping as $origCategory => $dataToUpdate) {
            $setup->getConnection()->update(
                $tableName,
                $dataToUpdate,
                [LogEntry::CATEGORY . ' = ?' => $origCategory]
            );
        }
    }

    private function migrateLogTypes(ModuleDataSetupInterface $setup): void
    {
        $tableName = $setup->getTable(LogEntryResource::TABLE_NAME);

        foreach ($this->logEntryTypes->toArray() as $typeKey => $typeLabel) {
            $setup->getConnection()->update(
                $tableName,
                [LogEntry::TYPE => $typeKey],
                [LogEntry::TYPE . ' = ?' => (string)$typeLabel]
            );
        }
    }

    /**
     * Update config pathes
     */
    protected function updateConfig(): void
    {
        foreach ($this->changedConfigFields as $oldPath => $newPath) {
            $oldPathData = $this->getConfigValues($oldPath);

            if (!$oldPathData) {
                continue;
            }
            foreach ($oldPathData as $record) {
                $this->changeConfigData($oldPath, $record);

                $this->scopeConfig->saveConfig(
                    'amaudit/' . $newPath,
                    $record['value'],
                    $record['scope'],
                    $record['scope_id']
                );
                $this->scopeConfig->deleteConfig(
                    $record['path'],
                    $record['scope'],
                    $record['scope_id']
                );
            }
        }
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws LocalizedException
     */
    private function getConfigValues($path): array
    {
        $connection = $this->scopeConfig->getConnection();
        $select = $connection->select()->from(
            $this->scopeConfig->getMainTable()
        )->where(
            'path = ?',
            'amaudit/' . $path
        );

        return $connection->fetchAll($select);
    }

    private function changeConfigData($oldPath, &$record): void
    {
        switch ($oldPath) {
            case 'successful_log_mailing/template':
            case 'unsuccessful_log_mailing/template':
            case 'suspicious_log_mailing/template':
                if ($record['value'] == 'amaudit_successful_log_mailing_template') {
                    $record['value'] = 'amaudit_email_notifications_successful_log_mailing_template';
                }
                if ($record['value'] == 'amaudit_unsuccessful_log_mailing_template') {
                    $record['value'] = 'amaudit_email_notifications_unsuccessful_log_mailing_template';
                }
                if ($record['value'] == 'amaudit_suspicious_log_mailing_template') {
                    $record['value'] = 'amaudit_email_notifications_suspicious_log_mailing_template';
                }
                break;
        }
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Setup\UpgradeData;

use Amasty\Mostviewed\Model\OptionSource\ReplaceType;
use Amasty\Mostviewed\Model\OptionSource\SourceType;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Mostviewed\Model\Repository\GroupRepository;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Amasty\Mostviewed\Api\Data\GroupInterface;

/**
 * Class UpgradeSettings
 * @package Amasty\Mostviewed\Setup\UpgradeData
 * @codingStandardsIgnoreFile
 */
class UpgradeSettings
{
    const SECTION_PATH = 'ammostviewed';

    /**
     * @var \Amasty\Mostviewed\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    public function __construct(
        \Amasty\Mostviewed\Model\GroupFactory $groupFactory,
        GroupRepository $groupRepository,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        $this->groupFactory = $groupFactory;
        $this->groupRepository = $groupRepository;
        $this->productMetadata = $productMetadata;
        $this->serializer = $serializer;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('core_config_data');

        $select = $setup->getConnection()->select()
            ->from($tableName)
            ->where('path like ?', self::SECTION_PATH . '%');

        $oldSettings = $connection->fetchAll($select);
        if ($oldSettings) {
            $this->convertSettings($setup, $oldSettings);
        } else {
            $this->createExamples();
        }
    }

    /**
     * @param $setup
     * @param $oldSettings
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function convertSettings(ModuleDataSetupInterface $setup, $oldSettings)
    {
        $oldSettings = $this->divideDataByType($oldSettings);
        foreach ($oldSettings as $groupName => $groupData) {
            if (isset($groupData['enabled']) && $groupData['enabled'] != '1') {
                continue;
            }

            $data = $this->getDefaultDataByType($groupName);
            if (isset($groupData['replace']) && $groupData['replace'] == '0') {
                if (isset($groupData['size'])) {
                    $data[GroupInterface::MAX_PRODUCTS] = $groupData['size'];
                }
                // do nothing if display manually added only
                $groupData = [];
            }

            foreach ($groupData as $key => $value) {
                switch ($key) {
                    case 'size':
                        $data[GroupInterface::MAX_PRODUCTS] = $value;
                        break;
                    case 'replace':
                        $value = ($value == 1) ? ReplaceType::REPLACE : ReplaceType::ADD;
                        $data[GroupInterface::REPLACE_TYPE] = $value;
                        break;
                    case 'in_stock':
                        $data[GroupInterface::SHOW_OUT_OF_STOCK] = !$value;
                        break;
                    case 'out_of_stock_only':
                        $data[GroupInterface::SHOW_FOR_OUT_OF_STOCK] = $value ? 1 : 0;
                        break;
                    case 'data_source':
                        switch ($value) {
                            case '0': //SOURCE_VIEWED
                                $data[GroupInterface::SOURCE_TYPE] = SourceType::SOURCE_VIEWED;
                                break;
                            case '1': //SOURCE_BOUGHT
                                $data[GroupInterface::SOURCE_TYPE] = SourceType::SOURCE_BOUGHT;
                                break;
                            case '2':
                                if (isset($groupData['category_condition'])) {
                                    $groupData['category_condition'] = null;
                                }
                                if (isset($groupData['brand_attribute'])) {
                                    $groupData['brand_attribute'] = null;
                                }
                                if (isset($groupData['price_condition'])) {
                                    $groupData['price_condition'] = null;
                                }
                                if (isset($groupData['condition_id']) && $groupData['condition_id']) {
                                    $data[GroupInterface::SOURCE_TYPE] = $value;
                                    $data[GroupInterface::CONDITIONS]
                                        = $this->getOldCondition($setup, $groupData['condition_id']);
                                }
                                break;
                        }
                        break;
                    case 'category_condition':
                        if (!$value) {
                            continue 2;
                        }
                        $value = 'category_ids';
                        break;
                    case 'brand_attribute':
                        if (!$value) {
                            continue 2;
                        }
                        $data[GroupInterface::SAME_AS] = 1;
                        $data[GroupInterface::SAME_AS_CONDITIONS]
                            =
                            '{"type":"Magento\\\\CatalogRule\\\\Model\\\\Rule\\\\Condition\\\\Combine"'
                            . ',"attribute":null,'
                            . '"operator":null,"value":1,"is_value_processed":null,"aggregator":all,"conditions":'
                            . '[{"type":"Amasty\\\\Mostviewed\\\\Model\\\\Rule\\\\Condition\\\\Product","attribute":"'
                            . $value . '","operator":"==","value":false,"is_value_processed":false}]}';
                        break;
                    case 'price_condition':
                        switch ($value) {
                            case '0':
                                $value = false;
                                break;
                            case '1':
                                $value = '==';
                                break;
                            case '2':
                                $value = '>';
                                break;
                            case '3':
                                $value = '<';
                                break;
                        }
                        if (!$value) {
                            continue 2;
                        }

                        $data[GroupInterface::SAME_AS] = 1;
                        $data[GroupInterface::SAME_AS_CONDITIONS]
                            =
                            '{"type":"Magento\\\\CatalogRule\\\\Model\\\\Rule\\\\Condition\\\\Combine",'
                            . '"attribute":null,'
                            . '"operator":null,"value":"1","is_value_processed":null,"aggregator":"all","conditions":'
                            . '[{"type":"Amasty\\\\Mostviewed\\\\Model\\\\Rule\\\\Condition\\\\Price",'
                            . '"attribute":false,'
                            . '"operator":"' . $value . '","value":false,"is_value_processed":false}]}';
                        break;
                }

            }

            if ($data) {
                $this->createGroup($data);
            }
        }
    }

    /**
     * @param $groupName
     *
     * @return array
     */
    private function getDefaultDataByType($groupName)
    {
        $data = [];
        switch ($groupName) {
            case 'related_products':
                $data['name'] = __('Related Products');
                $data[GroupInterface::BLOCK_POSITION] = BlockPosition::PRODUCT_INTO_RELATED;
                break;
            case 'cross_sells':
                $data['name'] = __('Cross-Sells');
                $data[GroupInterface::BLOCK_POSITION] = BlockPosition::CART_INTO_CROSSSEL;
                break;
            case 'up_sells':
                $data['name'] = __('Up-Sells');
                $data[GroupInterface::BLOCK_POSITION] = BlockPosition::PRODUCT_INTO_UPSELL;
                break;
        }

        $data[GroupInterface::STORES] = '0';
        $data[GroupInterface::CUSTOMER_GROUP_IDS] = '0,1,2,3';

        return $data;
    }

    /**
     * @param $setup
     * @param $conditionId
     *
     * @return string
     */
    private function getOldCondition(ModuleDataSetupInterface $setup, $conditionId)
    {
        $result = '';
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('amasty_mostviewed_rule');
        if ($connection->isTableExists($tableName)) {
            $select = $setup->getConnection()->select()
                ->from($tableName, ['conditions_serialized'])
                ->where('rule_id=?', $conditionId);

            $result = $connection->fetchRow($select);
            $result = isset($result['conditions_serialized']) ? $result['conditions_serialized'] : '';
        }

        return $result;
    }

    /**
     * @param $oldSettings
     *
     * @return array
     */
    private function divideDataByType($oldSettings)
    {
        $result = [
            'related_products' => [],
            'cross_sells'      => [],
            'up_sells'         => []
        ];

        foreach ($oldSettings as $setting) {
            $name = $setting['path'];
            foreach ($result as $key => $item) {
                if (strpos($name, $key) !== false) {
                    $name = str_replace($key . '/', '', $name);
                    $name = str_replace(self::SECTION_PATH . '/', '', $name);
                    $result[$key][$name] = $setting['value'];
                    break;
                }
            }
        }

        return $result;
    }

    /**
     *
     */
    private function createExamples()
    {
        $paths = $this->getXmlTemplatesPaths();
        foreach ($paths as $path) {
            $xmlDoc = simplexml_load_file($path);
            $templateData = $this->parseNode($xmlDoc);

            $this->createGroup($templateData);
        }
    }

    /**
     * @param array $data
     */
    private function createGroup($data)
    {
        $group = $this->groupFactory->create();
        /* fix for magento 2.1 */
        if (version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
            $serializeFlds = ['where_conditions_serialized', 'conditions_serialized', 'same_as_conditions_serialized'];
            foreach ($serializeFlds as $key) {
                if (isset($data[$key])) {
                    try {
                        $tmp = json_decode($data[$key], true);
                        if ($tmp) {
                            $data[$key] = $this->serializer->serialize($tmp);
                        }
                    } catch (\Exception $ex) {
                        continue;//skip
                    }
                }
            }
        }

        $group->addData($data);

        $this->groupRepository->save($group);
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string $parentKeyNode
     *
     * @return array|string
     */
    private function parseNode($node, $parentKeyNode = '')
    {
        $data = [];
        foreach ($node as $keyNode => $childNode) {
            if (is_object($childNode)) {
                $data[$keyNode] = $this->parseNode($childNode, $keyNode);
            }
        }

        if (count($node) == 0) {
            $data = (string)$node;
            if ($data == 'true') {
                $data = true;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getXmlTemplatesPaths()
    {
        $p = strrpos(__DIR__, DIRECTORY_SEPARATOR);
        $directoryPath = $p ? substr(__DIR__, 0, $p) : __DIR__;
        $directoryPath .= '/../etc/adminhtml/examples/';
        // @codingStandardsIgnoreLine
        return glob($directoryPath . '*.xml');
    }
}

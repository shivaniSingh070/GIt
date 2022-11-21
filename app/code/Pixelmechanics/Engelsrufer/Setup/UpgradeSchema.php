<?php
/**
 * @category    Pixelmechanics
 * @package     Pixelmechanics Engelsrufer
 * Updated by AA on 15.05.2019
 */

namespace Pixelmechanics\Engelsrufer\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
/**
 * Class UpgradeSchema
 * @package Pixelmechanics\Engelsrufer\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
         $installer = $setup;

        $installer->startSetup();
        /*
         * Check if table exist then add a new column
         */
        $connection = $installer->getConnection();
         if (version_compare($context->getVersion(), '1.0.1' ,'<')) {
              if ($installer->tableExists('mageplaza_blog_post')) {
                   $connection->addColumn(
                $setup->getTable('mageplaza_blog_post'),
                'description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Post Description'
                ]
            );
              }
         }
         
        $installer->endSetup();
        
        
        
    }
}

<?php

/**
 * @author: AA
 * @date: 18.11 2019
 * @description: Add the order attributes with code "erp_id" and "navision_data"
 * @trello : https://trello.com/c/1FNqLT6A/47-export-order-only-when-payment-is-done-order-status-processing#comment-5dd244680f55406f9c884c70
*/

namespace Pixelmechanics\OrderAttribute\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
 
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    protected $salesSetupFactory;
 
    /**
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
    }
 
    /**
     * {@inheritDoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) 
    {
        $installer = $setup;
 
        $installer->startSetup();
 
        $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $installer]);
 
        // 1) field for storing only the Salesorder-No (Example: AT1440966)
        $salesSetup->addAttribute(Order::ENTITY, 'erp_id', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'=> 64,
            'visible' => false,
            'nullable' => true
        ]);
 
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'erp_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 64,
                'comment' =>'Nav Sales-Order-No'
            ]
        );
        
        
        
        // 2) field for storing only the Debitor-No (Example: BC100000126)
        $salesSetup->addAttribute(Order::ENTITY, 'debitor_no', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length'=> 64,
            'visible' => false,
            'nullable' => true
        ]);
 
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'debitor_no',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 64,
                'comment' =>'Nav Debitor-No'
            ]
        );
        
        
        
        
        /* PM RH: commented it out for now, as we have Sales-order id and debitor-id.  Rest can be seen in the order-comments history.
        3) field for storing serialized values
        $salesSetup->addAttribute(Order::ENTITY, 'navision_data', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            // 'length'=> 3096,
            'visible' => false,
            'nullable' => true
        ]);
 
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'navision_data',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                // 'length' => 3096,
                'comment' =>'Navision-Data'
            ]
        );
        /**/
         
        $installer->endSetup();
    }
}
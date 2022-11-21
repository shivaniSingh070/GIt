<?php
/**
 * @author: AA
 * @date: 07.11 2019
 * @description: Add the ERP customer attribute with code "erp_id"
 * @trello : https://trello.com/c/ytbOL9Aw/291-prio-1-new-customer-numbers#comment-5dc2cba65c2afc39cd6708a2
*/
namespace Pixelmechanics\CustomerAttribute\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    private $customerSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * created customer attribute with code "erp_id"
     * Show in customer form in admin
     * Type is "varchar"
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'erp_id', [
            'type' => 'varchar',
            'label' => 'ERP ID',
            'input' => 'text',
            'source' => '',
            'required' => false,
            'visible' => true,
            'position' => 300,
            'system' => false,
            'backend' => '',
            'note' => 'Die Kundennummer aus dem ERP-System'
        ]);
        
        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'erp_id')
        ->addData(['used_in_forms' => [
                'adminhtml_customer'
            ]
        ]);
        $attribute->save();

     
    }
}
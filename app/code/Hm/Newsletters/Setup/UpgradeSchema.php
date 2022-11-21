<?php
/**
 * UpgradeSchema of Hm_Newsletters Module to save interest
 * 
 */
namespace Hm\Newsletters\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $setup->startSetup();

        $table = $setup->getTable('newsletter_subscriber');

		if(version_compare($context->getVersion(), '1.2.0', '<')) {
			$setup->getConnection()->addColumn(
                $table,
                'c_group',
				[
					'type' => Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Ich möchte Newsletter über Schmuck für ...'
				]
			);
		}
		/**
 		*		 
 		* add c_dateofbirth column to save customer date of birth
 		*/
		if(version_compare($context->getVersion(), '1.4.0', '<')) {
			$setup->getConnection()->addColumn(
                $table,
                'c_dateofbirth',
				[
					'type' => Table::TYPE_TEXT,
					'nullable' => true,
					'comment' => 'Date of birth'
				]
			);
		}
	

        
        $setup->endSetup();
	}
}

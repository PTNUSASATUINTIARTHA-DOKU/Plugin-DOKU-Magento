<?php
namespace Doku\Core\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface{

	public function uninstall(
		SchemaSetupInterface $setup,
		ModuleContextInterface $context
	){

		$installer = $setup;
		$installer->startSetup();
		$installer->getConnection()->dropTable($installer->getTable('doku_tokenization_account'));
		$installer->getConnection()->dropTable($installer->getTable('doku_transaction'));
		$installer->endSetup();

	}

}
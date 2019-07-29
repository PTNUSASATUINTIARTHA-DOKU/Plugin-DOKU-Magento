<?php
/**
 * Doku
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://dokucommerce.com/Doku-Commerce-License.txt
 *
 *
 * @package    Doku_RefundRequest
 * @author     Extension Team
 *
 *
 */
namespace Doku\RefundRequest\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * Create Database
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /**
         * Create table 'doku_refundrequest'
         */
        $installer = $setup;

        $installer->startSetup();

        if ($setup->getConnection()->tableColumnExists('sales_order_grid','refund_status') == false) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('sales_order_grid'),
                    'refund_status',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => '2M',
                        'nullable' => true,
                        'comment' => 'Refund Status'
                    ]
                );
        }


        $table = $installer->getConnection()
            ->newTable($installer->getTable('doku_refundrequest'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Id'
            )
            ->addColumn(
                'refund_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '2M',
                ['nullable' => false, 'default' => 0],
                'Refund Status'
            )
            ->addColumn(
                'doku_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '50',
                ['nullable' => true],
                'Refund Status'
            )
            ->addColumn(
                'refund_status_remark',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Admin Remark'
            )
            ->addColumn(
                'refund_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                [10,0],
                ['nullable' => false, 'default' => 0],
                'Refund Amount'
            )
            ->addColumn(
                'doku_refund_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '50',
                ['nullable' => true],
                'DOKU Refund Type'
            )
            ->addColumn(
                'increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Increment Id'
            )
            ->addColumn(
                'customer_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Customer Name'
            )
            ->addColumn(
                'customer_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Customer Email'
            )
            ->addColumn(
                'reason_comment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Reason'
            )
            ->addColumn(
                'reason_option',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Reason for refund'
            )
            ->addColumn(
                'radio_option',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '2M',
                [],
                'Product Status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Post Created At'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Post Updated At'
            )
            ->addColumn(
                'doku_attachment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '65538',
                ['nullable' => true],
                'Attachment'
            )
            ->addIndex(
                $installer->getIdxName('doku_refundrequest', ['id']),
                ['id']
            )
            ->setComment("Refund Request");
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('doku_requestlabel'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                ],
                'Id'
            )
            ->addColumn(
                'request_label',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [],
                'Request Label'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                '2M',
                ['nullable' => false, 'default' => 0],
                'Status'
            )
            ->addIndex(
                $installer->getIdxName('doku_requestlabel', ['id']),
                ['id']
            )
            ->setComment("Refund Request Dropdown Options");
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}

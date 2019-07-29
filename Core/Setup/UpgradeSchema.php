<?php

namespace Doku\Core\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface {

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {

        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $tableName = 'doku_transaction';

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {

                // Declare data
                $columns = [
                    'created_at' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => true,
                        'comment' => 'Craeted At',
                    ],
                    'updated_at' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => true,
                        'comment' => 'Update At',
                    ],
                    'admin_fee_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Admin Fee Type',
                    ],
                    'admin_fee_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'Admin Fee Amount',
                    ],
                    'admin_fee_trx_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'Admin Fee Trx Amount',
                    ],
                    'discount_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Discount Fee Type',
                    ],
                    'discount_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'Discount Fee Amount',
                    ],
                    'discount_trx_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'Discount Fee Trx Amount',
                    ],
                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {

                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        } 
        
        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            $tableName = 'doku_transaction';

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {

                // Declare data
                $columns = [
                    'expired_at_gmt' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => true,
                        'comment' => 'Exipred at in GMT',
                    ],
                    'expired_at_storetimezone' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => true,
                        'comment' => 'Expired at in store time zone',
                    ],
                    'doku_grand_total' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'nullable' => false,
                        'default' => 0.00,
                        'comment' => 'Doku Grand Total'
                    ]
                ];

                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {

                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.3', '<')) {

            $tableName = 'doku_transaction';

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {

                // Declare data
                $columns = [
                    'admin_fee_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'nullable' => false,
                        'default' => 0.00,
                        'comment' => 'Admin Fee Amount'
                    ],
                    'admin_fee_trx_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'nullable' => false,
                        'default' => 0.00,
                        'comment' => 'Admin Fee Trx Amount'
                    ],
                    'discount_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'nullable' => false,
                        'default' => 0.00,
                        'comment' => 'Discount Amount'
                    ],
                    'discount_trx_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'length' => '12,4',
                        'nullable' => false,
                        'default' => 0.00,
                        'comment' => 'Discount Trx Amount'
                    ]
                ];       
           
                $connection = $setup->getConnection();

                foreach ($columns as $name => $definition) {

                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.4', '<')) {

            $status = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Sales\Model\Order\Status');

            $status->setData('status', 'waiting_for_verification')->setData('label', 'WAITING FOR VERIFICATION')->save();
            $status->assignState(\Magento\Sales\Model\Order::STATE_NEW, false, true);
        }

        if ($setup->getConnection()->isTableExists('doku_transaction')) {
            if (version_compare($context->getVersion(), '1.0.5', '<')) {
                // Declare data
                $columns = [
                    'customer_email' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Customer Email',
                    ],
                    'recurring_billnumber' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Recurring Billnumber',
                    ],
                    'recurring_flatstatus' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'Recurring Flat Status',
                    ]
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn('doku_transaction', $name, $definition);
                }
            }
        }


        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('doku_recurring_registration')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Customer id / email'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['nullable' => true],
                'Registration Status SUCCESS/FAILED'
            )->addColumn(
                'status_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                10,
                ['nullable' => true],
                'Registration Status; G: Notify Registration T: Notify Update'
            )->addColumn(
                'subscribe',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => true],
                'subscribe => 1; un-subscribe => 0'
            )->addColumn(
                'card_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => true],
                'Masked Credit Card Number'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Updated At'
            )->setComment(
                'Doku Recurring Registration'
            );
            $setup->getConnection()->createTable($table);
        }

        if ($setup->getConnection()->isTableExists('doku_recurring_registration')) {
            if (version_compare($context->getVersion(), '1.0.7', '<')) {
                // Declare data
                $columns = [
                    'token_id' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Token ID',
                    ]
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn('doku_recurring_registration', $name, $definition);
                }
            }
        }


        if ($setup->getConnection()->isTableExists('doku_tokenization_account')) {
            if (version_compare($context->getVersion(), '1.0.8', '<')) {
                // Declare data
                $columns = [
                    'customer_email' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Customer Email',
                    ]
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn('doku_tokenization_account', $name, $definition);
                }


                $columns = [
                    'bill_number' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Billing Number',
                    ],
                    'bill_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 1,
                        'nullable' => true,
                        'comment' => 'Billing Number',
                    ],
                    'start_date' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                        'nullable' => true,
                        'comment' => 'Start Date',
                    ],
                    'end_date' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                        'nullable' => true,
                        'comment' => 'End Date',
                    ],
                    'execute_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 1,
                        'nullable' => true,
                        'comment' => 'Executed Type',
                    ],
                    'execute_date' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 8,
                        'nullable' => true,
                        'comment' => 'Executed Date',
                    ],
                    'execute_month' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 3,
                        'nullable' => true,
                        'comment' => 'Executed Month',
                    ],
                    'flat_status' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'length' => 1,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Flat Status',
                    ],
                    'register_amount' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Register Amount',
                    ],
                    'subscription_status' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'length' => 1,
                        'nullable' => false,
                        'default' => 1,
                        'comment' => 'Subscription Status',
                    ],
                    'subscription_updated' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                        'nullable' => true,
                        'comment' => 'Subscription Status Last Update',
                    ]

                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn('doku_recurring_registration', $name, $definition);
                }

                if($connection->tableColumnExists('doku_recurring_registration', 'subscribe')) {
                    $connection->dropColumn('doku_recurring_registration', 'subscribe');
                }


            }
        }


        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('doku_recurring_payment')
                )
                ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [   'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true],
                'ID'
                )
                ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                [   'nullable' => false],
                    'Customer id / email'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => true],
                    'Order Id'
                )
                ->addColumn(
                    'token_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => true],
                    'Token Id'
                )
                ->addColumn(
                    'card_number',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => true],
                    'Masked Credit Card Number'
                )
                ->addColumn(
                    'bill_number',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => true],
                    'Bill Number'
                )
                ->addColumn(
                    'doku_payment_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => true],
                    'Doku Payment ID'
                )
                ->addColumn(
                    'merchant_transid',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => true],
                    'Merchant Trans ID'
                )
                ->addColumn(
                    'amount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Order Amount'
                )
                ->addColumn(
                    'currency',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => true],
                    'Currency'
                )
                ->addColumn(
                    'response_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    4,
                    ['nullable' => true],
                    'Response Code'
                )
                ->addColumn(
                    'approval_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    ['nullable' => true],
                    'Approval Code'
                )
                ->addColumn(
                    'result_message',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => true],
                    'Result Message'
                )
                ->addColumn(
                    'bank',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    ['nullable' => true],
                    'Issuer Bank Name'
                )
                ->addColumn(
                    'verify_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    30,
                    ['nullable' => true],
                    'Verify ID '
                )
                ->addColumn(
                    'verify_score',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    3,
                    ['nullable' => true],
                    'Verify Score '
                )
                ->addColumn(
                    'verify_status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => true],
                    'Verify Status '
                )
                ->addColumn(
                    'recurring_status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    ['nullable' => true],
                    'Recurring Status '
                )
                ->addColumn(
                    'session_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    50,
                    ['nullable' => true],
                    'Session ID '
                )
                ->addColumn(
                    'payment_datetime',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    20,
                    ['nullable' => true],
                    'Payment Datetime'
                )
                ->addColumn(
                    'words',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'WORDS'
                )
                ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
                )
                ->addColumn(
                'recurred_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Recurred At'
                )
                ->setComment(
                'Doku Recurring Payments'
                );
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            if ($setup->getConnection()->isTableExists('doku_recurring_payment')) {
                $connection = $setup->getConnection();

                $connection->addColumn('doku_recurring_payment', 'scheduled_at', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => true,
                    'comment' => 'Scheduled At',
                ]);

            }
        }


        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            if ($setup->getConnection()->isTableExists('doku_transaction')) {
                $connection = $setup->getConnection();

                $connection->addColumn('doku_transaction', 'payment_type', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'Payment Type',
                ]);

            }
        }

        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            if ($setup->getConnection()->isTableExists('doku_transaction')) {
                $connection = $setup->getConnection();

                $connection->addColumn('doku_transaction', 'approval_code', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'Approval Code',
                ]);

            }
        }

        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            if ($setup->getConnection()->isTableExists('doku_transaction')) {
                $connection = $setup->getConnection();

                $connection->addColumn('doku_transaction', 'capture_request', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 65538,
                    'nullable' => true,
                    'comment' => 'Authorization Capture Request',
                    'after' => 'review_params'
                ]);

                $connection->addColumn('doku_transaction', 'capture_response', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 65538,
                    'nullable' => true,
                    'comment' => 'Authorization Capture Response',
                    'after' => 'capture_request'
                ]);

                $connection->addColumn('doku_transaction', 'void_request', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 65538,
                    'nullable' => true,
                    'comment' => 'Authorization Cancel Request',
                    'after' => 'capture_response'
                ]);

                $connection->addColumn('doku_transaction', 'void_response', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 65538,
                    'nullable' => true,
                    'comment' => 'Authorization Cancel Response',
                    'after' => 'void_request'
                ]);

                $connection->addColumn('doku_transaction', 'authorization_status', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 20,
                    'nullable' => true,
                    'comment' => 'Authorization Status',
                ]);

            }
        }

        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            if ($setup->getConnection()->isTableExists('doku_transaction')) {
                $connection = $setup->getConnection();

                $connection->addColumn('doku_transaction', 'auth_expired', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'nullable' => true,
                    'comment' => 'Auth Expired At',
                ]);

            }
        }




        $setup->endSetup();
    }

}

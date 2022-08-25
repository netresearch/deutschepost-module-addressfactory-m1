<?php

/**
 * See LICENSE.md for license details.
 */

/** @var Mage_Sales_Model_Resource_Setup $installer */
$installer = Mage::getResourceModel('sales/setup', 'sales_setup');

$idColumnDefinition = array(
    'identity'  => false,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
);

$columnDefinition = array(
    'nullable' => false,
);

$table = $installer->getConnection()
    ->newTable($installer->getTable('postdirekt_addressfactory/analysis_result'))
    ->addColumn('order_address_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, $idColumnDefinition, 'Order Address ID')
    ->addColumn('status_codes', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $columnDefinition, 'Status Codes')
    ->addColumn('first_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $columnDefinition, 'First Name')
    ->addColumn('last_name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $columnDefinition, 'Last Name')
    ->addColumn('city', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $columnDefinition, 'City')
    ->addColumn('postal_code', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $columnDefinition, 'Postal Code')
    ->addColumn('street', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $columnDefinition, 'Street')
    ->addColumn('street_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $columnDefinition, 'Street Number')
    ->addForeignKey(
        $installer->getFkName('postdirekt_addressfactory/analysis_result', 'order_address_id', 'sales/order_address', 'entity_id'),
        'order_address_id',
        $installer->getTable('sales/order_address'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Postdirekt Addressfactory analysis table');
$installer->getConnection()->createTable($table);


$table = $installer->getConnection()
    ->newTable($installer->getTable('postdirekt_addressfactory/analysis_status'))
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, $idColumnDefinition, 'Order ID')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, $idColumnDefinition, 'Status')
    ->addForeignKey(
        $installer->getFkName('postdirekt_addressfactory/analysis_status', 'order_address_id', 'sales/order', 'entity_id'),
        'order_id',
        $installer->getTable('sales/order'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Postdirekt Addressfactory analysis status table');
$installer->getConnection()->createTable($table);

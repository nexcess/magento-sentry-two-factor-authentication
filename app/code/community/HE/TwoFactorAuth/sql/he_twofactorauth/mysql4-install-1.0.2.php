<?php
// create the twofactor_google_secret table on admin_user

$installer = $this;
$installer->startSetup();
$installer->getConnection()
    ->addColumn($installer->getTable('admin/user'),
    'twofactor_google_secret',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable' => true,
        'length' => 255,
        'default' => null,
        'comment' => 'Google Secret'
    )
);
$installer->endSetup();

// TODO add an uninstall script for users who remove module - apparently no automatic way to do this
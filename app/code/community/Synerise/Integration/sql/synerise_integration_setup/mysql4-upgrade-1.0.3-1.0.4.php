<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('sales/order')}
        MODIFY `synerise_send_at` TIMESTAMP NULL DEFAULT NULL;
");

$installer->endSetup();
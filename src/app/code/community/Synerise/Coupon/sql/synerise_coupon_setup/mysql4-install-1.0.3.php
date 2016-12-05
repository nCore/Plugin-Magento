<?php

$installer = $this;

$installer->startSetup();

$installer->run("
    
    ALTER TABLE {$this->getTable('salesrule')}
        ADD `synerise_uuid` VARCHAR(40) NOT NULL
        
");

$installer->endSetup(); 
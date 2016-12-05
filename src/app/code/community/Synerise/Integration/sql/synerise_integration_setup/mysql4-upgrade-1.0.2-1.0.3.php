<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$fork = Mage::getModel('core/config_data')->load('synerise_integration/allowForkTracking/enable', 'path')->getValue();
$installer->setConfigData('synerise_integration/tracking/fork', $fork);
$installer->endSetup();
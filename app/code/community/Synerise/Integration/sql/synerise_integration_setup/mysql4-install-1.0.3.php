<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE {$this->getTable('sales/order')}
        ADD `synerise_send_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
        
    UPDATE {$this->getTable('sales/order')} SET `synerise_send_at` = NULL;
");

$setup = new Mage_Customer_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->removeAttribute('customer', 'synerise_send_at');

$setup->addAttribute('customer', 'synerise_send_at', array(
    'input'          => 'text',
    'type'           => 'datetime',
    'label'          => 'synerise send at',
    'visible'        => 0,
    'required'       => 0,
    'user_defined'   => 0,
));
    
$installer->endSetup();
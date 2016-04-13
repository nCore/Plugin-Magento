<?php
class Synerise_Integration_Block_Tracking extends Mage_Core_Block_Template
{
    private function _validateCode() {
        return preg_match('/[A-Z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}/',$this->getCode());
    }
    
    public function getCode() {
        return Mage::getStoreConfig('synerise_integration/tracking/code');
    }

    public function getApiKey() {
        return Mage::getStoreConfig('synerise_integration/api/key');
    }
    
    public function enableTracking() {
        return (Mage::getStoreConfig('synerise_integration/tracking/enable') && $this->_validateCode());
    }
    
    public function getHost() {
        return Mage::getStoreConfig('synerise_integration/api/sandbox') ? 'tc.stage.synerise.com' : 'tc.synerise.com';
    }
}
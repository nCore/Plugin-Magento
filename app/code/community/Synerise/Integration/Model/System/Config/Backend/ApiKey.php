<?php
class Synerise_Integration_Model_System_Config_Backend_ApiKey extends Mage_Core_Model_Config_Data
{
    protected $_successMessage = null;
    
    /*
     * Validate & save api key.
     */
    public function save()
    {
        if(!$this->isValueChanged()) {
            return parent::save();
        }
        
        $apiKey = trim($this->getValue());
        $helper = $this->_getHelper();
        
        // validate api key
        if(!$apiKey) {
            Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('Api key removed.', $apiKey));
        } elseif($helper->validateApiKey($apiKey)) {

            // test connection
            $instance = $this->_getHelper()->getInstance('Default', '1.0', $apiKey);
            $data = $instance->test();

            // success response
            if($data && array_key_exists('business-profile', $data)) {
                if(isset($data['business-profile'])) {
                    $this->_successMessage = $this->_getHelper()->__('Api key saved successfully. Current business profile: %s.', $data['business-profile']['name']);
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($helper->__('No bussiness profile found for Api key %s.', $apiKey));
                    $apiKey = null;
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError($helper->__('Profile data could not be obtained at the moment. Please try again in a little while.'). '('.$apiKey.')');
                $apiKey = null;
            }            
            
        } else {            
            Mage::getSingleton('adminhtml/session')->addError($helper->__('Invalid Api key: %s.<br/>Proper format is AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE.', $apiKey));
            $apiKey = null;
        }
        
        $this->setValue($apiKey);            
        return parent::save();
    }
    
    /*
     * Get taracking code for provided api key & save
     */
    public function _afterSave()
    {
        if(!$this->isValueChanged()) {
            return parent::_afterSave();
        }
        
        Mage::getConfig()->cleanCache();
        
        if($this->_successMessage) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->_successMessage);
        }
        
        $apiKey = $this->getValue();
        if($apiKey) {
            $domain = parse_url(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),PHP_URL_HOST);

            // obtain tracking code
            $instance = $this->_getHelper()->getInstance('TrackingCode', '1.0');
            $data = $instance->trackingcode($domain);
            if($data && isset($data) && isset($data['code'])) {
                 Mage::getConfig()->saveConfig('synerise_integration/tracking/code', $data['code']);
                 Mage::getSingleton('adminhtml/session')->addSuccess($this->_getHelper()->__('Tracking code was successfully obtained (%s).', $data['code']));
                 return parent::_afterSave();
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->_getHelper()->__('Tracking code colouldn\'t be obtained for this profile.'));                         
            }
        }

        Mage::getConfig()->saveConfig('synerise_integration/tracking/code', null);                
        Mage::getConfig()->saveConfig('synerise_integration/tracking/enable', 0);
    }
    
    protected function _getHelper()
    {
        return Mage::helper('synerise_integration/api');
    }
    
}
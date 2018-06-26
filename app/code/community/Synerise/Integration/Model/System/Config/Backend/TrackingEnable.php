<?php
class Synerise_Integration_Model_System_Config_Backend_TrackingEnable extends Mage_Core_Model_Config_Data
{
    protected $_successMessage = null;
    
    /*
     * Get taracking code for provided api key & save/
     */
    public function save()
    {
        if(!empty($this->_getConfigDataValue('synerise_integration/tracking/code')) && (!$this->isValueChanged() || $this->getValue() != 1)) {
            return parent::save();
        }

        $domain = parse_url(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),PHP_URL_HOST);
        
        // obtain tracking code
        $instance = $this->_getHelper()->getInstance('TrackingCode');
        $data = $instance->trackingcode($domain);
        if($data && isset($data) && isset($data['code'])) {
            if($data['code'] != $this->_getConfigDataValue('synerise_integration/tracking/code')) {
                $this->_setConfigDataValue('synerise_integration/tracking/code', $data['code']);
                Mage::getSingleton('adminhtml/session')->addSuccess($this->_getHelper()->__('Tracking code was successfully obtained (%s).', $data['code']));
            }
        } else {
            $this->setValue(0); 
            Mage::getSingleton('adminhtml/session')->addError($this->_getHelper()->__('Tracking code colouldn\'t be obtained for this profile.'));                         
        }
        
        return parent::save();
    }

    protected function _getConfigDataValue($key)
    {
        return Mage::getSingleton('adminhtml/config_data')->getConfigDataValue($key);
    }

    protected function _setConfigDataValue($key, $value)
    {
        return Mage::getConfig()->saveConfig(
            $key,
            $value,
            Mage::getSingleton('adminhtml/config_data')->getScope(),
            Mage::getSingleton('adminhtml/config_data')->getScopeId()
        );
    }

    protected function _getHelper()
    {
        return Mage::helper('synerise_integration/api');
    }
    
}
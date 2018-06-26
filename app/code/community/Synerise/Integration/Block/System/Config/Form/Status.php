<?php
class Synerise_Integration_Block_System_Config_Form_Status extends Synerise_Integration_Block_System_Config_Form_Fieldset
{
    protected function _construct()
    {
        parent::_construct();
        $this->setHeadMsg($this->__("Integration Status"));
        $this->setInfoMsg($this->_getProfileDataHtml());
    }
    
    protected function _getProfileDataHtml()
    {
        try {
            if(!$this->_getConfigDataValue('synerise_integration/api/key')) {
                return $this->__('Missing Api Key.');
            }

            $instance = $this->_getHelper()->getInstance('Default');
            $data = $instance->test();
            
            $html = '';
            if(isset($data['business-profile'])) {
                $html .= '<p><b>'.$this->__('Business Profile').':</b> '.$data['business-profile']['name'].' (id: '.$data['business-profile']['id'].')</p>';
            }
            
            $trackingCode = $this->_getConfigDataValue('synerise_integration/tracking/code');
            $status = $this->_getConfigDataValue('synerise_integration/tracking/enable') ? $this->__('Enabled') : $this->__('Disabled');
            
            if($trackingCode) {
                $html .= sprintf('<p><b> %s:</b> %s (%s)</p>', $this->__('Tracking'), $status, $trackingCode);                
            } else {
                $html .= sprintf('<p><b> %s:</b> %s</p>', $this->__('Tracking'), $status);
            }
            
            $version = Mage::getConfig()->getModuleConfig("Synerise_Integration")->version;
            $html .= sprintf('<p><b> %s:</b> %s</p>', $this->__('Module Version'), $version);
            
            if($html) return $html;                            

        } catch (Exception $ex) {
            Mage::log($ex->getMessage(), null, 'synerise_integration.log');
        }
        return '<p>'.$this->__('Profile data could not be obtained at the moment. Please try again in a little while.').'</p>';
    }
}
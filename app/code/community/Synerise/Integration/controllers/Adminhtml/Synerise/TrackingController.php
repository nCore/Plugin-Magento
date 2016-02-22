<?php
class Synerise_Integration_Adminhtml_Synerise_TrackingController extends Mage_Adminhtml_Controller_Action {
	    
     protected function _getHelper() {
         return Mage::helper('synerise_integration');
     }    
    
    public function getAction() {

        try {
           $api = Mage::getModel("synerise_integration/api_trackingcode");
           $domain = parse_url(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),PHP_URL_HOST);
           $response = $api->API_getByDomain($domain);   
                
           if(isset($response['message']) && $response['message'] == 'General.Status.Success') {
                Mage::getConfig()->saveConfig('synerise_integration/tracking/code',$response['data']['code']);
                Mage::app()->getStore()->resetConfig();               
                $this->_getSession()->addSuccess($this->_getHelper()->__('Tracking code was updated successfully'));
           } elseif(isset($response['code']) && isset($response['message'])) {
               $this->_getSession()->addError($response['code']. ': ' .$this->_getHelper()->__($response['message'])); 
           } else {
               $this->_getSession()->addError($this->_getHelper()->__('Error: No API response')); 
           }    
        } catch (Exception $exc) {
            $exc->getMessage();
            $this->_getSession()->addError($this->_getHelper()->__($exc->getMessage())); 
        }

        $this->_redirect('*/system_config/edit', array('section'=>'synerise_integration'));
        return false;
    }
}
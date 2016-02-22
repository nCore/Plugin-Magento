<?php
Class Synerise_Integration_Model_Adminhtml_Api extends Mage_Core_Model_Abstract {
    
     protected function _getHelper() {
         return Mage::helper('synerise_integration');
     }

     public function getTrackinCodeUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/synerise_tracking/get');
    }       
    
    public function getCommentText(){
         return 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX<br/><a href="'.$this->getTrackinCodeUrl().'">'.$this->_getHelper()->__('Get your tracking code automatically through API').'</a>';
    }
    
}
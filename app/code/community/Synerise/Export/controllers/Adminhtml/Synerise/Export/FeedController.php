<?php
class Synerise_Export_Adminhtml_Synerise_Export_FeedController extends Mage_Adminhtml_Controller_Action 
{
    protected function _getConfig() {
        return Mage::getModel('synerise_export/config');
    }
    
    public function generateAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        if($this->_getConfig()->isEnabled($storeId)) {
            
            $time_start = microtime(true);
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);                
            $result = Mage::getModel('synerise_export/feed')->generateFeeds($storeId);
            if(isset($result['catalogUrl']) && isset($result['offersUrl'])) {
                $html = '<a href="' . $result['catalogUrl'] . '">catalog.xml</a> <a href="' . $result['offersUrl'] . '">ceneo.xml</a> <br/>Date: ' . $result['date'] . ' - Products: ' . $result['qty'] . ' - Time: ' . number_format((microtime(true) - $time_start), 4);
                $config = new Mage_Core_Model_Config();
                $config->saveConfig('synerise_export/generate/feed_result', $html, 'stores', $storeId);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('synerise_export')->__('Generated feed with %s products', $result['qty']));
                Mage::app()->getCacheInstance()->cleanType('config');
            }	
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);  
                        
        } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('synerise_export')->__('Please enable the store view before generating the xml'));		
        }
        $this->_redirect('adminhtml/system_config/edit/section/synerise_export');        
        
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/synerise/synerise_export');
    }    
    
}
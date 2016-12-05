<?php
class Synerise_Export_Model_Observer
{
    
    protected function getConfig() {
        return Mage::getModel('synerise_export/config');
    }        
    
    /*
     * Executed by cron
     */
    public function scheduledGenerateFeeds()
    {
        $storeIds = $this->getConfig()->getEnabledStoreIds(); 
        foreach($storeIds as $storeId) {
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
        }
    }
}
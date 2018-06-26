<?php
class Synerise_Integration_Helper_Api extends Mage_Core_Helper_Abstract
{
    public $defaults = array();
    
    public function __construct()
    {
        Synerise_Integration_Helper_Autoloader::createAndRegister();

        if (Mage::app()->getStore()->isAdmin()) {
            $apiKey = Mage::getSingleton('adminhtml/config_data')->getConfigDataValue('synerise_integration/api/key');
            $context = Synerise\SyneriseTracker::APP_CONTEXT_SYSTEM ;
        } else {
            $apiKey = Mage::getStoreConfig('synerise_integration/api/key');
            $context = Synerise\SyneriseTracker::APP_CONTEXT_CLIENT;
        }

        $context = (php_sapi_name() == "cli") ? Synerise\SyneriseTracker::APP_CONTEXT_SYSTEM : $context;

        $this->defaults = array(
            'apiKey' => $apiKey,
            'verify' => (bool) Mage::getStoreConfig('synerise_integration/api/verify_ssl'),
            'debug' => (bool) Mage::getStoreConfig('synerise_integration/dev/debug'),
            'context' => $context
        );
    }
    
    public function getInstance($client, $options = array())
    {
        $options = array_merge($this->defaults, $options);
        $logger = !empty($options['debug']) ? Mage::getModel('synerise_integration/Logger') : null;

        $class = 'Synerise\Synerise'.$client;
        return $class::getInstance($options, $logger);
    }
    
    public function validateApiKey($apiKey)
    {
        return preg_match('/^[A-Z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}$/', $apiKey);
    }
}
<?php
class Synerise_Integration_Helper_Api extends Mage_Core_Helper_Abstract
{
    public $defaults = array();
    
    public function __construct()
    {
        Synerise_Integration_Helper_Autoloader::createAndRegister();

        $this->defaults = array(
            'apiKey' => Mage::getStoreConfig('synerise_integration/api/key')     
        );
    }
    
    public function getInstance($client, $options = array())
    {
        $logger = Mage::getModel('synerise_integration/Logger');

        $class = 'Synerise\Synerise'.$client;
        return $class::getInstance(array_merge($this->defaults, $options), $logger);
    }
    
    public function validateApiKey($apiKey)
    {
        return preg_match('/^[A-Z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}$/', $apiKey);
    }
}
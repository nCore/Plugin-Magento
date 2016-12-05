<?php
require_once Mage::getBaseDir().'/vendor/autoload.php';

class Synerise_Integration_Helper_Api extends Mage_Core_Helper_Abstract
{
    public $defaults = array();
    
    public function __construct()
    {
        $this->defaults = array(
            'apiKey' => Mage::getStoreConfig('synerise_integration/api/key'),
            'apiVersion' => '1.0'            
        );
    }
    
    public function getInstance($client, $options = array())
    {
        $class = 'Synerise\Synerise'.$client;
        return $class::getInstance(array_merge($this->defaults, $options));
    }
    
    public function validateApiKey($apiKey)
    {
        return preg_match('/^[A-Z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}$/', $apiKey);
    }
}
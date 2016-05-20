<?php

require_once Mage::getBaseDir().'/vendor/autoload.php';

class Synerise_Newsletter_Model_Subscriber
{

    private $tracker;

    private $debug = false;

    private $snr = null;

    private $apiKey = null;

    public function __construct()
    {

        $this->tracker = Mage::getStoreConfig('synerise_integration/tracking/code');
        $this->apiKey = Mage::getStoreConfig('synerise_integration/api/key');


        $this->snr = Synerise\SyneriseNewsletter::getInstance([ //@todo wynieść do helpera
            'apiKey' => $this->apiKey, //@todo zaciagać z panelu
            'apiVersion' => '2.1.0', //@todo zaciagać z panelu? (czy po kluczu?)
            'allowFork' => false, //@todo tylko do debugowania? czy konfigurowalne z panelu?
        ]);

        $this->snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');
    }


    public function subscribe($email, $additionalParams = array()) {

        $this->snr->subscribe($email, $additionalParams);

    }

}
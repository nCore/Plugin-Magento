<?php
Class Synerise_Integration_Model_Api_Trackingcode extends Synerise_Integration_Model_Api_Abstract {
        
    public function __construct($params)
    {
        parent::__construct($params);
        $this->_serverUrl .= 'trackingcode/';
        $this->_logFile = 'trackingapi.log';
    }    
    
    public function API_getByDomain($domain) 
    {
        $action = $domain;
        $method = 'GET';
        $data = Array(
            'apikey' => $this->_apiKey
        );
        return $this->_sendRequest($action,$data,$method);
    }    
}



<?php
Abstract Class Synerise_Integration_Model_Api_Abstract {
    
    protected $_serverUrl;
    protected $_apiKey;
    protected $_logPath;
    protected $_logFile;
    
    public function __construct($params = null)
    {
        $this->_logPath = Mage::getBaseDir('log').DIRECTORY_SEPARATOR.'synerise'.DIRECTORY_SEPARATOR;
        $this->_serverUrl = $this->getServerUrl();
        $this->_apiKey = Mage::getStoreConfig('synerise_integration/api/key');
        
        if(!preg_match('/[A-Z0-9]{8}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{4}-[A-Za-z0-9]{12}/',$this->_apiKey)) {
            throw new Exception('Invalid API key');
        }
    }
    
    public function isProdEnv() {
        return !Mage::getStoreConfig('synerise_integration/api/sandbox');
    }
      
    public function getLogFile() {
        return $this->_logFile;
    }    
    
    public function setLogFile($logFile) {
        $this->_logFile = $logFile;
    }    
    
    public function setDebugMail($email) {
        $this->debugMail = $email;
    }
   
    protected function _sendRequest($action, $data, $method = 'POST', $log = true)
    {
        $ch = curl_init();

        if($method == 'GET' && !empty($data)) {
            $query = http_build_query($data);
            $url = $this->_serverUrl.$action.'?'.$query;         
        } else {
            $url = $this->_serverUrl.$action;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);        
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, true));            
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Host: ".parse_url($this->_serverUrl,PHP_URL_HOST), 
            "Accept: application/json",             
            "Content-Type: application/json",             
            "Api-Version: 2.0.0"
        ));
        
        if($log) {
            $logRequest = 'Synerise Request ('.$this->_serverUrl.$action.'): ' . json_encode($data, true);        
            $this->logMsg($logRequest);
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if($log) {
            $logResponse = 'Synerise Response ('.$status.'): ' . $response;   
            $this->logMsg($logResponse);
        }
        
        curl_close($ch);

        return json_decode($response,true);
    }
    
    public function logMsg($msg=false,$params=false) {
        
        if (!file_exists($this->_logPath)) {
            mkdir($this->_logPath, 0775, true);
        }
        
        if(!$this->_logFile) {
            return false;
        }
        
        $fullMsg = "\r\n-------------------------------\r\n" . PHP_EOL;
        if($msg) {
            $paramsPart = '';
            if($params) {
                if(is_array($params)) {
                    $params = http_build_query($params, '', '&');
                }
                $paramsPart = ": " . $params;
            }
            $fullMsg = "\r\n" . date('[Y-m-d H:i e] ') . $msg . $paramsPart . PHP_EOL;
        }

        error_log($fullMsg, 3, $this->_logPath.$this->_logFile);
        
    }
    
    public function getServerUrl() {
        return $this->isProdEnv() ? 'http://api.synerise.com/' : 'http://api.stage.synerise.com/';        
    }
}
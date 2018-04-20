<?php
namespace Synerise\Producers;

use Synerise\Producers\Client;
use Synerise\Exception\SyneriseException;
use Synerise\Helper\Cookie;


abstract class ProducerAbstract
{
    /**
     * An instance of the ProducerAbstract class
     * @var ProducerAbstract
     */
    private static $_instances = array();

    /**
     * @var array a queue to hold messages in memory before flushing in batches
     */
    private $_requestQueue = array();

    protected $_uuid;

    protected $_cookie;

    public function __construct()
    {
        $this->_cookie = Cookie::getInstance();
    }

    /**
     * Returns a singleton instance of Event
     * @return ProducerAbstract
     */
    public static function getInstance() {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }

    public static function flushInstances()
    {
        self::$_instances = array();
    }

    /**
     * Empties the queue without persisting any of the messages
     */
    public function reset() {
        $this->_requestQueue = array();
    }


    /**
     * Returns the in-memory queue
     * @return array
     */
    public function getRequestQueue($key = '') {
        if($key != ''){
            $return[$key] = $this->_requestQueue;
        } else {
            $return = $this->_requestQueue;

        }
        return $return;
    }

    /**
     * Add an array representing a message to be sent to Synerise to a queue.
     * @param array $message
     */
    public function enqueue($message = array()) {
        $customIdetify = Client::getInstance()->getCustomIdetify();
        if(!empty($customIdetify)){
            $message['clientCustomId'] =  $customIdetify;
        }

        $email = Client::getInstance()->getEmail();
        if(!empty($email)){
            $message['email'] =  $email;
        }

        $uuid = Client::getInstance()->getUuid();
        if(!empty($uuid)){
            $message['uuid'] =  $uuid;
        }

        if(empty($message['uuid'])) {
            $clientUUID = $this->getUuid();
            if(!empty($clientUUID)) {
                $message['uuid'] = $clientUUID;
            }
        }
        
        $ip = $this->getIp();
        if($ip) {
            $message['ip'] = $ip;    
        }
        
        $ssuid = $this->getSsuid();
        if($ssuid) {
            $message['ssuid'] = $ssuid;
        }
        
        $userAgent = $this->getUserAgent();
        if($userAgent) {
            $message['userAgent'] = $userAgent;
        }
        
        $snrsParams = $this->getSnrsParams();
        if($snrsParams) {
            $message['snr_params'] = $snrsParams;
        }

        if(isset($message['params']['time']) && $this->_is_timestamp($message['params']['time'])){
            $message['time'] = $message['params']['time'] = $message['params']['time'] * 1000;
            unset($message['params']['time']);
        } else if(isset($message['params']['time'])) {
            throw new SyneriseException('Parameter `time` have to be in timesamp format.');
        } else {
            $message['time'] = time() * 1000;
        }

        array_push($this->_requestQueue, $message);
    }



    /**
     * @return string
     */
    private function getIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            return '0.0.0.0';
        }

        return $ip;
    }

    /**
     * get user agent
     *
     * @return string
     */
    private function getUserAgent() {
        return !empty($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
    }


    /**
     * @return bool|string
     */
    public function getSnrsParams()
    {
        $snrsP = $this->_cookie->getCookieString('_snrs_params');
        if ($snrsP) {
            $dataSendSnrs = @json_decode($snrsP);
            if ($dataSendSnrs) {
                return $dataSendSnrs;
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    protected function getUuid()
    {
        if($this->_uuid) {
            return $this->_uuid;
        }

        $snrsP = $this->_cookie->getCookieString('_snrs_p');
        if ($snrsP) {
            $snrsP = explode('&', $snrsP);
            foreach ($snrsP as $snrs_part) {
                if (strpos($snrs_part, 'uuid:') !== false) {
                    return str_replace('uuid:', null, $snrs_part);
                }
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    private function getSsuid()
    {
        $snrsS = $this->_cookie->getCookieString('_snrs_sa');
        if ($snrsS) {
            $snrsS = explode('&', $snrsS);
            foreach ($snrsS as $snrs_part) {
                if (strpos($snrs_part, 'ssuid:') !== false) {
                    return str_replace('ssuid:', null, $snrs_part);
                }
            }
        }

        return false;
    }



    private function _is_timestamp($timestamp) {
        if(is_numeric($timestamp)
            && strtotime(date('d-m-Y H:i:s',$timestamp)) === (int)$timestamp) {
            return $timestamp;
        }
        return false;
    }
}
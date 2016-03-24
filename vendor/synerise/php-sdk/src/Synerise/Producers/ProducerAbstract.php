<?php 
namespace Synerise\Producers;

use Synerise\Producers\Client;
use Synerise\Exception\SyneriseException;


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
        if(!empty(Client::getInstance()->getCustomIdetify())){
        	$message['customIdentify'] =  Client::getInstance()->getCustomIdetify();
        }
        $clientUUID = $this->getUuid();
        if(!empty($clientUUID)) {
            $message['uuid'] = $clientUUID;
        }

        $message['ip'] = $this->getIp();

        $message['userAgent'] = $this->getUserAgent();

        $snrsParams = $this->getSnrsParams();
        if($snrsParams) {
            $message['snr_params'] = $snrsParams;
        }

        if(isset($message['params']['time']) && $this->_is_timestamp($message['params']['time'])){
//        	$message['time'] = date("Y-m-d\TH:i:sP",$message['params']['time']);
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
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
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
        $snrsP = isset($_COOKIE['_snrs_params']) && !empty($_COOKIE['_snrs_params'])?$_COOKIE['_snrs_params']:false;
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
    private function getUuid()
    {
        $snrsP = isset($_COOKIE['_snrs_p'])?$_COOKIE['_snrs_p']:false;
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

    private function _is_timestamp($timestamp) {
    	if(is_numeric($timestamp) 
    		&& strtotime(date('d-m-Y H:i:s',$timestamp)) === (int)$timestamp) {
        	return $timestamp;
    	}
    	return false;
    }
}
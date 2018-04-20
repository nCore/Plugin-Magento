<?php
namespace Synerise;

use GuzzleHttp\Collection;
use Synerise\Producers\Client;
use Synerise\Producers\Event;
use GuzzleHttp\Pool;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Subscriber\History;
use Synerise\Consumer\ForkCurlHandler;

class SyneriseTracker extends SyneriseAbstractHttpClient
{

    /**
     * An instance of the Client class (used to create/update client profiles)
     * @var Producers\Client
     */
    public $client;

    /**
     * An instance of the Event class (used for tracking custom event)
     * @var Producers\Event
     */
    public $event;

    /**
     * An instance of the Transaction class (used for tracking purchase event)
     * @var Producers\Transaction
     */
    public $transaction;

    
    /**
     * Instantiates a new SyneriseTracker instance.
     * @param array $config
     */
    public function __construct($config = array(), $logger = null)
    {
    	if(isset($config['allowFork']) && $config['allowFork'] == true){
			$config['handler'] = new ForkCurlHandler(array());
    	}

        parent::__construct($config, $logger);

        $this->client       = Producers\Client::getInstance();
        $this->event        = Producers\Event::getInstance();
        $this->transaction  = Producers\Transaction::getInstance();

    }

    /**
     * Flush the queue when we destruct the client with retries
     */
    public function __destruct() {
        $this->sendQueue();
    }

    public function sendQueue(){

        $data['json'] = array_merge(
            $this->event->getRequestQueue(),
            $this->transaction->getRequestQueue(),
            $this->client->getRequestQueue()
        );
        
        if(count($data['json']) == 0) {
            return;
        }

        try {
            $response = $this->post(SyneriseAbstractHttpClient::BASE_TCK_URL, $data);
        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
        }

        $this->flushQueue();

        if(isset($response) && substr($response->getStatusCode(),0,1) == '2') {
            return true;
        }
        return false;
    }

    public function flushQueue() {
        $this->event->reset();
        $this->transaction->reset();
        $this->client->reset();
    }

    /**
     * Gets the default configuration options for the client
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            'base_url' => self::BASE_TCK_URL,
            'headers' => [
                'Content-Type' => self::DEFAULT_CONTENT_TYPE,
                'Accept' => self::DEFAULT_ACCEPT_HEADER,
                'User-Agent' => self::USER_AGENT,
            ]
        ];
    }

    /**
     * Send form submit event.
     *
     * @param string $label
     * @param array $params allowed values: uuid*, source* , formType**, formData
     *
     * *required in system context
     * **equals label by default
     */
    public function formSubmit($label, $params = array())
    {
        $params["formType"] = isset($params["formType"]) ? $params["formType"] : $label;
        $this->sendEvent('form.submit', $label, $params);
    }

    public function sendEvent($action, $label, $params = array(), $category = null)
    {
        if(!isset($params['uuid']) && !empty($this->getUuid())){
            if($this->_context == self::APP_CONTEXT_CLIENT) {
                if(isset($params['email']) && !empty($params['email'])) {
                    $this->regenerateUuid($params['email']);
                } elseif(isset($params['formData'])) {
                    $formData = json_decode($params['formData']);
                    if(isset($formData->email) && !empty($formData->email)) {
                        $this->regenerateUuid($formData->email);
                    }
                }
            }
            $params['uuid'] = $this->getUuid();
        }

        if(!isset($params['source'])) {
            $params['source'] = $this->getSource();
        }

        $data['label'] = $label;
        $data['params'] = $params;
        $data['action'] = $action;
        $data['category'] = !empty($category) ? $category : $this->getCategoryBySource($params['source'], $action);

        try {
            $response = $this->put('http://tck.synerise.com/tracker/' . $this->_apiKey, array(
                'json' => $data,
                'timeout' => 1
            ));
        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
        }
        
        if(isset($response) && $response->getStatusCode() == '200') {
            return true;
        }
        return false;
    }

    public function renderJsScripts($trackingCode)
    {
        $jsSdkUrl = self::JS_SDK_URL;
        return <<<EOT
        <script>
            function onSyneriseLoad() {
                SR.init({
                    'trackerKey':'$trackingCode',
              });
            }

            (function(s,y,n,e,r,i,se){s['SyneriseObjectNamespace']=r;s[r]=s[r]||[],
             s[r]._t=1*new Date(),s[r]._i=0,s[r]._l=i;var z=y.createElement(n),
             se=y.getElementsByTagName(n)[0];z.async=1;z.src=e;se.parentNode.insertBefore(z,se);
             z.onload=z.onreadystatechange=function(){var rdy=z.readyState;
             if(!rdy||/complete|loaded/.test(z.readyState)){s[i]();z.onload = null;
             z.onreadystatechange=null;}};})(window,document,'script',
             '//$jsSdkUrl','SR', 'onSyneriseLoad');
        </script>
EOT;
    }

}
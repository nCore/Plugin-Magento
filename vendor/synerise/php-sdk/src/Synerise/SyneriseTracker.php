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
     * @var Producer\Event
     */
    public $event;

    /**
     * An instance of the Transaction class (used for tracking purchase event)
     * @var Producer\Transaction
     */
    public $transaction;


    /**
     * Instantiates a new SyneriseTracker instance.
     * @param array $config
     */
    public function __construct($config = array()) {

    	if(isset($config['allowFork']) && $config['allowFork'] == true){
			$config['handler'] = new ForkCurlHandler([]);
    	}

        parent::__construct($config);

		$this->client = Producers\Client::getInstance();
		$this->event = Event::getInstance();
		$this->transaction = Producers\Transaction::getInstance();

    	$config = Collection::fromConfig($config, static::getDefaultConfig(), static::$required);
		$this->configure($config);

    }

    /**
     * Flush the queue when we destruct the client with retries
     */
    public function __destruct() {
        
        $history = new History();
        $this->getEmitter()->attach($history);
        
    	$data['json'] = array_merge($this->event->getRequestQueue(), 
    		$this->transaction->getRequestQueue(), 
    		$this->client->getRequestQueue());
        
        if(count($data['json']) == 0) {
            return;
        }
        $request = $this->createRequest('POST', SyneriseAbstractHttpClient::BASE_TCK_URL, $data);
        $request->setHeader('Content-Type','application/json');

        try {
            $this->_log($request, 'TRACKER');
            $response = $this->send($request);
            $this->_log($response, 'TRACKER');

        } catch(\Exception $e) {

            $this->_log($e->getMessage(), 'TRACKER_ERROR');

        }
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

}
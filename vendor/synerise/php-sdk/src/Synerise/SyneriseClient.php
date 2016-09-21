<?php
namespace Synerise;

use Synerise\Exception\SyneriseException;
use Synerise\Producers\Client;
use Synerise\Producers\Event;
use GuzzleHttp\Pool;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message;

class SyneriseClient extends SyneriseAbstractHttpClient
{

    protected $_cache = array();


    public function getClientByCustomIdentify($numberCard) {
        return $this->getClientByParameter(array('customIdentify' => $numberCard));
    }

    public function getClientByParameter(array $fields)
    {
        try {
            $request = $this->createRequest("GET", SyneriseAbstractHttpClient::BASE_API_URL . '/client/?' . http_build_query($fields), array(
                'headers' => array ('content-type' => 'application/json')
            ));
            
            $this->_log($request, 'CLIENT');
            $response = $this->send($request);
            $this->_log($response, 'CLIENT');

            return $response;

        } catch (\Exception $e) {
            $this->_log($e->getMessage(), "ClientERROR");
            throw $e;
        }
    }

    public function batchAddOrUpdateClients(array $items)
    {
        $data = array();
        foreach($items as $item) {
            if(isset($item['email']) && isset($item['data'])) {
                $data[$item['email']] = $item['data'];
            }
        }
        
        if(!empty($data)) {

            try {
                $request = $this->createRequest("POST", SyneriseAbstractHttpClient::BASE_API_URL . '/client/batch', array(
                    'headers' => array ('content-type' => 'application/json'),
                    'json' => array('items' => $data)
                ));    
                
                $this->_log($request, 'CLIENT');
                $response = $this->send($request);
                $this->_log($response, 'CLIENT');

                return $response->json();
                
            } catch (\Exception $e) {
                $this->_log($e->getMessage(), "ClientsERROR");
                throw $e;
            }
        }
        
        return false;
    }

}
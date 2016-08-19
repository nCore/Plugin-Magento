<?php
namespace Synerise;

use Synerise\Exception\SyneriseException;
use GuzzleHttp\Pool;
use GuzzleHttp\Collection;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message;

class SyneriseDefault extends SyneriseAbstractHttpClient
{
    public function test() 
    {
        try {
            $request = $this->createRequest("GET", SyneriseAbstractHttpClient::BASE_API_URL . "/test");
            $this->_log($request, "Default");
            
            $response = $this->send($request);
            $this->_log($response, "Default");     

            if ($response->getStatusCode() != '200') {
                throw new Exception\SyneriseException('API Synerise not responsed 200.', 500);
            }
            
            $responseArray = $response->json();            
            return isset($responseArray['data']) ? $responseArray['data'] : null;                        

        } catch (\Exception $e) {

            $this->_log($e->getMessage(), "DefaultERROR");
            throw $e;
        }
    }
}
<?php
namespace Synerise;

use Synerise\Exception\SyneriseException;
use GuzzleHttp\Pool;
use GuzzleHttp\Collection;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message;

class SyneriseTrackingCode extends SyneriseAbstractHttpClient
{
    public function trackingcode($domain) 
    {
        try {
            $request = $this->createRequest("GET", SyneriseAbstractHttpClient::BASE_API_URL . "/trackingcode/.$domain");
            $this->_log($request, "TrackingCode");
            
            $response = $this->send($request);
            $this->_log($response, "TrackingCode");            

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
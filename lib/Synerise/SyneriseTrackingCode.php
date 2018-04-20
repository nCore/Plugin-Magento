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
            $response = $this->get(SyneriseAbstractHttpClient::BASE_API_URL . "/trackingcode/$domain");

            if ($response->getStatusCode() != '200') {
                throw new Exception\SyneriseException('API Synerise not responsed 200.', 500);
            }
            
            $responseArray = json_decode($response->getBody(), true);
            return isset($responseArray['data']) ? $responseArray['data'] : null;                        

        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
            throw $e;
        }
    }
}
<?php
namespace Synerise;

use GuzzleHttp\Collection;
use GuzzleHttp\Exception\RequestException;
use Synerise\Exception\SyneriseException;
use Synerise\Producers\Client;
use Synerise\Producers\Event;
use GuzzleHttp\Pool;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Message;
use GuzzleHttp\Message\Response;
use Synerise\Response\Newsletter as SyneriseResponseNewsletter;


class SyneriseNewsletter extends SyneriseAbstractHttpClient
{
    
    public function subscribe($email, $additionalParams = array())
    {

        try {
            $baseParams = array();
            $baseParams['email'] = $email;
            if(!empty($this->getUuid())){
                $baseParams['uuid'] = $this->getUuid();
            }

            if(isset($additionalParams['sex'])) { //@todo
                $baseParams['sex '] = $additionalParams['sex'];
            }

            /**
             * @var Response
             */
            $request = $this->createRequest("PUT", SyneriseAbstractHttpClient::BASE_API_URL . "/client/subscribe",
                array('body' => array_merge($baseParams, array('params' => $additionalParams)))
            );

            $this->_log($request, 'NEWSLETTER');

            $responseClass = 'GuzzleHttp\\Message\\Response';            
            
            try {
                
                $response = $this->send($request);
                $this->_log($response, 'NEWSLETTER');

                if ($response instanceof $responseClass) {
                    $responseNewsletter = new SyneriseResponseNewsletter($response->json());
                    return $responseNewsletter->success();
                }
                
            } catch (RequestException $e) {
                
                $response = $e->getResponse();
                $this->_log($response, 'NEWSLETTER');
                
                if ($e->getResponse() instanceof $responseClass) {
                    $responseNewsletter = new SyneriseResponseNewsletter($e->getResponse()->json());
                    return $responseNewsletter->fail();
                }

            }
            
            throw new SyneriseException('Unknown error', SyneriseException::UNKNOWN_ERROR);

        } catch (\Exception $e) {
            
            $this->_log($e->getMessage(), 'NEWSLETTER');
            throw $e;
        }

    }


}
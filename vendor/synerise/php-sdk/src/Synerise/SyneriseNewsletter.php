<?php
namespace Synerise;

use GuzzleHttp\Collection;
use Synerise\Exception\SyneriseException;
use Synerise\Producers\Client;
use Synerise\Producers\Event;
use GuzzleHttp\Pool;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Subscriber\History;
use Synerise\Consumer\ForkCurlHandler;
use GuzzleHttp\Message;
use GuzzleHttp\Message\Response;
use Synerise\Response\Newsletter as SyneriseResponseNewsletter;


class SyneriseNewsletter extends SyneriseAbstractHttpClient
{

    public function subscribe($email, $additionalParams = array())
    {

        try {

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

            $response = $this->send($request);

            $this->_log($response, 'NEWSLETTER');

            $class = 'GuzzleHttp\\Message\\Response';

            if ($response instanceof $class && $response->getStatusCode() == '200') {
                $responseNewsletter = new SyneriseResponseNewsletter($response->json());
                return $responseNewsletter->success();
            }
            throw new SyneriseException('API Synerise not responsed 200.', SyneriseException::API_RESPONSE_ERROR);
        }catch (\Exception $e) {
            $this->_log($e->getMessage(), 'NEWSLETTER');
            throw $e;
        }

    }


}
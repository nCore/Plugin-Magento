<?php
namespace Synerise;

use Synerise\Exception\SyneriseException;
use Synerise\Producers\Client;
use Synerise\Producers\Event;
use GuzzleHttp\Pool;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Subscriber\History;
use Synerise\Consumer\ForkCurlHandler;
use GuzzleHttp\Message;

class SyneriseClient extends SyneriseAbstractHttpClient
{

    protected $_cache = array();


    public function getClientByCustomIdentify($numberCard) {
        return $this->getClientByParameter(array('customIdentify' => $numberCard));
    }

    public function getClientByParameter(array $filds)
    {

        try {
            /**
             * @var Response
             */
            //$response = $this->get(SyneriseAbstractHttpClient::BASE_API_URL . '/coupons/active/' . $token);

            $request = $this->createRequest("GET", SyneriseAbstractHttpClient::BASE_API_URL . '/client/?' . http_build_query($filds));
            $response = $this->send($request);

            return $response;



        } catch (\Exception $e) {
            $this->_log($e->getMessage(), "CouponERROR");
            throw $e;
        }

    }



}
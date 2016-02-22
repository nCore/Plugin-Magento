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

class SyneriseCoupon extends SyneriseAbstractHttpClient
{

    protected $_cache = array();

    /**
     * @param $token
     * @return Coupon
     * @throws SyneriseException
     */
    public function getCoupon($token)
    {

        try {
            /**
             * @var Response
             */
            //$response = $this->get(SyneriseAbstractHttpClient::BASE_API_URL . '/coupons/active/' . $token);
            if (!isset($this->_cache[$token])) {
                $request = $this->createRequest("GET", SyneriseAbstractHttpClient::BASE_API_URL . '/coupons/active/' . $token);
                $this->_log($request, "Coupon");
                $response = $this->send($request);

                $this->_log($response, "Coupon");
                $class = 'GuzzleHttp\\Message\\Response';
                if ($response instanceof $class && $response->getStatusCode() == '200') {
                    $this->_cache[$token] = new Response\Coupon($response->json());
                } else {
                    throw new SyneriseException('API Synerise not responsed 200.', SyneriseException::API_RESPONSE_ERROR);
                }


            }

            return $this->_cache[$token];


        } catch (\Exception $e) {
            $this->_log($e->getMessage(), "CouponERROR");
            throw $e;
        }

    }


    /**
     * @param $token
     * @return void
     * @throws SyneriseException
     *         code: 20105 - Coupon.Use.AlreadyUsed
     *         code: -1 - Coupon.UnknownError
     *         code: 500 - HTTP error
     */
    public function useCoupon($token)
    {
        try {
            if (isset($this->_cache[$token])) {
                unset($this->_cache[$token]);
            }

            $this->_log('USE '.$token, "Coupon");
            $response = $this->post(SyneriseAbstractHttpClient::BASE_API_URL . "/coupons/active/$token/use");
            $this->_log($response, "Coupon");
            if ($response->getStatusCode() == '200') {
                $responseArray = $response->json();
                switch ($responseArray['code']) {
                    case 1:
                        return true;
                    case 20105:
                        throw new Exception\SyneriseException('Coupon.Use.AlreadyUsed', SyneriseException::COUPON_ALREADY_USED);
                    case 20101:
                        throw new Exception\SyneriseException('Coupon.Use.NotFound', SyneriseException::COUPON_NOT_FOUND);
                    default:
                        throw new Exception\SyneriseException('Coupon.UnknownError', SyneriseException::UNKNOWN_ERROR);
                }
            }
            throw new Exception\SyneriseException('API Synerise not responsed 200.', 500);

        } catch (\Exception $e) {
            $this->_log($e->getMessage(), "CouponERROR");
            throw $e;
        }
    }

}
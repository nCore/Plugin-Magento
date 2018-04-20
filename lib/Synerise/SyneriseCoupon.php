<?php
namespace Synerise;

use Synerise\Exception\SyneriseException;

class SyneriseCoupon extends SyneriseAbstractHttpClient
{

    protected $_cache = array();

    /**
     * Get activated coupon details
     *
     * @param $code
     * @return Response\ActiveCoupon
     * @throws SyneriseException
     */
    public function getActiveCoupon($code, array $options = array())
    {

        try {
            /**
             * @var Response
             */
            if (!isset($this->_cache[$code])) {

                $url = SyneriseAbstractHttpClient::BASE_API_URL . '/coupons/active/' . $code;

                $defaults = array(
                    'uuid'          => null,
                    'token'         => null,
                    'includeCoupon' => false
                );

                $options = array_intersect_key($options, $defaults);

                if(!empty($options)) {
                    $url .= '?'.http_build_query($options);
                }

                $response = $this->get($url);

                if ($response->getStatusCode() == '200') {

                    if ($response->getStatusCode() != '200') {
                        throw new Exception\SyneriseException('API Synerise not responsed 200.', 500);
                    }

                    $json = json_decode($response->getBody(), true);

                    if (isset($json['data']) && isset($json['data']['coupon'])) {
                        $activeCoupon = new Response\ActiveCoupon($json);
                        $this->_cache[$code] = $activeCoupon;
                    }
                } else {
                    throw new SyneriseException('API Synerise not responsed 200.', SyneriseException::API_RESPONSE_ERROR);
                }
            }

            return isset($this->_cache[$code]) ? $this->_cache[$code] : new Response\ActiveCoupon();


        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
            throw $e;
        }

    }

    /**
     *
     * @param type $limit
     * @param type $offset
     * @return boolean|\Synerise\Response\Collection\Coupon
     * @throws SyneriseException
     */
    public function getCoupons($limit = 100, $offset = 0)
    {

        try {
            $query = array(
                'pagination[limit]' => (int) $limit,
                'pagination[offset]' => (int) $offset,
            );
            
            $response = $this->get(SyneriseAbstractHttpClient::BASE_API_URL . '/coupons/', array('query' => $query));

            if ($response->getStatusCode() == '200') {
                return new Response\Collection\Coupon(json_decode($response->getBody(), true));
            } else {
                throw new SyneriseException('API Synerise not responsed 200.', SyneriseException::API_RESPONSE_ERROR);
            }

            return false;

        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
            throw $e;
        }

    }

    /**
     * @param $code
     * @return void
     * @throws SyneriseException
     *         code: 20105 - Coupon.Use.AlreadyUsed
     *         code: -1 - Coupon.UnknownError
     *         code: 500 - HTTP error
     */
    public function useActiveCoupon($code)
    {
        try {
            if (isset($this->_cache[$code])) {
                unset($this->_cache[$code]);
            }

            $response = $this->post(SyneriseAbstractHttpClient::BASE_API_URL . "/coupons/active/$code/use");

            if ($response->getStatusCode() == '200') {
                $responseArray = json_decode($response->getBody(), true);

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
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
            throw $e;
        }
        return false;
    }

    public function activateCoupon($couponUuid, $clientUuid = null)
    {
        try {
            $response = $this->post(SyneriseAbstractHttpClient::BASE_API_URL . "/coupons/$couponUuid/activate");

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

    public function updateCoupon($couponUuid, $parameters)
    {
        try {
            $response = $this->patch(SyneriseAbstractHttpClient::BASE_API_URL . "/coupons/$couponUuid",  array('json' => $parameters));

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
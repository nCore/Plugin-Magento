<?php
namespace Synerise\Response\Collection;
use Synerise\Exception\SyneriseException;

class Coupon extends AbstractCollection
{
    public function __construct(array $response)
    {
        $this->_data = array();
        if(isset($response['data']) && isset($response['data']['coupons'])) {
            foreach($response['data']['coupons'] as $key => $item) {
                $item['uuid'] = $key;
                $this->_data[$key] = (new \Synerise\Response\Coupon($item));
            }
            parent::__construct($response);
        } else {
            throw new SyneriseException('Missing "data" in API resonse.', SyneriseException::API_RESPONSE_INVALID);
        }
    }
}
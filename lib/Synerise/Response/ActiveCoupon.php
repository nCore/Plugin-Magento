<?php
namespace Synerise\Response;

class ActiveCoupon extends AbstractResponse
{
    /**
     *
     * @var Synerise\Response\Coupon
     */
    private $_coupon = null;    
    
    /**
     *
     * @var string
     */
    private $_usedAt = null;

    /**
     *
     * @var string
     */
    private $_uuid = null;

    /**
     *
     * @var string
     */
    private $_activatedAt = null;

    /**
     *
     * @var string
     */
    private $_expiresAt = null;

    /**
     *
     * @var bool
     */
    private $_isValid = false;

    /**
     *
     * @param array $response
     * @return void
     */
    public function __construct($response = array())
    {
        if(isset($response['data'])) {
            parent::__construct($response);
            $data = $response['data'];
        } else {
            $data = $response;
        }

        $this->_uuid        = isset($data['uuid']) ? $data['uuid'] : null;
        $this->_isValid     = isset($data['isValid']) ? $data['isValid'] : false;
        $this->_usedAt      = isset($data['usedAt']) ? $data['usedAt'] : null;
        $this->_activatedAt = isset($data['activatedAt']) ? $data['activatedAt'] : null;
        $this->_expiresAt   = isset($data['expiresAt']) ? $data['expiresAt'] : null;
        
        if(isset($data['coupon'])) {
            $couponData = $data['coupon'];
            $couponData['uuid'] = $this->_uuid;
            $this->_coupon = new Coupon(array('data' => $couponData));
        }
    }

    /**
     * Coupon can be used
     * @return bool
     */
    public function canUse()
    {
        return $this->getIsValid() === true;
    }

    /**
     * Coupon is active
     * @return bool
     */
    public function isActive()
    {
        return $this->_status == 1111; //@todo docelowo kupon jest aktywny ale nie spełnia warunków koszyka
    }

    public function getMessage()
    { //@todo jeśli kupon jest aktwyny ale brakuje innych warunków aby go wykorzystać (pamiętać o różnych warunkach i o wersjach językowych, walutach).
        if ($this->isActive()) {
            return array(
                'code' => 881,
                'message' => '20',
                'description' => 'Kup coś za 20zł aby móc użyć kuponu'
            );
        }

        return;
    }

    public function getUsedAt()
    {
        return $this->_usedAt;
    }

    public function setUsedAt($usedAt)
    {
        $this->_usedAt = !empty($usedAt) && is_numeric($usedAt) ? true : false;
    }

    public function getUuid()
    {
        return $this->_uuid;
    }

    public function setUuid($uuid)
    {
        $this->_uuid = !empty($uuid) && is_numeric($uuid) ? true : false;
    }
    
    public function getActivatedAt()
    {
        return $this->_activatedAt;
    }

    public function setActivatedAt($activatedAt)
    {
        $this->_activatedAt = !empty($activatedAt) && is_numeric($activatedAt) ? true : false;
    }

    public function getExpiresAt()
    {
        return $this->_expiresAt;
    }

    public function setExpiresAt($expiresAt)
    {
        $this->_expiresAt = !empty($expiresAt) && is_numeric($expiresAt) ? true : false;
    }

    public function getIsValid()
    {
        return $this->_isValid;
    }

    public function setIsValid($isValid)
    {
        $this->_isValid = !empty($isValid) && is_numeric($isValid) ? true : false;
    }
    
    public function getCoupon()
    {
        return $this->_coupon;
    }

    public function setCoupon($coupon)
    {
        $this->_coupon = !empty($coupon) && is_numeric($coupon) ? true : false;
    }

}
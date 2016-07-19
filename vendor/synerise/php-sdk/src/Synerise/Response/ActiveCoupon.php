<?php
namespace Synerise\Response;

class ActiveCoupon
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
    private $_redeemedAt = null;

    /**
     *
     * @var string
     */
    private $_couponUuid = null;

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

    public function __construct($activeCoupon = array())
    {
        $this->_redeemedAt = isset($activeCoupon['redeemedAt']) ? $activeCoupon['redeemedAt'] : null;
        $this->_couponUuid = isset($activeCoupon['couponUuid']) ? $activeCoupon['couponUuid'] : null;
        $this->_activatedAt = isset($activeCoupon['activatedAt']) ? $activeCoupon['activatedAt'] : null;
        $this->_expiresAt = isset($activeCoupon['expiresAt']) ? $activeCoupon['expiresAt'] : null;
        $this->_isValid = isset($activeCoupon['isValid']) ? $activeCoupon['isValid'] : false;
        $this->_coupon = isset($activeCoupon['coupon']) ? new Coupon($activeCoupon['coupon']) : null;
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

    public function getRedeemedAt()
    {
        return $this->_redeemedAt;
    }

    public function setRedeemedAt($redeemedAt)
    {
        $this->_redeemedAt = !empty($redeemedAt) && is_numeric($redeemedAt) ? true : false;
    }

    public function getCouponUuid()
    {
        return $this->_couponUuid;
    }

    public function setCouponUuid($couponUuid)
    {
        $this->_couponUuid = !empty($couponUuid) && is_numeric($couponUuid) ? true : false;
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
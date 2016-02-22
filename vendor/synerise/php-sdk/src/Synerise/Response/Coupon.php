<?php
namespace Synerise\Response;

class Coupon
{

    /**Ważność kuponu
     * @var int
     */
    private $_status = false;

    /**
     *  Typ zniżki
     * @var string "percent", "cost"
     */
    private $_discount = null;

    /**
     *
     * @var string
     */
    private $_redeemedAt = null;

    /**
     * Wartość zniżki
     *
     * @var float
     */
    private $_value = null;

    /**
     *
     * @var string
     */
    private $_name;

    /**
     *
     * @var string
     */
    private $_uuid;

    /**
     *
     * @var string
     */
    private $_type;

    /**
     *
     * @var string
     */
    private $_start;

    /**
     *
     * @var string
     */
    private $_expiration;

    /**
     *
     * @var string
     */
    private $_description;

    /**
     *
     * @var string
     */
    private $_additionalDescription;

    public function __construct($response)
    {

        $this->_status = $response['code'];

        if (isset($response['data'])) {
            $coupon = $response['data']['coupon'];
            $this->_name = $coupon['name'];
            $this->_uuid = $coupon['uuid'];
            $this->_discount = $coupon['discount'];
            $this->_type = $coupon['type'];
            $this->_value = $coupon['value'];
            $this->_start = $coupon['start'];
            $this->_expiration = $coupon['expiration'];
            $this->_description = $coupon['description'];
            $this->_additionalDescription = $coupon['additionalDescription'];
            $this->_redeemedAt = !empty($response['data']['redeemedAt']) && is_numeric($response['data']['redeemedAt']) ? true : false;
        };


    }


    /**
     * Coupon can be used
     * @return bool
     */
    public function canUse()
    {
        return $this->_status == 1 && $this->_redeemedAt === false;
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

    public function getUuid()
    {
        return $this->_uuid;
    }

    public function getDiscount()
    {
        return $this->_discount;
    }

    public function getDescription()
    {
        return $this->_description;
    }

    public function getAdditionalDescription()
    {
        return $this->_additionadescription;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getValue()
    {
        return (float)$this->_value;
    }

    public function getStart()
    {
        return $this->_start;
    }

    public function getExpiration()
    {
        return $this->_expiration;
    }
}
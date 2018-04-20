<?php
namespace Synerise\Response;

class Coupon extends AbstractResponse
{

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
     * discount type
     *
     * @var string "percent", "cost"
     */
    private $_discountType;

    /**
     * discount value
     *
     * @var float
     */
    private $_discountValue = null;

    /**
     *
     * @var string
     */
    private $_code = null;

    /**
     *
     * @var int
     */
    private $_startAt;

    /**
     *
     * @var int
     */
    private $_endAt;

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

    /**
     *
     * @var string
     */
    private $_termsUrl;

    /**
     *
     * @var string
     */
    private $_image;

    /**
     *
     * @var int
     */
    private $_validFor;

    /**
     *
     * @var int
     */
    private $_limitPerClient;

    /**
     *
     * @var int
     */
    private $_limitGlobal;

    /**
     *
     * @var boolean
     */
    private $_activationRequired;

    /**
     *
     * @var boolean
     */
    private $_strictUse;

    /**
     *
     * @var array
     */
    private $_tags;

    /**
     *
     * @var array
     */
    private $_locations;

    /**
     *
     * @var array
     */
    private $_attributes;

    /**
     *
     * @var array
     */
    private $_products;

    public function __construct(array $response)
    {
        if(isset($response['data'])) {
            parent::__construct($response);
            $data = $response['data'];
        } else {
            $data = $response;
        }

        $this->_uuid                    = isset($data['uuid']) ? $data['uuid'] : null;
        $this->_name                    = isset($data['name']) ? $data['name'] : null;
        $this->_description             = isset($data['description']) ? $data['description'] : null;
        $this->_additionalDescription   = isset($data['additionalDescription']) ? $data['additionalDescription'] : null;
        $this->_termsUrl                = isset($data['termsUrl']) ? $data['termsUrl'] : null;
        $this->_discountType            = isset($data['discountType']) ? $data['discountType'] : null;
        $this->_discountValue           = isset($data['discountValue']) ? $data['discountValue'] : null;
        $this->_startAt                 = isset($data['startAt']) ? $data['startAt'] : null;
        $this->_endAt                   = isset($data['endAt']) ? $data['endAt'] : null;
        $this->_image                   = isset($data['image']) ? $data['image'] : null;
        $this->_validFor                = isset($data['validFor']) ? $data['validFor'] : null;
        $this->_code                    = isset($data['code']) ? $data['code'] : null;
        $this->_tags                    = isset($data['tags']) ? $data['tags'] : null;
        $this->_locations               = isset($data['locations']) ? $data['locations'] : null;
        $this->_attributes              = isset($data['attributes']) ? $data['attributes'] : null;
        $this->_products                = isset($data['products']) ? $data['products'] : null;
        $this->_limitGlobal             = isset($data['limitGlobal']) ? $data['limitGlobal'] : null;
        $this->_limitPerClient          = isset($data['limitPerClient']) ? $data['limitPerClient'] : null;
        $this->_activationRequired      = isset($data['activationRequired']) ? $data['activationRequired'] : null;
        $this->_strictUse               = isset($data['strictUse']) ? $data['strictUse'] : null;
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

    public function getDiscountType()
    {
        return $this->_discountType;
    }

    public function getDiscountValue()
    {
        return (float) $this->_discountValue;
    }

    public function getStartAt()
    {
        return $this->_startAt;
    }

    public function getEndAt()
    {
        return $this->_endAt;
    }

    public function getTermsUrl()
    {
        return $this->_termsUrl;
    }

    public function getImage()
    {
        return $this->_image;
    }

    public function getValidFor()
    {
        return $this->_validFor;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function getTags()
    {
        return $this->_tags;
    }

    public function getLocations()
    {
        return $this->_locations;
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function getProducts()
    {
        return $this->_products;
    }

    public function getLimitGlobal()
    {
        return $this->_limitGlobal;
    }

    public function getLimitPerClient()
    {
        return $this->_limitPerClient;
    }

    public function getStrictUse()
    {
        return $this->_strictUse;
    }
}
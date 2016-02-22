<?php
require_once Mage::getBaseDir().'/vendor/autoload.php';

class Synerise_Coupon_Model_Coupon
{
    protected $_couponCode;    
    protected $_allowedValues = array(
            'cost'      => Mage_SalesRule_Model_Rule::CART_FIXED_ACTION,
            'percent'   => Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION        
    );
    protected $_mageCoupon;
    protected $_mageRule;
    protected $_syneriseCoupon;
    protected $_syneriseCouponInstance;
    protected $_ruleValidated;

    public function __construct()
    {


        $this->apiKey = Mage::getStoreConfig('synerise_integration/api/key');
        $this->_syneriseCouponInstance = Synerise\SyneriseCoupon::getInstance([
            'apiKey' => $this->apiKey,
            'apiVersion' => '2.0',
            'allowFork' => true,
        ]);
        $this->_syneriseCouponInstance->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');
     
        $this->_syneriseCouponInstance->setDefaultOption('verify', false);
    }
    
    /*
     * Bind coupon with promo rule, based on discount type (cost, percent).
     * 
     * @return boolean false if no changes applied
     */
    public function fixCouponRuleRelation() 
    {
        $this->_updatePromoRule();

        $mageCoupon = $this->_getMageCoupon();        
        $rule = $this->_getMageRule();
        
        // no changes
        if($mageCoupon->getRuleId() == $rule->getId() && $rule->getId() && !$rule->hasDataChanges()) {
            return false;
        }
        
        if($this->_updateCoupon()) {
            // coupon updated
            return true;
        }

        // no changes
        return false;        
    }
    
    /*
     * Fetch and set discount amount if valid
     * else remove coupon code from quote
     * 
     * @return boolean false if coupon cannot be used
     */
    public function applyDiscount()
    {
        $snrsCoupon = $this->_getSyneriseCoupon();

        // coupon valid
        if ($snrsCoupon->canUse()) {

            // set discount amount
            $discountAmount = $snrsCoupon->getValue();
            $mageRule = $this->_getMageRule();
            $mageRule->setDiscountAmount($discountAmount);           
            return true;
             
        // coupon invalid (possibly redeemed)
        } else {
            //remove coupon code
            $quote = Mage::getModel('checkout/cart')->getQuote();
            if($quote->getId()) {
                $quote
                    ->setCouponCode('')              
                    ->save(); 
            }
            return false;
        }
        return false;
    }
    
    /*
     * mark coupon as used
     */
    public function useCoupon()
    {
        /**
         * @var $snrs Synerise\SyneriseCoupon
         */
        $snrs = $this->_getSyneriseCouponInstance();
        $snrs->useCoupon($this->_couponCode);
    }
    
    public function setCouponCode($couponCode)
    {
        $this->_couponCode = $couponCode;
    }
    
    public function setRule($rule)
    {
        $coupon = $this->_getMageCoupon();
        if($coupon->getRuleId() == $rule->getId()) {
            $this->_mageRule = $rule;
        }
    }
    
    /*
     * get magento coupon entity
     * 
     * @return Mage_SalesRule_Model_Coupon
     */
    protected function _getMageCoupon($force = false)
    {
        if($force || !$this->_mageCoupon instanceof Mage_SalesRule_Model_Coupon) {
            $this->_mageCoupon = Mage::getModel('salesrule/coupon')->load($this->_couponCode, 'code');          
        }
        return $this->_mageCoupon;
    }
    
    /*
     * get magento salesrule bound with coupon
     * 
     * @return Mage_SalesRule_Model_Rule
     */
    protected function _getMageRule($force = false)
    {
        if($force || !$this->_mageRule instanceof Mage_SalesRule_Model_Rule) {
            $syneriseCoupon = $this->_getSyneriseCoupon();
            $this->_mageRule = Mage::getModel('salesrule/rule')->load($syneriseCoupon->getUuid(),'synerise_uuid');
        }
        return $this->_mageRule;
    }
    
    /*
     * get synerise coupon
     * 
     * @return \Synerise\Response\Coupon
     */
    protected function _getSyneriseCoupon($force = false)
    {
        $couponCode = $this->_couponCode;

        $class = "\\Synerise\\Response\\Coupon";
        if($force || !$this->_syneriseCoupon instanceof $class) {
            $syneriseCouponInstance = $this->_getSyneriseCouponInstance();
            $this->_syneriseCoupon = $syneriseCouponInstance->getCoupon($couponCode);
        }

        return $this->_syneriseCoupon;
    }
    
    protected function _getSyneriseCouponInstance() 
    {
        return $this->_syneriseCouponInstance;
    }

    protected function _updatePromoRule() {
        $syneriseCoupon = $this->_getSyneriseCoupon(); 
        // load or create
        $rule = $this->_getMageRule();

        if(!isset($this->_allowedValues[$syneriseCoupon->getDiscount()])) {
            throw new Exception($syneriseCoupon->getName() . 'invalid rule action: ' . $syneriseCoupon->getDiscount());
        } 
        
        // prepare data
        $name = '[synerise] '.$syneriseCoupon->getName();
        $uuid = $syneriseCoupon->getUuid();
        $action = $this->_allowedValues[$syneriseCoupon->getDiscount()];        
        $customerGroupIds = Mage::getModel('customer/group')->getCollection()->getAllIds();
        $websiteIds = Mage::getModel('core/website')->getCollection()
                ->addFieldToFilter('website_id', array('neq' => 0))->getAllIds();        
        
        // new rule
        if($rule->getSyneriseUuid() != $uuid) {
            
            // set configurable opts
            $rule->setIsActive(1)
                ->setSortOrder(0)                    
                ->setStopRulesProcessing(0)              
                ->setCustomerGroupIds($customerGroupIds)
                ->setWebsiteIds($websiteIds)                    
                ->setProductIds('')                    
                ->setConditionsSerialized('')
                ->setActionsSerialized('');
        }
        
        // "one_time", "multiple_time", "unlimited_use"
        switch($syneriseCoupon->getType()):
            case 'unlimited_use':
                $usesPerCoupon = 0;
                break;
            case 'multiple_time':
                $usesPerCoupon = 0;
                break;    
            case 'one_time':
                $usesPerCoupon = 1;
                break;
            default:
                $usesPerCoupon = 1;
        endswitch;
        
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($syneriseCoupon->getStart()));
        $fromDate = date('Y-m-d', $dateTimestamp);        
        
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($syneriseCoupon->getExpiration()));
        $toDate = date('Y-m-d', $dateTimestamp);        

        // update or create
        $rule->setName($name)
            ->setDescription('This rule is used by Synerise Coupon module. Please edit responsibly.'
                    .PHP_EOL. '--------------------------------------------------------------------'
                    .PHP_EOL.$syneriseCoupon->getDescription())
            ->setSyneriseUuid($uuid)                
            ->setFromDate($fromDate)
            ->setToDate($toDate)
            ->setUsesPerCoupon($usesPerCoupon)
            ->setSimpleAction($action)                
            ->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
            ->setUseAutoGeneration(1)
            ->setUsesPerCustomer(0)
            ->setDiscountAmount(0)
            ->setDiscountStep(0);

        if($rule->hasDataChanges()) {
            $this->_rule = $rule;
            $rule->save();
            return true;
        }
            
        return false;
    }

    protected function _updateCoupon() {
        $mageCoupon = $this->_getMageCoupon();
        $rule = $this->_getMageRule();

        // new coupon, create
        if(!$mageCoupon->getId()) {
            /** @var $coupon Mage_SalesRule_Model_Coupon */
            $mageCoupon = Mage::getModel('salesrule/coupon');                

            $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
            $now = date('Y-m-d', $currentTimestamp);

            $mageCoupon->setCreatedAt($now);
        }
        
        $expirationDate = $rule->getToDate();
        if ($expirationDate instanceof Zend_Date) {
            $expirationDate = $expirationDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        
        // update or create
        $mageCoupon
            ->setRuleId($rule->getRuleId())
            ->setUsageLimit($rule->getUsesPerCoupon())
            ->setUsagePerCustomer($rule->getUsesPerCustomer())
            ->setExpirationDate($expirationDate)
            ->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
            ->setCode($this->_couponCode);

        if($mageCoupon->hasDataChanges()) {
            $mageCoupon->save(); 
            $this->_mageCoupon = $mageCoupon;
            return true;
        }
        return false;
    }    
    
    /*
     * Check if coupon exists & is bound with synerise rule
     * else check if coupon exsists in synerise
     * 
     * @return boolean
     */
    public function isSyneriseCoupon() 
    {
        // coupon format invalid
        if(!$this->validateFormat($this->_couponCode)) {
            return false;
        }

        // coupon exists
        $mageCoupon = $this->_getMageCoupon();
        if($mageCoupon->getId()) {
            $rule = $this->_getMageRule();

            // non-synerise rule, continue
            if(!$this->isSyneriseRule($rule)) {
                return false;
            }
            
        }

        // get synerise coupon
        $syneriseCoupon = $this->_getSyneriseCoupon();

        // coupon invalid, continue
        if (!$syneriseCoupon->canUse()) {
            return false;
        }
        
        return true;
    }
    
    public function isSyneriseRule($rule) 
    {
        if(!preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/i',$rule->getSyneriseUuid())) {
            return false;
        }
        return true;
    }
    
    // validate code for default format
    public function validateFormat($couponCode) 
    {
        if($this->validateFormatFlag() && !preg_match('/^[\d]{13}$/',$couponCode)) {
            return false;
        }
        return true;
    }
    
    public function isEnabled()
    {
        return Mage::getStoreConfig('synerise_coupon/general/enable');
    }    
    
    public function validateFormatFlag() 
    {
        return Mage::getStoreConfig('synerise_coupon/general/validate_format');
    }
    
    public function log($msg)
    {
        Mage::log($msg, true, 'synerise_coupon_use.log');                    
    }
}
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
    protected $_syneriseActiveCoupon;
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
        $syneriseCoupon = $this->_getSyneriseCoupon(); 
        $mageCoupon = $this->_getMageCoupon();        
        $mageRule = $this->_getMageRule();

        $this->_updatePromoRule($mageRule, $syneriseCoupon);        
        
        // no changes
        if($mageCoupon->getRuleId() == $mageRule->getId() && $mageRule->getId() && !$mageRule->hasDataChanges()) {
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
        $activeCoupon = $this->_getSyneriseActiveCoupon();

        // coupon valid
        if ($activeCoupon && $activeCoupon->canUse()) {
            $coupon = $activeCoupon->getCoupon();
            if($coupon && $coupon->getValue()) {
                $mageRule = $this->_getMageRule();
                if($mageRule) {
                    // set discount amount
                    $mageRule->setDiscountAmount($coupon->getValue());  
                    $mageRule->setDiscountQty(null);  
                    return true;
                }
            }
        }
        
        $this->removeQuoteCouponCode();

        return false;
    }
    
    public function removeQuoteCouponCode()
    {
        // coupon invalid (possibly redeemed), remove
        $quote = Mage::getModel('checkout/cart')->getQuote();
        if($quote->getId()) {
            $quote
                ->setCouponCode('')              
                ->save(); 
        }
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
            
            // get coupon if exists
            $mageCoupon = $this->_getMageCoupon();
            if($mageCoupon && $mageCoupon->getRuleId()) {
                $this->_mageRule = Mage::getModel('salesrule/rule')->load($mageCoupon->getRuleId());
            } else {
                // load by coupon uuid
                $syneriseCoupon = $this->_getSyneriseCoupon();
                if($syneriseCoupon && $syneriseCoupon->getUuid()) {
                    $this->_mageRule = Mage::getModel('salesrule/rule')->load($syneriseCoupon->getUuid(),'synerise_uuid');
                }
            }
        }
        return $this->_mageRule;
    }
    
    /*
     * get synerise coupon
     * 
     * @return \Synerise\Response\ActiveCoupon
     */
    protected function _getSyneriseCoupon($force = false)
    {
        $class = "\\Synerise\\Response\\Coupon";
        if($force || !$this->_syneriseCoupon instanceof $class) {
            if($this->_couponCode) {
                // get coupon data form activeCoupon
                $activeCoupon = $this->_getSyneriseActiveCoupon();
                $this->_syneriseCoupon = $activeCoupon ? $activeCoupon->getCoupon() : null;
            }
        }

        return $this->_syneriseCoupon;
    }
    
    /*
     * get synerise Active coupon
     * 
     * @return \Synerise\Response\ActiveCoupon
     */
    protected function _getSyneriseActiveCoupon($force = false)
    {
        $class = "\\Synerise\\Response\\ActiveCoupon";
        if($force || !$this->_syneriseCoupon instanceof $class) {
            $this->_syneriseCoupon = $this->_getSyneriseCouponInstance()
                    ->getActiveCoupon($this->_couponCode);
        }

        return $this->_syneriseCoupon;
    }
    
    /*
     * get synerise coupon
     * 
     * @return \Synerise\Response\Coupon
     */
    protected function _getSyneriseCoupons()
    {
        $syneriseCouponInstance = $this->_getSyneriseCouponInstance();
        return $syneriseCouponInstance->getCoupons();
    }
    
    protected function _getSyneriseCouponInstance() 
    {
        return $this->_syneriseCouponInstance;
    }

    protected function _updatePromoRule($rule,$syneriseCoupon) {

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
        
        $usesPerCoupon = 0;
        // "one_time", "multiple_time", "unlimited_use"
//        switch($syneriseCoupon->getType()):
//            case 'unlimited_use':
//                $usesPerCoupon = 0;
//                break;
//            case 'multiple_time':
//                $usesPerCoupon = 0;
//                break;    
//            case 'one_time':
//                $usesPerCoupon = 1;
//                break;
//            default:
//                $usesPerCoupon = 1;
//        endswitch;
        
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($syneriseCoupon->getStart()));
        $fromDate = date('Y-m-d', $dateTimestamp);        
        
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($syneriseCoupon->getExpiration()));
        $toDate = date('Y-m-d', $dateTimestamp);        

        // update or create
        $rule->setName($name)
            ->setDescription($syneriseCoupon->getDescription())
            ->setSyneriseUuid($uuid)                
            ->setFromDate($fromDate)
            ->setToDate($toDate)
            ->setUsesPerCoupon($usesPerCoupon)
            ->setSimpleAction($action)                
            ->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
            ->setUseAutoGeneration(1)
            ->setUsesPerCustomer(0)
            ->setDiscountAmount($syneriseCoupon->getValue())
            ->setDiscountQty(null);          
//            ->setDiscountStep(0);

        // @todo save only on change
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
    
    public function importAllCoupons()
    {
        $coupons = $this->_getSyneriseCoupons();
        foreach($coupons as $coupon) {
            $rule = Mage::getModel('salesrule/rule')->load($coupon->getUuid(),'synerise_uuid');
            $this->_updatePromoRule($rule,$coupon);
        }
        return count($coupons);
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
        if($mageCoupon && $mageCoupon->getId()) {
            $rule = $this->_getMageRule();
            
            // non-synerise rule, continue
            if(!$this->isSyneriseRule($rule)) {
                return false;
            }
            
        }

        // get synerise coupon
        $syneriseCoupon = $this->_getSyneriseActiveCoupon();

        // coupon invalid, continue
        if (!$syneriseCoupon->canUse()) {
            return false;
        }
        
        return true;
    }
    
    public function isSyneriseRule($rule) 
    {
        if(!$rule || !$rule->getSyneriseUuid()) {
            return false;
        }
        
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
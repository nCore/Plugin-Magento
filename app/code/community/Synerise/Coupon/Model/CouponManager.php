<?php
require_once Mage::getBaseDir().'/vendor/autoload.php';

class Synerise_Coupon_Model_CouponManager extends Mage_Core_Model_Abstract
{
    protected $_allowedValues = array(
            'cost'      => Mage_SalesRule_Model_Rule::CART_FIXED_ACTION,
            'percent'   => Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION        
    );
    protected $_syneriseRuleClass   = '\\Synerise\\Response\\Coupon';
    protected $_syneriseCouponClass = '\\Synerise\\Response\\ActiveCoupon';

    protected function _construct()
    {
        $syneriseCouponInstance = Synerise\SyneriseCoupon::getInstance([
            'apiKey' => Mage::getStoreConfig('synerise_integration/api/key'),
            'apiVersion' => '2.0'
        ]);
        $syneriseCouponInstance->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise_coupon.log');
        $syneriseCouponInstance->setDefaultOption('verify', false);
        
        $this->setSyneriseCouponInstance($syneriseCouponInstance);
    }
    
    /*
     * Get all rules from current profile & create/update
     */
    public function updateAllRules()
    {
        $syneriseRules = $this->getSyneriseRules();
        foreach($syneriseRules as $syneriseRule) {
            $rule = Mage::getModel('salesrule/rule')->load($syneriseRule->getUuid(), 'synerise_uuid');
            $this->_updateRule($rule,$syneriseRule);
        }
        return count($syneriseRules);
    }
    
    /*
     * Bind coupon with promo rule, based on discount type (cost, percent).
     * 
     * @return boolean false if no changes applied
     */
    public function updateCouponRuleRelation()
    {
        $coupon = $this->getCoupon();
        $rule = $this->getRule();
        
        $syneriseRule = $this->getSyneriseRule();  

        $this->_updateRule($rule, $syneriseRule);        
          
        // no changes
        if($rule->getId() && $coupon->getRuleId() == $rule->getId() && !$rule->hasDataChanges()) {
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
     * Tests coupon validity via synerise
     * 
     * @return boolean
     */
    public function applyCoupon()
    {
        $syneriseCoupon = $this->getSyneriseCoupon();

        // coupon valid
        if (!$syneriseCoupon || !$syneriseCoupon->canUse()) {
            $this->removeQuoteCouponCode();
            return false;
        }
        
        return true;
    }

    /*
     * Remove coupon code from current quote
     */
    public function removeQuoteCouponCode()
    {
        $quote = Mage::getModel('checkout/cart')->getQuote();
        if($quote->getId()) {
            $quote
                ->setCouponCode('')              
                ->save(); 
        }
    }
    
    /*
     * Create/Update magento rule based on synerise data
     */
    protected function _updateRule($rule,$syneriseRule) 
    {
        if(!$syneriseRule instanceof $this->_syneriseRuleClass) {
            throw new Exception('Synerise rule invalid');
        }
        
        if(!isset($this->_allowedValues[$syneriseRule->getDiscount()])) {
            throw new Exception($syneriseRule->getName() . 'invalid rule action: ' . $syneriseRule->getDiscount());
        }
        
        // prepare data
        $name = $syneriseRule->getName();
        $uuid = $syneriseRule->getUuid();
        $action = $this->_allowedValues[$syneriseRule->getDiscount()];        
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
//        switch($syneriseRule->getType()):
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
        
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($syneriseRule->getStart()));
        $fromDate = date('Y-m-d', $dateTimestamp);        
        
        $dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($syneriseRule->getExpiration()));
        $toDate = date('Y-m-d', $dateTimestamp);        

        // update or create
        $rule->setName($name)
            ->setDescription($syneriseRule->getDescription())
            ->setSyneriseUuid($uuid)                
            ->setFromDate($fromDate)
            ->setToDate($toDate)
            ->setUsesPerCoupon($usesPerCoupon)
            ->setSimpleAction($action)                
            ->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
            ->setUseAutoGeneration(1)
            ->setUsesPerCustomer(0)
            ->setDiscountAmount($syneriseRule->getValue())
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

    /*
     * Create/Update magento coupon & bind it with proper rule based on synerise data
     */    
    protected function _updateCoupon() {
        $coupon = $this->getCoupon();

        // new coupon, create
        if(!$coupon || !$coupon->getId()) {
            /** @var $coupon Mage_SalesRule_Model_Coupon */
            $coupon = Mage::getModel('salesrule/coupon');                

            $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
            $now = date('Y-m-d', $currentTimestamp);

            $coupon->setCreatedAt($now);
        }
        
        $rule = $this->getRule();
        
        $expirationDate = $rule->getToDate();
        if ($expirationDate instanceof Zend_Date) {
            $expirationDate = $expirationDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        
        // update or create
        $coupon
            ->setRuleId($rule->getRuleId())
            ->setUsageLimit($rule->getUsesPerCoupon())
            ->setUsagePerCustomer($rule->getUsesPerCustomer())
            ->setExpirationDate($expirationDate)
            ->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
            ->setCode($this->getCouponCode());

        if($coupon->hasDataChanges()) {
            $coupon->save(); 

            return true;
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
        $snrs = $this->getSyneriseCouponInstance();
        $snrs->useActiveCoupon($this->getCouponCode());
    }    
    
    /*
     * Activate cupon by rule Uuid
     */
    public function activateCoupon($couponUuid,$clientUuid)
    {
        /**
         * @var $snrs Synerise\SyneriseCoupon
         */
        $snrs = $this->getSyneriseCouponInstance();
        $snrs->activateCoupon($couponUuid,$clientUuid);
    }    
    

    public function isEnabled()
    {
        return Mage::getStoreConfig('synerise_coupon/general/enable');
    }
    
    /*
     * Check if coupon exists & is bound with synerise rule
     * else check if coupon exsists in synerise
     * 
     * @return boolean
     */
    public function isSyneriseCoupon() 
    {
        $couponCode = $this->getCouponCode();

        if(!$couponCode) {
            throw new Exception('no coupon code set');
        }
        
        /*
         * non-synerise coupon (invalid format)
         */
        if(!$this->validateFormat($couponCode)) {
            return false;
        }
            
        $coupon = $this->getCoupon();

        if($coupon && $coupon->getRuleId()) {
            
            $rule = $this->getRule();

            /*
             * non-synerise rule
             */
            if(!$this->isSyneriseRule($rule)) {
                return false;
            }

        }
   
        // get synerise coupon
        $syneriseCoupon = $this->getSyneriseCoupon();
        
        // coupon not found
        if($syneriseCoupon->getCoupon() == null) {
            return false;
        }
        
        return true;
    }
    
    /*
     * Check if rule is managed by synerise
     */
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
    
    /* 
     * validate code for default format (EAN13)
     */
    public function validateFormat($couponCode) 
    {
        if($this->validateFormatFlag() && !preg_match('/^[\d]{13}$/',$couponCode)) {
            return false;
        }
        return true;
    }
    
    /*
     * is format validation enabled
     */
    public function validateFormatFlag() 
    {
        return Mage::getStoreConfig('synerise_coupon/general/validate_format');
    }
    
    /*
     * log data
     */
    public function log($msg)
    {
        Mage::log($msg, true, 'synerise_coupon.log');                    
    }    
    
    /*
     * is coupon valid for use
     */
    public function canUseSyneriseCoupon()
    {
        // get synerise coupon
        $syneriseCoupon = $this->getSyneriseCoupon();
        return $syneriseCoupon->canUse();
    }

    /*
     * get synerise coupon (active coupon)
     * 
     * @return \Synerise\Response\ActiveCoupon
     */
    public function getSyneriseCoupon()
    {
        if(!$this->getData('synerise_coupon') instanceof $this->_syneriseCouponClass) {
            $syneriseCoupon = $this->getSyneriseCouponInstance()
                    ->getActiveCoupon($this->getCouponCode());
            $this->setSyneriseCoupon($syneriseCoupon);
        }

        return $this->getData('synerise_coupon');
    }
    
    /*
     * get synerise coupon
     * 
     * @return \Synerise\Response\ActiveCoupon
     */
    public function getSyneriseRule()
    {
        $syneriseCoupon = $this->getSyneriseCoupon();
        if($syneriseCoupon instanceof $this->_syneriseCouponClass) {
            return $syneriseCoupon->getCoupon();
        }
        return null;
    }    
    
    /*
     * get synerise coupon
     * 
     * @return \Synerise\Response\Coupon
     */
    public function getSyneriseRules()
    {
        if(!$this->getData('synerise_rules')) {
            $syneriseCouponInstance = $this->getSyneriseCouponInstance();
            $this->setData('synerise_rules',$syneriseCouponInstance->getCoupons());
        }
        return $this->getData('synerise_rules');
    }
  
    /*
     * get magento rule bound with coupon
     * 
     * @return Mage_SalesRule_Model_Coupon
     */    
    public function getCoupon()
    {
        if(!$this->getCouponCode()){
            throw new Exception('no coupon code set');
        }
        
        if(!$this->getData('coupon') instanceof Mage_SalesRule_Model_Coupon || $this->getData('coupon')->getCode() != $this->getCouponCode()) {
            $this->setCoupon(Mage::getModel('salesrule/coupon')->load($this->getCouponCode(), 'code'));
        }
        
        return $this->getData('coupon');
    }
    
    /*
     * load magento rule by coupon's rule id or synerise uuid
     * initiate new if no rule found
     * 
     * @return Mage_SalesRule_Model_Rule
     */
    public function getRule()
    {
        if(!$this->getData('rule') instanceof Mage_SalesRule_Model_Rule) {
            
            // get coupon if exists
            $coupon = $this->getCoupon();
            if($coupon && $coupon->getRuleId()) {
                // load by rule id
                $this->setData('rule', Mage::getModel('salesrule/rule')->load($coupon->getRuleId()));
            } else {
                // load by synerise uuid
                $syneriseRule = $this->getSyneriseRule();
                if($syneriseRule && $syneriseRule->getUuid()) {
                    $this->setData('rule', Mage::getModel('salesrule/rule')->load($syneriseRule->getUuid(),'synerise_uuid'));
                } else {
                    $this->setData('rule', Mage::getModel('salesrule/rule'));
                }
            }
        }
        return $this->getData('rule');    
    }
        
    /*
     * set coupon code & reset related entities
     */
    public function setCouponCode($couponCode)
    {
        if($this->getData('coupon_code') != $couponCode) {
            $this->setData('coupon_code',$couponCode);
            $this->setData('coupon', null);
            $this->setData('rule', null);
        }
    }
    
}
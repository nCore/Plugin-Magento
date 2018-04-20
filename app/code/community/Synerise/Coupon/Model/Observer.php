<?php
class Synerise_Coupon_Model_Observer
{
    
    /*
     * Validate & apply synerise coupon
     * Create/Update rule & bind coupon if coupon_code submited
     */
    public function salesQuoteCollectTotalsBefore($event)
    {

        /*
         * Extension disabled
         */
        if(!Mage::getModel('synerise_coupon/couponManager')->isEnabled()) {
            return $this;
        }

        $quote = $event->getData('quote');
        
        /*
         * No quote, or no coupon code
         */
        if(!$quote || !$quote->getCouponCode()) {
            return $this;
        }
        
        $couponCode = $quote->getCouponCode();

        $couponManager = Mage::getSingleton('synerise_coupon/couponManager');  

        try {
            
            /*
             * Coupon code already processed
             */            
            if($couponManager->getCouponCode() == $couponCode) {
                return $this;
            }
            
            $couponManager->setCouponCode($couponCode);
            
            if(!$couponManager->isSyneriseCoupon()) {
                return $this;
            }
            
            /*
             * Manage coupon & rule
             */
            if($this->_getRequest()->getParam('coupon_code')) {
                $couponManager->updateCouponRuleRelation();
            }
            
            /*
             * Apply discount or remove code
             */
            if(!$couponManager->applyCoupon()) {
                $this->_getSession()->addError(Mage::helper('synerise_coupon')->__('Cannot apply the coupon code.'));
                // redirect back to cart (totals need to be recalculated)
                $this->_goBack();
            }
            
        } catch (Exception $e) {
            $couponManager->log($couponCode.' '.$e->getMessage());
            $rule = $couponManager->getRule();
            if($couponManager->isSyneriseRule($rule)) {
                $couponManager->removeQuoteCouponCode();
            }
        }
        
        return $this;        
    }
    
    /*
     * Use coupon
     */
    public function checkoutSubmitAllAfter($event) 
    {
        if(!Mage::getModel('synerise_coupon/couponManager')->isEnabled()) {
            return $this;
        }
        
        $order = $event->getData('order');
        if(!$order->getId()) {
            throw new Exception('No order');
        }

        $couponCode = $order->getCouponCode();
        
        // no coupon applied, continue        
        if(!$couponCode) {
            return $this;
        }
        
        $couponManager = Mage::getSingleton('synerise_coupon/couponManager');
        
        try {
            
            $couponManager->setCouponCode($couponCode);   

            // non-synerise coupon, continue        
            if(!$couponManager->isSyneriseCoupon() || !$couponManager->canUseSyneriseCoupon()) {
                return $this;
            }

            $couponManager->useCoupon();
            
        } catch (Exception $e) {
            $couponManager->log($order->getIncrementId().' '.$couponCode.' '.$e->getMessage());            
        }
        
        return $this;
    }        
    
    /*
     * Exclude synerise rules from deafult collection
     */
    public function coreCollectionAbstractLoadBefore($obsrerver) 
    {
        if(Mage::app()->getRequest()->getControllerName() == 'promo_quote') {
            $collection = $obsrerver->getData('collection');
            if(is_a($collection, 'Mage_SalesRule_Model_Resource_Rule_Collection')) {
                $collection->addFieldToFilter('synerise_uuid', array('eq' => '' ));
            }
        }
        return $this;
    }

    /*
     * Set Synerise promo_quote_form save action
     */
    public function coreBlockAbstractToHtmlBefore($obsrerver) 
    {
        $block = $obsrerver->getData('block');
        if($block->getId() == 'promo_quote_form') {
            $rule = Mage::registry('current_promo_quote_rule');
            if($rule && $rule->getSyneriseUuid()) {
                $block->setAction(Mage::helper('adminhtml')->getUrl('adminhtml/synerise_promo_quote/save'));                
            }
        }
        return $this;
    }
    
    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     * @throws Mage_Exception
     */
    protected function _goBack()
    {
        $returnUrl = $this->_getRequest()->getParam('return_url');
        if ($returnUrl) {
            $this->_getSession()->getMessages(true);
            $this->_getResponse()->setRedirect($returnUrl);
            Mage::app()->getResponse()->sendResponse();
            exit;
        } else {
            $this->_redirect('checkout/cart');
            Mage::app()->getResponse()->sendResponse();
            exit;
        }
        return $this;
    }

    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
	
    protected function _getResponse()
    {
        return Mage::app()->getResponse();
    }
	
    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    protected function _redirect($path, $arguments = array()) 
    {
        Mage::app()->getResponse()->setRedirect(Mage::getUrl($path,$arguments));
        return $this;        
    }    
}
<?php
class Synerise_Coupon_Model_Observer
{
    
    /*
     * Bind valid coupon code with promo rule
     */
    public function controllerActionPredispatch()
    {

        if(!Mage::getModel('synerise_coupon/coupon')->isEnabled()) {
            return $this;
        }
        
        $couponCode = $this->_getRequest()->getParam('coupon_code');
        if($this->_getRequest()->getActionName() == 'couponPost' && $couponCode) {
            
            $coupon = Mage::getModel('synerise_coupon/coupon');            
            $coupon->setCouponCode($couponCode);
            
            // if coupon defined in synerise, try to bind it with rule
            if($coupon->isSyneriseCoupon()) {
                $coupon->fixCouponRuleRelation(); 
            } // else proceed as magento regular coupon         
        }
        return $this;
    }
    
    /*
     * Set discount amount
     */
    public function salesruleRuleLoadAfter($event) 
    {
        if(!Mage::getModel('synerise_coupon/coupon')->isEnabled()) {
            return $this;
        }

        // skip in admin area
        if (Mage::app()->getStore()->isAdmin()) {
            return $this;
        }

        $couponCode = Mage::getModel('checkout/cart')->getQuote()->getCouponCode();
        // skip if no code applied yet
        if($couponCode == null) {
            return $this;
        }
        
        $rule = $event->getData('rule');
        
        $coupon = Mage::getModel('synerise_coupon/coupon');            
        $coupon->setCouponCode($couponCode);        
        $coupon->setRule($rule);        

        // non-synerise rule, continue
        if(!$coupon->isSyneriseRule($rule)) {
            return $this;
        }

        if($this->_getRequest()->getActionName() != 'couponPost' && $couponCode) {
            // fix rule binding if necessary
            $updated = $coupon->fixCouponRuleRelation();
            if($updated) {
                // redirect back to cart (totals need to be recalculated)
                $this->_goBack();            
            }
        }
        
        if(!$coupon->applyDiscount()) {
            $this->_getSession()->addError(Mage::helper('synerise_coupon')->__('Cannot apply the coupon code.'));
            // redirect back to cart (totals need to be recalculated)
            $this->_goBack();
        }
        
        return $this;
    }
    
    /*
     * Mark as used
     */
    public function checkoutSubmitAllAfter($event) 
    {

        if(!Mage::getModel('synerise_coupon/coupon')->isEnabled()) {
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

        $coupon = Mage::getModel('synerise_coupon/coupon');            
        $coupon->setCouponCode($couponCode);   
        
        // non-synerise coupon, continue        
        if(!$coupon->isSyneriseCoupon()) {
            return $this;
        }
        
        try {
            $coupon->useCoupon();
        } catch (Exception $e) {
            $coupon->log($order->getIncrementId().' '.$couponCode.' '.$e->getMessage());            
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
	
    protected function _getCart() 
    {
         return Mage::getModel('checkout/cart');
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
<?php
class Synerise_Coupon_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action 
{

    public function addAction()
    {
        $coupon = Mage::getModel('synerise_coupon/coupon');  
        $updated = $coupon->importAllCoupons();
        
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('synerise_coupon')->__('Total of %d record(s) were updated', $updated)                
        );
        $this->_redirect('adminhtml/promo_quote/index');
    }
    
}
<?php
require_once 'Mage/Adminhtml/controllers/Promo/QuoteController.php';

class Synerise_Coupon_Synerise_Promo_QuoteController extends Mage_Adminhtml_Promo_QuoteController
{
    public function addAction()
    {
        $coupon = Mage::getModel('synerise_coupon/coupon');  
        $updated = $coupon->importAllCoupons();
        
        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('synerise_coupon')->__('Total of %d record(s) were updated', $updated)                
        );
        $this->_redirect('*/*/index');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('synerise/synerise_coupon/promo_quote')
            ->_addBreadcrumb(Mage::helper('salesrule')->__('Promotions'), Mage::helper('salesrule')->__('Promotions'))
        ;
        return $this;
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/synerise/synerise_coupon');
    }
}
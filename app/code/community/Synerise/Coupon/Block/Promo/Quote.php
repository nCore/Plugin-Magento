<?php
class Synerise_Coupon_Block_Promo_Quote extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'promo_quote';
        $this->_blockGroup = 'synerise_coupon';
        $this->_headerText = Mage::helper('salesrule')->__('Shopping Cart Price Rules');
        $this->_addButtonLabel = Mage::helper('synerise_coupon')->__('Import Synerise Rules');
        
        parent::__construct();    
    }
    
    /*
     * Add button url
     */
    public function getCreateUrl()
    {
        return $this->getUrl('adminhtml/synerise_promo_quote/add');
    }    
}

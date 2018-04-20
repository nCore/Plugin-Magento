<?php
class Synerise_Coupon_Block_Promo_Quote extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'promo_quote';
        $this->_blockGroup = 'synerise_coupon';
        $this->_headerText = 'Synerise: ' . Mage::helper('salesrule')->__('Shopping Cart Price Rules');
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

    public function getGridHtml()
    {
        $html = parent::getGridHtml();

        $infoHtml = '<p>';
        $infoHtml .= $this->__("Manage coupons through Synerise Panel in <a href='https://app.synerise.com/coupons' target='_blank'>campaigns</a> under Coupons section.").'<br/>';
        $infoHtml .= $this->__('Use the <i>%s</i> button to fetch your rules form Synerise system.', $this->_addButtonLabel);
        $infoHtml .= '</p>';

        $info = $this->getLayout()
                ->createBlock('synerise_integration/system_config_form_fieldset')
                ->setHeadMsg($this->_headerText)
                ->setInfoMsg($infoHtml)
                ->toHtml();

        return $info.$html;
    }
}

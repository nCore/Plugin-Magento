<?php
class Synerise_Coupon_Block_System_Config_Form_Status extends Synerise_Integration_Block_System_Config_Form_Fieldset
{
    protected function _construct()
    {
        parent::_construct();
        $this->setHeadMsg($this->__("Coupon"));
        
        $html = '<p>';
        $html .= $this->__("Manage coupons through Synerise Panel in <a href='https://app.synerise.com/coupons' target='_blank'>campaigns</a> under Coupons section.").'<br/>';
        $link = '<i>Synerise > ' . $this->__('Coupon') . ' > <a href="'. $this->getUrl('adminhtml/synerise_promo_quote/index') .'">' . $this->__('Shopping Cart Price Rules').'</a></i>';
        $html .= $this->__('You can find the list off all your coupons under %s.', $link).'<br/>';
        $html .= '</p>';        

        $version = Mage::getConfig()->getModuleConfig("Synerise_Coupon")->version;
        $html .= sprintf('<p><b> %s:</b> %s</p>', $this->__('Module Version'), $version);

        $this->setInfoMsg($html);
    }
}
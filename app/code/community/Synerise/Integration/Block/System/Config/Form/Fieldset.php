<?php
class Synerise_Integration_Block_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected function _construct()
    {
        $logoUrl = Mage::getBaseUrl('media') . 'synerise/logo.jpg';        
        $this->setInfoStyle("background: url('$logoUrl') no-repeat scroll left center; "
            . "border: 1px solid #CCCCCC; "
            . "margin-bottom: 10px; "
            . "padding: 10px 10px 10px 225px; "
            . "min-height: 75px; "
            . "color: #6f8992; ");
        
        $this->setHeadStyle("font-size:1.3em; margin:0.25em 0");        
    }
    
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<div style="'.$this->getInfoStyle().'">'
                . ($this->getHeadMsg() ? '<h2 style="'.$this->getHeadStyle().'">'.$this->getHeadMsg().'</h2>' : '')
                . $this->getInfoMsg()
                . '</div>';        
        
        return $html;
    }
    
    protected function _getHelper()
    {
        return Mage::helper('synerise_integration/api');
    }    
}

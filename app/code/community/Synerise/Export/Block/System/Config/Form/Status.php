<?php
class Synerise_Export_Block_System_Config_Form_Status extends Synerise_Integration_Block_System_Config_Form_Fieldset
{
    protected function _construct()
    {
        parent::_construct();
        $this->setHeadMsg($this->__("Export"));
        
        $html = '<p>';
        $html .= $this->__("Generate XML feeds containg products and categories.");
        $html .= '</p>';        

        $version = Mage::getConfig()->getModuleConfig("Synerise_Export")->version;
        $html .= sprintf('<p><b> %s:</b> %s</p>', $this->__('Module Version'), $version);

        $this->setInfoMsg($html);
    }
}
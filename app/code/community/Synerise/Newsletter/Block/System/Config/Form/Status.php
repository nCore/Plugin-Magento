<?php
class Synerise_Newsletter_Block_System_Config_Form_Status extends Synerise_Integration_Block_System_Config_Form_Fieldset
{
    protected function _construct()
    {
        parent::_construct();
        $this->setHeadMsg($this->__("Newsletter Agreements"));
        
        $html = '<p>'.$this->__('All emails, including confirmation are handled externally via Synerise.').'<br/>';
        $html .= $this->__('Optionally, you can save newsletter agreement info locally.').'<br/>';
        $html .= $this->__('<u>Notice</u>: Newsletter module extends Mage_Newsletter_Model_Subscriber model.').'</p>';        

        $version = Mage::getConfig()->getModuleConfig("Synerise_Newsletter")->version;
        $html .= sprintf('<p><b> %s:</b> %s</p>', $this->__('Module Version'), $version);

        $this->setInfoMsg($html);
    }
}
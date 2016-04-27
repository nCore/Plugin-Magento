<?php
abstract class Synerise_Integration_Block_Opengraph_Abstract extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setOgUrl(trim(strtok($this->helper('core/url')->getCurrentUrl(),'?'),'/'));
        $this->setOgSiteName(Mage::getStoreConfig('general/store_information/name'));
    }
    
    public function getOgDescription()
    {
        return $this->getParentBlock()->getDescription();
    }
    
    public function getOgTitle()
    {
        return $this->getParentBlock()->getTitle();
    }
    
    public function getOgImages()
    {
        return array();
    }    
}

<?php
class Synerise_Export_Block_Admin_Offer_Urls extends Mage_Adminhtml_Block_Widget_Container {
    
    public function __construct() {
        parent::__construct();
        $this->setTemplate('synerise_export/offer/urls.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('grid', $this->getLayout()->createBlock('synerise_export/admin_offer_urls_grid', 'synerise_export_offer_urls_grid'));
        return parent::_prepareLayout();
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }
    
}

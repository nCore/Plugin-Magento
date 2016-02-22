<?php
class Synerise_Export_Adminhtml_OfferController extends Mage_Adminhtml_Controller_Action {
	
    protected function _initAction() {
		return $this;
	}
    
    public function urlsAction() {
        $this->_title($this->__('Catalog'))
            ->_title($this->__('Export Synerise'))
            ->_title($this->__('Feed URLs'));
        $this->loadLayout();
        $this->renderLayout();
    }
    
}
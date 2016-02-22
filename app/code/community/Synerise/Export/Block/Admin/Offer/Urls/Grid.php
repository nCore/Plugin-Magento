<?php
class Synerise_Export_Block_Admin_Offer_Urls_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    
    public function __construct() {
        parent::__construct();
        $this->setId('ceneo_offer_urls_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
    }
    
    protected function getConfig() {
        return Mage::getModel('synerise_export/config');
    }
    
    protected function _prepareCollection(){
        $collection = Mage::getModel('core/store')->getCollection();
        foreach ($collection as &$item) {
            $item->setCeneoUrl($item->getUrl('synerise_export/products/feed', array('hash' => $this->getConfig()->getHash())));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns() {
        $this->addColumn('store_id', array(
            'header' => Mage::helper('synerise_export')->__('Store ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'store_id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('synerise_export')->__('Store Name'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'name',
        ));
        $this->addColumn('ceneo_url', array(
            'header' => Mage::helper('catalog')->__('Feed URL'),
            'align' => 'left',
            'index' => 'ceneo_url',
        ));
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row) {
        return $row->getCeneoUrl();
    }
    
}
<?php

class Synerise_Export_Model_Feed_Category extends Mage_Catalog_Model_Abstract {

    public $rootCategoryId;
    public $defaultSortDir;
    public $catalog = array();
    
    public function __construct() {   
        $this->rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
    }
    
    protected function getConfig() {
        return Mage::getModel('synerise_export/config');
    }
    
    public function getStoreCategories() {
        return $this->getConfig()->getStoreCategories();
    }    
    
    protected function _addCategory($category)
    {
        $this->catalog['categories'][$category->getId()]['category'] = array(
            'id' => $category->getId(),
            'name' => $category->getName(),
            'parent_id' => ($category->getParentId() != $this->rootCategoryId) ? $category->getParentId() : '',
            'order' => $category->getPosition()
        );
    }
    
    protected function _addProduct($product) {
        if(!isset($this->catalog['products'][$product->getId()])) {
            $this->catalog['products'][$product->getId()]['product'] = array(
                'id' => $product->getId()
            ); 
            foreach(array('sku', 'name') as $attribute) {
                $this->catalog['products'][$product->getId()]['product']['attributes'][]['attribute'] = array(
                    'name' => $attribute,
                    $product->getData($attribute)
                );
            }
        }
    }
    
    protected function _addProductCategory($product, $category, $position = 0) {      
        $this->catalog['products'][$product->getId()]['product']['categories'][$category->getId()]['category'] = array(
            'id' => $category->getId(),
            'order' => $position
        );       
    }
    
    public function getDefaultSortDir(){
        if(!$this->defaultSortDir) {
            $dir = Mage::getStoreConfig('synerise_export/attr_other/order_direction');
            if(!in_array($dir, array('asc','desc'))) {
                $dir = 'asc';
            }
            $this->defaultSortDir = $dir;            
        }
        return $this->defaultSortDir;
    }

    public function getCatalogData($storeId) {
        $this->catalog['products']['xmlUrl'] = $this->getConfig()->getOffersUrl($storeId);  
        foreach($this->getStoreCategories() as $category) {
            $this->getCatalogDataByCategory($category);
        }
                
        return $this->catalog;
    }    
    
    public function getCatalogDataByCategory($category) {

        if (!$category->getIsActive()) {
            return false;
        }

        $this->_addCategory($category);        

        $product_collection = $category->getProductCollection();
        $product_collection->addAttributeToSelect('name');
        $product_collection->addAttributeToSelect('type');
        
        
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($product_collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($product_collection);
    
        // default category sort_by [after getLoadedProductCollection]
        $sort = $category->getDefaultSortBy();
        // default product list dir
        $dir = $this->getDefaultSortDir();

        // apply order
        $product_collection->setOrder($sort, $dir);        

        $cur_position = 1;           

        // paging
        $product_collection->setPageSize(100); 
        $pages = $product_collection->getLastPageNumber();
        $currentPage = 1;         

        do {
            $product_collection->setCurPage($currentPage);
            $product_collection->load();
            
            foreach ($product_collection as $product) {
                    $this->_addProduct($product);
                    $this->_addProductCategory($product, $category, $cur_position++);
            }

            $currentPage++;
            //clear collection and free memory
            $product_collection->clear();              

        } while ($currentPage <= $pages);
        
    }

}

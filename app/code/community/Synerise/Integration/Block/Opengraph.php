<?php
class Synerise_Integration_Block_Opengraph extends Mage_Core_Block_Template
{
    public function getCurrentProductCategories() {

        $collection  = new Varien_Data_Collection();        
        $categoryIds = Mage::registry('current_product')->getCategoryIds();
        
        // get all associated category ids
        if(!empty($categoryIds)) {
            $pathIds = array();
            foreach ($categoryIds as $categoryId) {                
                $category = Mage::getModel('catalog/category')->load($categoryId);
                if($category->getIsActive()) {
                    $pathIds[] = $categoryId;
                }
                $pathIds = array_merge($pathIds,$category->getPathIds());                
            }
        }
        
        // get active categories
        if(!empty($pathIds)) {
            $inactive = Mage::getModel('catalog/category')->getCollection();
            $inactive->addAttributeToFilter('is_active', 0);
            $inactiveIds = $inactive->getAllIds();            
            
            $collection = $category->getResourceCollection();
            $collection->addAttributeToSelect('name');
            $collection->addAttributeToFilter('entity_id', array('in' => array_unique($pathIds)));
            
            // skip root categories
            $collection->addAttributeToFilter('entity_id', array('gt' => 2)); 
            $collection->addAttributeToFilter('is_active', 1);
            
            // parent categories active
            foreach($inactiveIds as $inactiveId) {
                $collection->addAttributeToFilter('path', array('nlike' => '%/'.$inactiveId.'/%'));
            }            
            
            // directly associated to product or anchor categories
            $collection->addAttributeToFilter(
                array(
                    array('attribute' => 'entity_id', 'in' => $categoryIds),
                    array('attribute' => 'is_anchor', 'eq' => 1),
                )                    
            );              
            
            $collection->setOrder('level', 'DESC');
        }
        
        return $collection;        
    }
}
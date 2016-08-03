<?php
class Synerise_Export_Model_Adminhtml_System_Config_Source_Attribute 
{
    
    protected function getConfig() 
    {
        return Mage::getModel('synerise_export/config');
    }
    
    public function toOptionArray() 
    {
        $entity_type_id = $this->getEntityTypeId();
        if ($entity_type_id) {
            $attributes_collection = Mage::getModel('catalog/entity_attribute')->getCollection()
                    ->addFieldToFilter('entity_type_id', $entity_type_id);
            $res = array(array('label' => '', 'value' => ''));
            foreach ($attributes_collection as $attribute) {
                $res[$attribute->getAttributeCode()] = array(
                    'label' => $attribute->getAttributeCode(),
                    'value' => $attribute->getAttributeCode()
                );
            }
            ksort($res);
        }
        return $res;
    }
    
    protected function getEntityTypeId() 
    {
        $collection = Mage::getModel('eav/entity_type')->getCollection()
                ->addFieldToFilter('entity_type_code', 'catalog_product');
        $item = $collection->getFirstItem();
        return $item->getId();
    }
    
}
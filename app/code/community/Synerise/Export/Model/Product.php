<?php

class Synerise_Export_Model_Product extends Mage_Catalog_Model_Product {

    protected $_categoryPath;
    
    protected function getConfig() {
        return Mage::getModel('synerise_export/config');
    }

    public function getOffers() {
        $store = $this->getConfig()->getStore();
        $conditions = $this->getConfig()->getCoreAttributesConditions();
        $mappings = $this->getConfig()->getAttributesMappings();
        $additional_attributes = array();
        $_attribute = Mage::getModel('synerise_export/attribute');
        foreach ($mappings as $group) {
            foreach ($group as $mapping) {
                if (!empty($mapping)) {
                    if (!in_array($mapping, $additional_attributes)) {
                        $additional_attributes[$mapping] = $_attribute->getOptionsByCode($mapping);
                    }
                }
            }
        }
        
        $selectAttributes = array_merge(array('sku','weight'),$this->getAdditionalAttributes(true));
        $product_collection = $this->getNonFlatProductCollection();
        $product_collection
                ->addStoreFilter($store->getStoreId())
                ->addAttributeToSelect('price')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('special_from_date')
                ->addAttributeToSelect('special_to_date')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('description')
                ->addAttributeToSelect('short_description')
                ->addAttributeToSelect('tax_class_id')
                ->addAttributeToSelect('visibility')
                ->addAttributeToSelect('status')
                ->addAttributeToFilter('status', array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED))                
                ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        
        
        foreach($selectAttributes as $attr) {
            $product_collection->addAttributeToSelect($attr);
        }        
        
        foreach ($additional_attributes as $code => $options) {
            $product_collection->addAttributeToSelect($code);
        }       
//        $product_collection = $this->addMediaGalleryAttributeToCollection($product_collection);
        $offers = array('other' => array());
        $_stock = Mage::getModel('cataloginventory/stock_item');

        $images_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
        
        // create backend model object to append media gallery images in product collection
        $backendModel = $product_collection->getResource()->getAttribute('media_gallery')->getBackend();        
        
        // paging
        $product_collection->setPageSize(100); 
        $pages = $product_collection->getLastPageNumber();
        $currentPage = 1;         

        do {
            $product_collection->setCurPage($currentPage);
            $product_collection->load();

            foreach ($product_collection as $product) {
                if ($product->isVisibleInSiteVisibility() && $product->isVisibleInCatalog()) {

                    // load images
                    $backendModel->afterLoad($product);                  
                    
                    $core_attrs = array();
                    $_stock = $_stock->loadByProduct($product);
                    if ($_stock->getManageStock()) {
                        $core_attrs['stock'] = (int) $_stock->getQty();
                    }
                    foreach ($conditions as $attr => $data) {
                        if (array_key_exists('code', $data)) {
                            if (!empty($data['code']) && $product->getData($data['code']) !== null) {
                                $options = $additional_attributes[$data['code']];
                                if (empty($options)) {
                                    $core_attrs[$attr] = (int) ($product->getData($data['code']) == $data['value']);
                                } else {
                                    $key = $product->getData($data['code']);
                                    if ($key) {
                                        $option = array_key_exists($key, $options) ? $options[$key] : null;
                                        $core_attrs[$attr] = $option ? (int) ($option == $data['value']) : 0;
                                    }
                                }
                            }
                        } else if (array_key_exists('values', $data)) {
                            if (is_array($data['values'])) {
                                foreach ($data['values'] as $value => $value_data) {
                                    if (!empty($value_data['code'])) {
                                        $options = $additional_attributes[$value_data['code']];
                                        if (empty($options)) {
                                            if ($product->getData($value_data['code']) == $value_data['value']) {
                                                $core_attrs[$attr] = $value;
                                                break;
                                            }
                                        } else {
                                            if ($product->getData($value_data['code'])) {
                                                $option = $options[$product->getData($value_data['code'])];
                                                if ($option == $value_data['value']) {
                                                    $core_attrs[$attr] = $value;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if (!isset($core_attrs[$attr]) && isset($data['default'])) {
                                $core_attrs[$attr] = $data['default'];
                            }
                        }
                    }      
                    $group = 'other';
                    $group_attrs = array();
                    foreach ($mappings[$group] as $attr => $mapping) {
                        if (!empty($mapping)) {
                            $value = $product->getData($mapping);
                            if (!empty($value)) {
                                $options = $additional_attributes[$mapping];
                                if (!empty($options)) {
                                    $group_attrs[$attr] = $options[$value];
                                } else {
                                    $group_attrs[$attr] = $value;
                                }
                            }
                        }
                    }

                    // get category
                    $categories = $product->getCategoryIds();
                    $cat = array();
                    if($categories && count($categories)) {
                        foreach($categories as $category) {
                            $_cat = $this->getMageCategoryPath($category);
                            if(count($_cat) > count($cat)) {
                                $cat = $_cat;
                            }
                        }
                    }
                
                    // get configurable attributes
                    if($product->getTypeId() == 'configurable') {
                        $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                        foreach ($productAttributeOptions as $productAttribute) {
                            $values = array();
                            foreach ($productAttribute['values'] as $attribute) {
                                $values[] = $attribute['store_label'];
                            }
                            $group_attrs[$productAttribute['label']] = implode(';',$values);
                        }
                    }

                    // get simple attributes
                foreach($this->getAdditionalAttributes() as $attribute) {
                        if(!isset($group_attrs[$attribute->getStoreLabel()]) && !in_array($attribute->getAttributeCode(),array('sku','price')) ) {
                        $group_attrs[$attribute->getStoreLabel()] = $product->getAttributeText($attribute->getAttributeCode());
                        }
                    }
                
                    $imgs = array();
                    $media_gallery = $product->getMediaGallery();
                    $images = (isset($media_gallery['images'])) ? $media_gallery['images'] : array();
                    $i = 0;
                    foreach ($images as $image) {
                        $imgs[] = $images_url . $image['file'];
                        if ($i == 1) {
                            break;
                        }
                        $i++;
                    }
                
                    $price = $this->getFinalPriceIncludingTax($product);
                    $offers[$group][] = array(
                        'id' => $product->getSku(),
                        'url' => $product->getProductUrl(),
                        'price' => $price,
                        'name' => $product->getName(),
                        'desc' => $product->getDescription() ? $product->getDescription() : $product->getShortDescription(),
                        'weight' => $product->getWeight(),
                        'imgs' => $imgs,
                        'cat' => $cat,
                        'group_attrs' => $group_attrs,
                        'core_attrs' => $core_attrs
                    );
                }
            }
            
            $currentPage++;
            //clear collection and free memory
            $product_collection->clear();              
            
        } while ($currentPage <= $pages);
        return $offers;
    }

    public function addMediaGalleryAttributeToCollection($_productCollection) {
        $all_ids = $_productCollection->getAllIds();
        if (!empty($all_ids)) {
            $_mediaGalleryAttributeId = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'media_gallery')->getAttributeId();
            $_read = Mage::getSingleton('core/resource')->getConnection('catalog_read');

            $_mediaGalleryData = $_read->fetchAll('
				SELECT
					main.entity_id, `main`.`value_id`, `main`.`value` AS `file`,
					`value`.`label`, `value`.`position`, `value`.`disabled`, `default_value`.`label` AS `label_default`,
					`default_value`.`position` AS `position_default`,
					`default_value`.`disabled` AS `disabled_default`
				FROM `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery') . '` AS `main`
					LEFT JOIN `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value') . '` AS `value`
						ON main.value_id=value.value_id AND value.store_id=' . Mage::app()->getStore()->getId() . '
					LEFT JOIN `' . Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_media_gallery_value') . '` AS `default_value`
						ON main.value_id=default_value.value_id AND default_value.store_id=0
				WHERE (
					main.attribute_id = ' . $_read->quote($_mediaGalleryAttributeId) . ') 
					AND (main.entity_id IN (' . $_read->quote($all_ids) . '))
				ORDER BY IF(value.position IS NULL, default_value.position, value.position) ASC    
			');


            $_mediaGalleryByProductId = array();
            foreach ($_mediaGalleryData as $_galleryImage) {
                $k = $_galleryImage['entity_id'];
                unset($_galleryImage['entity_id']);
                if (!isset($_mediaGalleryByProductId[$k])) {
                    $_mediaGalleryByProductId[$k] = array();
                }
                $_mediaGalleryByProductId[$k][] = $_galleryImage;
            }
            unset($_mediaGalleryData);
            foreach ($_productCollection as &$_product) {
                $_productId = $_product->getData('entity_id');
                if (isset($_mediaGalleryByProductId[$_productId])) {
                    $_product->setData('media_gallery', array('images' => $_mediaGalleryByProductId[$_productId]));
                }
            }
            unset($_mediaGalleryByProductId);
        }

        return $_productCollection;
    }

    public function getIdsByCategoryIds($category_ids = array()) {
        $ids = array();
        $_category = Mage::getModel('catalog/category');
        foreach ($category_ids as $category_id) {
            $_category->load($category_id);
            if ($_category->getId()) {
                foreach ($_category->getProductCollection() as $product) {
                    $id = $product->getId();
                    if (!isset($ids[$id])) {
                        $ids[$id] = $id;
                    }
                }
            }
        }
        return $ids;
    }

    public function updateCeneoCategory($product_ids = array(), $ceneo_category_id) {
        $error = false;
        try {
            foreach ($product_ids as $id) {
                $this->unsetData()->load($id);
                if ($this->getId() && $this->getCeneoCategoryId() != $ceneo_category_id) {
                    $this->setCeneoCategoryId($ceneo_category_id);
                    $this->getResource()->saveAttribute($this, 'ceneo_category_id');
                }
            }
        } catch (Exception $e) {
            $error = true;
            Mage::log($e->getMessage(), null, 'synerise_export.log');
        }
        return !$error;
    }

    public function getFinalPriceIncludingTax($product) {
        return Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), 2);
    }    
    
    public function getNonFlatProductCollection() {
        $flatHelper = Mage::helper('catalog/product_flat');
        if ($flatHelper->isEnabled()) {        
            $process = $flatHelper->getProcess();
            $status = $process->getStatus();
            $process->setStatus(Mage_Index_Model_Process::STATUS_RUNNING);        
            $collection = $this->getCollection();
            $process->setStatus($status);        
        } else {
            $collection = $this->getCollection();
        }
        return $collection;
    }
    
    public function getMageCategoryPath($categoryId, $includeRoot = false) {
        if(isset($this->_categoryPath[$categoryId])) {
            $results = $this->_categoryPath[$categoryId];
        } else {
            $results = array();        
            if($categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $collection = $category->getResourceCollection();
                $pathIds = $category->getPathIds();
                $collection->addAttributeToSelect('name');
                $collection->addAttributeToFilter('entity_id', array('in' => $pathIds));

                foreach ($collection as $cat) {
                    $results[] = $cat->getName();
                }
                
                $this->_categoryPath[$categoryId] = $results;
            }
        }
        
        if(!$includeRoot && count($results) > 1) {
            unset($results[0]);
            unset($results[1]);
        }         

        return $results;        
    }

    public function getAdditionalAttributes($returnCodes = false) {

        /* @var $attributes Mage_Catalog_Model_Resource_Eav_Resource_Product_Attribute_Collection */
        $attributes = $this->getData('attributes');
        if (is_null($attributes)) {
            $product = Mage::getModel('catalog/product');
            $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
                ->addStoreLabel(Mage::app()->getStore()->getId())
                ->addFieldToFilter(
                    array('is_visible_on_front', 'is_filterable'),
                    array(
                        array('eq' => true), 
                        array('eq' => true)
                    )
                )                    
                ->load();
            foreach ($attributes as $attribute) {
                $attribute->setEntity($product->getResource());
            }
            $this->setData('attributes', $attributes);
        }        
        
        $results = array();           
        if($returnCodes) {
            foreach ($attributes as $attribute) {
                $results[] = $attribute->getAttributeCode();
            }     
            return $results;            
        }
        return $attributes;
    }

}

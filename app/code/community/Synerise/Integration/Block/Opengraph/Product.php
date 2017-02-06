<?php
class Synerise_Integration_Block_Opengraph_Product extends Synerise_Integration_Block_Opengraph_Abstract
{
    protected function _construct()
    {
        parent::_construct();

        $this->setOgType('product');

        $product = Mage::registry('current_product');

        if($product) {
            $this->setProduct($product);
            $this->setOgTitle($product->getName());
            $this->setOgUrl($product->getProductUrl());
            if($product->getShortDescription()) {
                $this->setOgDescription(strip_tags($product->getShortDescription()));
            } elseif($product->getDescription()) {
                $this->setOgDescription(strip_tags($product->getDescription()));
            }
            $this->setProductPrice(Mage::getModel('directory/currency')->formatTxt($product->getFinalPrice(), array('display' => Zend_Currency::NO_SYMBOL)));
            $this->setProductOriginalPrice(Mage::getModel('directory/currency')->formatTxt($product->getPrice(), array('display' => Zend_Currency::NO_SYMBOL)));
            $this->setProductCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());
            $this->setProductRetailerPartNo($product->getSku());
        }
    }

    public function getOgImages()
    {
        $images = new Varien_Data_Collection();
        $product = $this->getProduct();

        if($product) {
            $imageType = Mage::getStoreConfig('synerise_integration/opengraph/image');

            if(in_array($imageType, array('image', 'small_image', 'thumbnail'))) {
                $images = $this->_addImageCollectionItem($product->getData($imageType));
            } else {
                $images = $product->getMediaGalleryImages();
            }
        }
        return $images ? $images : array();
    }

    public function getProductCategories()
    {

        $collection  = new Varien_Data_Collection();

        $product = Mage::registry('current_product');
        if($product) {
            $categoryIds = $product->getCategoryIds();

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
        }
        return $collection;
    }

    protected function _addImageCollectionItem($file, $collection = null)
    {
        if(!$collection) {
            $collection  = new Varien_Data_Collection();
        }

        if($file) {
            $image = array(
                'file'  => $file,
                'url'   => Mage::getModel('catalog/product_media_config')->getMediaUrl($file),
                'path'  => Mage::getModel('catalog/product_media_config')->getMediaPath($file)
            );
            if($image['url']) {
                $collection->addItem(new Varien_Object($image));
            }
        }

        return $collection;
    }
}
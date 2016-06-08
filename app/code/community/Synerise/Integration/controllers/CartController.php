<?php
class Synerise_Integration_CartController extends Mage_Core_Controller_Front_Action {
    
    public function addAction() 
    {
        $skuArray = Mage::app()->getRequest()->getParam('sku');
        $hash = Mage::app()->getRequest()->getParam('hash');
        if(!$skuArray || !$hash) {
            $this->_redirect('checkout/cart');
            $this->_getSession()->addNotice($this->__('Missing cart arguments.'));
            return;
        }

        $apiKey = $this->_getApiKey();
        if(!$apiKey) {
            $this->_redirect('checkout/cart');
            $this->_getSession()->addNotice($this->__('Missing Api Key.'));
            return;
        }
        
        if($hash != sha1(json_encode($skuArray).'-BASKET-'.$apiKey)) {
            $this->_redirect('checkout/cart');
            $this->_getSession()->addNotice($this->__('Invalid form key.'));
            return;
        }
        
        $cart = $this->_getCart();     
        
        $currentItems = array();
        foreach($cart->getQuote()->getAllItems() as $item) {
            $currentItems[] = $item->getSku();
        }
        $skuArray = array_diff($skuArray, $currentItems);   
        
        if(!empty($skuArray)) {
            $this->_getSession()->addNotice($this->__('All Items are already in cart.'));
            $this->_redirect('checkout/cart');                
            return;
        }        
        
        $_productCollection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku', array('in' => $skuArray));            

        if(!$_productCollection->getSize()) {
            $this->_getSession()->addNotice($this->__('Cannot add the item to shopping cart.'));
            $this->_redirect('checkout/cart');                
            return;
        }

        $productsAdded = array();
        
        foreach($_productCollection as $product) {

            try {
            
                if($product->getTypeId() == 'simple') {
                    if($product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                        // configurable option
                        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                            ->getParentIdsByChild($product->getId());               

                        if(!isset($parentIds[0])) {
                            continue;
                        }

                        $child = $product;
                        $product = Mage::getModel('catalog/product')->load($parentIds[0]);

                        $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
                        $options = array();

                        foreach ($productAttributeOptions as $productAttribute) {
                            $allValues = array_column($productAttribute['values'], 'value_index');
                            $currentProductValue = $child->getData($productAttribute['attribute_code']);
                            if (in_array($currentProductValue, $allValues)) {
                                $options[$productAttribute['attribute_id']] = $currentProductValue;
                            }
                        }

                        $params = array(
                            'product' => $product->getId(),
                            'qty' => 1,
                            'super_attribute' => $options                        
                        );

                    } else {
                        $params = array('qty' => 1);
                    }
                    
                    $request = new Varien_Object();
                    $request->setData($params);                    
                    
                    $cart->addProduct($product->getId(), $params);

                    $this->_getSession()->setCartWasUpdated(true);

                    Mage::dispatchEvent('checkout_cart_add_product_complete',
                        array('product' => $product, 'request' => $request, 'response' => null)
                    );
                
                    $productsAdded[] = $product->getName();
                }
            } catch (Mage_Core_Exception $e) {
                if ($this->_getSession()->getUseNotice(true)) {
                    $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message) {
                        $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                    }
                }
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                Mage::logException($e);
            }    
        }
        $cart->save();
        
        if (!$cart->getQuote()->getHasError()) {
            $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml(implode(', ',$productsAdded)));
            $this->_getSession()->addSuccess($message);
        }
        $this->_redirect('checkout/cart');        
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
    
    protected function _getApiKey() {
        return Mage::getStoreConfig('synerise_integration/api/key');
    }    
    
}
<?php
class Synerise_Integration_Helper_Tracker extends Mage_Core_Helper_Abstract
{
    public $defaults = array();
    public $rootCategoryId;

    public function __construct()
    {
        Synerise_Integration_Helper_Autoloader::createAndRegister();

        $this->defaults = array(
            'apiKey' => Mage::getStoreConfig('synerise_integration/api/key'),
            'verify' => (bool) Mage::getStoreConfig('synerise_integration/api/verify_ssl'),
            'debug' => (bool) Mage::getStoreConfig('synerise_integration/dev/debug')
        );

        $this->rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
    }
    
    public function getInstance($options = array())
    {
        $options = array_merge($this->defaults, $options);
        $logger = !empty($options['debug']) ? Mage::getModel('synerise_integration/Logger') : null;

        $class = 'Synerise\SyneriseTracker';
        return $class::getInstance($options, $logger);
    }
    
    /**
     *
     * Convert produt to array
     * @param $product Mage_Catalog_Model_Product
     * @return array
     */
    public function convertProductToDataSend(Mage_Catalog_Model_Product $product)
    {
        $quote = $quote = Mage::getSingleton('checkout/cart')->getQuote();
        $result = array();

        $result['$quoteUUID'] = $quote->getId();
        $result['$sku'] = $product->getSku();
        $result['$categories'] = $this->getActiveCategoryIds($product);
        $result['$title'] = $product->getName();
        $result['$regularPrice'] = $product->getPrice();
        $result['$discountPrice'] = $product->getSpecialPrice();
        $result['$currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();

        $product = Mage::getModel('catalog/product')->load($product->getId());
        $attributes = $product->getAttributes();
//
//
//        $keySend = [
//            'size' => 'size',
//            'type' => 'type',
//            'color' => 'color',
//            'colour' => 'colour',
//            'colGroup' => 'colGroup',
//            'skin' => 'skin',
//            'material' => 'material',
//            'col_group01' => 'colGroup01',
//            'colGroup02' => 'colGroup02',
//            'kind' => 'kind',
//            'heelHeightCm' => 'heelHeightCm',
//            'heelHeight' => 'heelHeight',
//            'season' => 'season',
//            'subcollection' => 'subCollection',
//            'unit' => 'unit',
//            'section' => 'section',
//            'size' => 'size',
//            'term' => 'term'
//        ];


        $productattrAttr = Mage::getStoreConfig('synerise_integration/productattr/attr');
        $keySend = array();

        if ($productattrAttr) {
            $productattrAttr = unserialize($productattrAttr);
            if (is_array($productattrAttr)) {
                foreach ($productattrAttr as $key => $productattrAttrRow) {
                    if ($productattrAttrRow['send'] == 1) {
                        $keySend[$key] = $productattrAttrRow['map'];
                    }
                }
            }
        }


        foreach($attributes as $attribute) {
            if(isset($keySend[$attribute->getAttributeCode()])) {
                $attr = $this->filterAttr($attribute->getFrontend()->getValue($product));
                if($attr) {
                    $result[$keySend[$attribute->getAttributeCode()]] = $attribute->getFrontend()->getValue($product);
                }
            }
        }
        $productMediaConfig = Mage::getModel('catalog/product_media_config');
        $result['image'] = (string)$productMediaConfig->getMediaUrl($product->getSmallImage());
        $result['url'] = $product->getProductUrl();

        return $result;
    }


    public function convertOrderToDataSend(Mage_Sales_Model_Order $order)
    {
        $result = array();

        $result['$offline'] = 0;
        $result['$totalAmount'] = $order->getGrandTotal();
        $result['$revenue'] = $order->getGrandTotal();
        $result['$discountAmount'] = $order->getDiscountAmount();
        $result['$deliveryType'] = $order->getShippingDescription();
        $result['$paymentType'] = array(
            $order->getPayment()->getMethod() => $order->getGrandTotal()
        );

        $result['$orderId'] = $order->getIncrementId(); // to samo co w koszyku

        $result['$productsQuantity'] = (int)$order->getTotalQtyOrdered(); //count($items);
        $result['$transactionDate'] = $order->getCreatedAt();
        $result['$taxAmount'] = $order->getTaxAmount();
        $result['$currency'] = $order->getStoreCurrencyCode();
        $result['$storeName'] = $order->getStoreName();
        $result['$discountCode'] = $order->getCouponCode();
        $result['$locationIdent'] = "261";
        $result['$location'] = 'e-sklep';
        $result['$city'] = 'e-sklep';
        $result['$region'] = 'INTERNET';
        $result['quoteUUID'] = $order->getQuoteId(); // to samo co w koszyku


        return $result;

    }


    public function convertCustomerToDataSend(Mage_Customer_Model_Customer $customer)
    {

        $result = array(
            '$email' => $customer->getEmail(),
            '$firstname' => $customer->getFirstname(),
            '$lastname' => $customer->getLastname(),
            '$createdInLanguage' => $customer->getCreatedIn(),
            '$entityId' => $customer->getId()
        );

        return $result;
    }

    public function convertCustomerByOrderToDataSend(Mage_Sales_Model_Order $order)
    {
        $shippingAddress = $order->getShippingAddress();
        if(empty($shippingAddress)) {
            $shippingAddress = $order->getBillingAddress();
        }

        return  array(
            '$email' => $order->getCustomerEmail(),
            '$firstname' => $order->getCustomerFirstname(),
            '$lastname' => $order->getCustomerLastname(),
            '$storeId' => $order->getStoreId(),
            '$company' => $shippingAddress->getCompany(),
            '$adress' => $shippingAddress->getStreet1(),
            '$city' => $shippingAddress->getCity(),
            '$region' => $shippingAddress->getRegion(),
            '$zipCode' => $shippingAddress->getPostcode(),
            '$phone' => $shippingAddress->getTelephone(),
            '$createdInLanguage' => '',
            '$entityId' => $order->getCustomerId()
        );
    }


    private function filterAttr($attr)
    {
        return !empty($attr)?$attr:false;
    }

    public function getActiveCategoryIds(Mage_Catalog_Model_Product $product)
    {
        $categories = $product->getCategoryIds();
        $activeCategoryIds = array();
        if($categories && count($categories)) {
            foreach($categories as $categoryId) {
                $activeCategoryIds = array_unique(array_merge($this->getCategoryParentIds($categoryId), $activeCategoryIds));
            }
        }

        return array_values($activeCategoryIds);
    }

    public function getCategoryParentIds($categoryId) {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $path = array();
        if(!$category || !$category->getId()) {
            return $path;
        }
        do {
            if ($category->getIsActive()) {
                $path[] = $category->getId();
            }
            $category = Mage::getModel('catalog/category')->load($category->getParentId());
        } while($category && $category->getId() && $category->getId() != $this->rootCategoryId);

        return array_values($path);
    }
}
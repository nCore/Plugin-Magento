<?php
class Synerise_Export_ProductsController extends Mage_Core_Controller_Front_Action {
	
    protected function getConfig() {
        return Mage::getModel('synerise_export/config');
    }
    
    public function feedAction() {
        $hash = $this->getRequest()->getParam('hash');
        if ($hash == $this->getConfig()->getHash()) {
            ini_set('max_execution_time', 0);
            require_once(Mage::getBaseDir('lib').'/Ceneopl/simple_xml_extended.php');
            $offers = Mage::getModel('synerise_export/product')->getOffers();
            $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><offers xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1" />');
            foreach ($offers as $group_name => $products) {
                $group = $xml->addChild('group');
                $group->addAttribute('name', $group_name);
                foreach ($products as $product) {
                    $o = $group->addChild('o');
                    $o->addAttribute('id', $product['id']);
                    $o->addAttribute('url', $product['url']);
                    $o->addAttribute('price', $product['price']);
                    if (!empty($product['weight'])) {
                        $o->addAttribute('weight', $product['weight']);
                    }
                    foreach ($product['core_attrs'] as $attr => $value) {
                        $o->addAttribute($attr, $value);
                    }
                    $o->addChild('cat')
                        ->addCData(implode('/', $product['cat']));
                    $o->addChild('name')
                        ->addCData($product['name']);
                    $o->addChild('desc')
                        ->addCData($product['desc']);
                    if (!empty($product['imgs'])) {
                        $imgs = $o->addChild('imgs');
                        $imgs->addChild('main')
                            ->addAttribute('url', $product['imgs'][0]);
                        if (isset($product['imgs'][1])) {
                            $imgs->addChild('i')
                                ->addAttribute('url', $product['imgs'][1]);
                        }
                    }
                    $attrs = $o->addChild('attrs');
                    foreach ($product['group_attrs'] as $attr => $value) {
                        $a = $attrs->addChild('a');
                        $a->addAttribute('name', $attr);
                        $a->addCData($value);
                    }
                }
            }
            $this->getResponse()
                ->setHeader('Content-Type', 'text/xml')
                ->setBody($xml->asXML())
                ->sendResponse();
            exit;
        } else {
            $this->_redirect('/');
        }
    }
    
}
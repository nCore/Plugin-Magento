<?php
class Synerise_Export_Model_Feed extends Mage_Core_Model_Abstract 
{
    
    protected function _construct()
    {
        $this->setVersion('2');
        ini_set('max_execution_time', 0);
        require_once(Mage::getBaseDir('lib').'/Synerise/simple_xml_extended.php');        
    }
    
    protected function getConfig() 
    {
        return Mage::getModel('synerise_export/config');
    }
    
    public function generateFeeds($storeId)
    {
            $result1 = $this->generateCatalogFeed($storeId);
            $result2 = $this->generateOffersFeed($storeId);
            return array_merge($result1,$result2);
    }    
    
    public function generateCatalogFeed($storeId)
    {
            // catalog xml
            $catalog = Mage::getSingleton('synerise_export/category')->getCatalogData($storeId);
            $catalogXml = new SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><catalog xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="'.$this->getVersion().'" />');
            $this->getArray2Xml($catalog, $catalogXml);  
            
            $filename = $this->getConfig()->getCatalogFileName($storeId, true);
            
            $result = array();
            if (file_put_contents($filename, $catalogXml->asXML())) {
                $result['catalogUrl'] = $this->getConfig()->getCatalogUrl($storeId);
            }
            
            return $result;           
    }
    
    public function generateOffersFeed($storeId) 
    {
            // offers xml
            $offers = Mage::getSingleton('synerise_export/product')->getOffers($storeId);
            $xml = new SimpleXMLExtended('<?xml version="1.0" encoding="utf-8"?><offers xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1" />');
            $count = 0;
            foreach ($offers as $group_name => $products) {
                $count += count($products);
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
                        if($value) {
                            $a = $attrs->addChild('a');
                            $a->addAttribute('name', $attr);
                            $a->addCData($value);
                        }
                    }
                }
            }
            
            $filename = $this->getConfig()->getOffersFileName($storeId, true);
            
            if (file_put_contents($filename, $xml->asXML())) {   }    
            
            $result = array();      
            $result['offersUrl'] = $this->getConfig()->getOffersUrl($storeId);
            $result['date'] = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
            $result['qty'] = $count;
            
            return $result;     
    }
    
    protected function getArray2Xml($array, &$xml_user_info) 
    {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)) {
                    $subnode = $xml_user_info->addChild($key);                                            
                    $this->getArray2Xml($value, $subnode);
                } else {
                    $this->getArray2Xml($value, $xml_user_info);
                }
            } else {  
                if(!is_numeric($key)) {
                    $xml_user_info->addAttribute($key,$value);
                } else {
                    $xml_user_info->addCData($value);
                }
            }
        }
    }          
    
}
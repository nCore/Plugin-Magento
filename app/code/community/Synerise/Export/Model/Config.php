<?php
class Synerise_Export_Model_Config extends Mage_Core_Model_Abstract {
    
    public static $groups = array(
        'core' => array('avail', 'set', 'basket'),
        'other' => array('Producent', 'Kod_producenta', 'EAN')
    );
    
    const AVAIL_DEFAULT_VALUE = 99;

    public function _construct() {
        $this->_init('synerise_export/config');
    }
    
    public function getGroups() {
        return self::$groups;
    }
    
    public function getCoreAttributes() {
        return self::$groups['core'];
    }
    
    public function getCoreAttributesConditions() {
        $attributes = $this->getCoreAttributes();
        $res = array();
        foreach ($attributes as $attr) {
            $res[$attr] = $this->getAttributeConditions($attr, 'core');
        }
        return $res;
    }
    
    public function getAttributeConditions($attr, $group) {
        if ($group == 'core' && $attr == 'avail') {
            $values = array(1, 3, 7, 14);
            $res = array(
                'values' => array(),
                'default' => self::AVAIL_DEFAULT_VALUE
            );
            foreach ($values as $value) {
                $res['values'][$value] = array(
                    'code' => Mage::getStoreConfig('synerise_export/attr_core/avail_'.$value.'_name'),
                    'value' => Mage::getStoreConfig('synerise_export/attr_core/avail_'.$value.'_value')
                );
            }
            return $res;
        } else {
            return array(
                'code' => Mage::getStoreConfig('synerise_export/attr_'.$group.'/'.$attr.'_name'),
                'value' => Mage::getStoreConfig('synerise_export/attr_'.$group.'/'.$attr.'_value')
            );
        }
    }
    
    public function getAttributesMappings() {
        $groups = $this->getGroups();
        $res = array();
        foreach ($groups as $group => $attributes) {
            $res[$group] = array();
            foreach ($attributes as $attr) {
                if ($group != 'core') {
                    $res[$group][$attr] = Mage::getStoreConfig('synerise_export/attr_'.$group.'/'.$attr);
                } else {
                    if ($attr != 'avail') {
                        $res[$group][$attr] = Mage::getStoreConfig('synerise_export/attr_'.$group.'/'.$attr.'_name');
                    } else {
                        $indexes = array(1, 3, 7, 14);
                        foreach ($indexes as $i) {
                            $res[$group][$attr.'_'.$i] = Mage::getStoreConfig('synerise_export/attr_'.$group.'/'.$attr.'_'.$i.'_name');
                        }
                    }
                }
            }
        }
        return $res;
    }
    
    public function getPriceIncludesTax() {
        return Mage::getStoreConfig('tax/calculation/price_includes_tax');
    }
    
    public function getStore() {
        return Mage::app()->getStore();
    }
    
    public function saveHash() {
        $hash = md5(microtime());
        Mage::getModel('core/config')->saveConfig('synerise_export/config/hash', $hash, 'default', 0);
    }
    
    public function getHash() {
        return Mage::getStoreConfig('synerise_export/config/hash');
    }
    
}
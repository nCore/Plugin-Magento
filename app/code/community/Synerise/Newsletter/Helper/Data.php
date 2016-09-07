<?php
class Synerise_Newsletter_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return Mage::getStoreConfig('synerise_newsletter/settings/enable');
    }
}
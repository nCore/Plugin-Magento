<?php
class Synerise_Coupon_Block_Promo_Quote_Grid extends Mage_Adminhtml_Block_Promo_Quote_Grid
{

    /**
     * Retrieve row click URL
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    
    /**
     * filter synerise rules
     *
     * @return Mage_Adminhtml_Block_Promo_Quote_Grid
     */
    protected function _prepareCollection()
    {
        // prevents collection from being loaded
        $this->_isExport = true;
        
        parent::_prepareCollection();
        
        $this->getCollection()
                ->addFieldToFilter('synerise_uuid', array('neq' => '' ))
                ->load();
        
        return $this;
    }
}

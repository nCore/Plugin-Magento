<?php
/**
 * Shopping cart rule edit form block
 */

class Synerise_Coupon_Block_Promo_Quote_Edit extends Mage_Adminhtml_Block_Promo_Quote_Edit
{

    /**
     * Initialize form
     * Add standard buttons
     * Add "Save and Continue" button
     */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'promo_quote';
        $this->_blockGroup = 'synerise_coupon';
        
        parent::__construct();

        $this->_addButton('save_and_continue_edit', array(
            'class'   => 'save',
            'label'   => Mage::helper('salesrule')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
        ), 10);
    }

}

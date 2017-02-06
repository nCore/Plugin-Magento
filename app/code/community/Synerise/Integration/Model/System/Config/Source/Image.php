<?php
class Synerise_Integration_Model_System_Config_Source_Image
{
    
    public function toOptionArray()
    {
        return array(
            array('value' => 'gallery',     'label'=> Mage::helper('synerise_integration')->__('Gallery')),
            array('value' => 'image',       'label'=> Mage::helper('synerise_integration')->__('Image')),
            array('value' => 'small_image', 'label'=>Mage::helper('synerise_integration')->__('Small Image')),
            array('value' => 'thumbnail',   'label'=>Mage::helper('synerise_integration')->__('Thumbnail'))
        );
    }

}
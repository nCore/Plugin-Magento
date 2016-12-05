<?php

class Synerise_Integration_Block_Customattributes extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * Returns html part of the setting
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);

        $productattrAttr = Mage::getStoreConfig('synerise_integration/productattr/attr');
        $mapValue = array();

        if ($productattrAttr) {
            $productattrAttr = unserialize($productattrAttr);
            if (is_array($productattrAttr)) {
                foreach ($productattrAttr as $key => $productattrAttrRow) {
                    if ($productattrAttrRow['send'] == 1) {
                        $mapValue[$key] = $productattrAttrRow['map'];
                    }
                }
            }
        }

        $name = $element->getName();
        $html = '<table>';

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->getItems();

        foreach ($attributes as $attribute) {
            $html .= '<tr>';
            $checked = isset($mapValue[$attribute->getAttributecode()]) ? "checked" : "";
            $valueMap = isset($mapValue[$attribute->getAttributecode()]) ? "value='" . $mapValue[$attribute->getAttributecode()] . "'" : "disabled=\"disabled\"";

            $html .= '' .
                '<td><span>' . $attribute->getAttributecode() . '</span></td>' .
                '<td><input ' . $checked . '  id="' . $attribute->getAttributecode() . '" onclick=\'handleClick(this);\' class="product_attributes" type="checkbox" name="' . $name . '[' . $attribute->getAttributecode() . '][send]' . '" value="1"></td>' .
                '<td><input id="' . $attribute->getAttributecode() . '_text" type="text" name="' . $name . '[' . $attribute->getAttributecode() . '][map]" ' . $valueMap . '>' .
                '</td>';

            $html .= '</tr>';
        }


        $html .= '</table>';

        $html .= "<script>
        function handleClick(cb)
        {
            if(cb.checked ) {
                document.getElementById( cb.getAttribute('id')+'_text').disabled = false;
                document.getElementById( cb.getAttribute('id')+'_text').value = cb.getAttribute('id');
            } else {
                document.getElementById( cb.getAttribute('id')+'_text').disabled = true;
                document.getElementById( cb.getAttribute('id')+'_text').value = '';
            }
        }
        </script>";

        return $html;
    }


}
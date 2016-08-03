<?php
class Synerise_Export_Block_Adminhtml_System_Config_Form_Field_Feeds
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface 
{

    protected function getConfig() {
        return Mage::getModel('synerise_export/config');
    }
    
    public function render(Varien_Data_Form_Element_Abstract $element) 
    {   
        $store_ids = $this->getConfig()->getEnabledStoreIds(); 		
        $html_feedlinks = '';
        foreach($store_ids as $storeId) {
                $generate_url = $this->getUrl('adminhtml/synerise_export_feed/generate/' , array('store_id' => $storeId));
                $feed_text = Mage::getStoreConfig('synerise_export/generate/feed_result', $storeId);
                if(empty($feed_text)) {
                        $feed_text = Mage::helper('synerise_export')->__('No active feed found');	
                }
                $store_title = Mage::app()->getStore($storeId)->getName();
                $html_feedlinks .= '<tr><td valign="top">' . $store_title . '</td><td>' . $feed_text . '</td><td><a href="' . $generate_url . '">Generate</a></td></tr>';
        }								
        if(empty($html_feedlinks)) {
                $html_feedlinks = Mage::helper('synerise_export')->__('No enabled feed(s) found');
        } else {
                $html_header = '<div class="grid"><table cellpadding="0" cellspacing="0" class="border" style="width: 100%"><tbody><tr class="headings"><th>Store</th><th>Feed</th><th>Generate</th></tr>';
                $html_footer = '</tbody></table></div>';
                $html_feedlinks = $html_header . $html_feedlinks . $html_footer;			
        }
        return sprintf('<tr id="row_%s"><td colspan="6" class="label" style="margin-bottom: 10px;">%s</td></tr>', $element->getHtmlId(), $html_feedlinks);
    }

}

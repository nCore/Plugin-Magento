<?php
class Synerise_Integration_Block_System_Config_Form_Sendhistory extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<table class="form-list">';
        $html .= '<tr><th style="padding: 5px;"></th><th style="padding: 5px;">Waiting</th><th style="padding: 5px;">Sent</th></tr>';        
        
        $orderCollection = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('status', array('nin' => array('canceled')))
                ->addFieldToFilter('synerise_send_at', array('null' => true));
        $ordersQueueCount = $orderCollection->getSize();

        $orderCollection2 = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('status', array('nin' => array('canceled')))
                ->addFieldToFilter('synerise_send_at', array('notnull' => true));
        $ordersSentCount = $orderCollection2->getSize();           
       
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/synerise_order/sendHistory');

        $html .= '<tr>';
            $html .= '<td style="padding: 5px;">Orders</td>';
            $html .= '<td style="padding: 5px;">'.$ordersQueueCount.'</td>';
            $html .= '<td style="padding: 5px;">'.$ordersSentCount.'</td>';
            $html .= '<td style="padding: 5px;"><button onclick="window.location=\''.$url.'\'" type="button"><span>Send</span></button></td>';
        $html .= '</tr>';
        
        $customerCollection = Mage::getModel('customer/customer')
                ->getCollection()
                ->addAttributeToFilter('synerise_send_at', array(array('eq' => '0000-00-00 00:00:00'),array('null' => true)), 'left');
        $customerQueueCount = $customerCollection->getSize();

        $customerCollection2 = Mage::getModel('customer/customer')
                ->getCollection()
                ->addFieldToFilter('synerise_send_at', array('neq' => '0000-00-00 00:00:00'));
        $customerSentCount = $customerCollection2->getSize();           

        $url = Mage::helper('adminhtml')->getUrl('adminhtml/synerise_customer/sendHistory');

        $html .= '<tr>';
            $html .= '<td style="padding: 5px;">Customers</td>';
            $html .= '<td style="padding: 5px;">'.$customerQueueCount.'</td>';
            $html .= '<td style="padding: 5px;">'.$customerSentCount.'</td>';
            $html .= '<td style="padding: 5px;"><button onclick="window.location=\''.$url.'\'" type="button"><span>Send</span></button></td>';
        $html .= '</tr>';
        
        $html .= '</table>';

        return $html;
    }
}

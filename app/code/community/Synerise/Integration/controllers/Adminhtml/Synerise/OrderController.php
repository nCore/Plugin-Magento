<?php
class Synerise_Integration_Adminhtml_Synerise_OrderController extends Mage_Adminhtml_Controller_Action {


    private $snr = null;

    /**
     * @var Synerise_Integration_Helper_Data
     */
    private $helper = null;

    public function _construct()
    {
        $this->helper = Mage::helper('synerise_integration/tracker');

        try {

            $this->snr = $this->helper->getInstance();

        } catch (Exception $e) {
            Mage::logException($e);
        }        

    }
    
    public function sendHistoryAction() 
    {
        $orderCollection = Mage::getModel('sales/order')
                ->getCollection()
                ->addFieldToFilter('status', array('nin' => array('canceled')))
                ->addFieldToFilter('synerise_send_at', array('null' => true));

        $orderCollection->setPageSize(100);

        $pages = $orderCollection->getLastPageNumber();
        $currentPage = 1;
        $sent = 0;
        
        if(!$orderCollection->getSize()) {
            $this->_getSession()->addSuccess($this->helper->__('Orders already sent.'));
        } else {

            do {
                $orderCollection->setCurPage($currentPage);
                $orderCollection->load();

                foreach($orderCollection as $order) {

                    // dodaj kienta
                    $customerData = $this->helper->convertCustomerByOrderToDataSend($order);

                    $uuid = md5($order->getCustomerEmail());
                    $this->snr->client->setUuid($uuid);

                    $this->snr->client->update(
                        $customerData
                    );        

                    // dodaj zamówienie
                    $orderData = array();
                    $products = array();

                    foreach ($order->getAllVisibleItems() as $key => $item) {
                        $products[$item->getSku()] = $this->helper->convertProductToDataSend($item->getProduct());
                        $products[$item->getSku()]['$quantity'] = (int)$item->getData('qty_ordered');
                    }

                    $orderData = $this->helper->convertOrderToDataSend($order);
                    $orderData['products'] = array_values($products);

                    $this->snr->transaction->charge(
                        array_merge($orderData, array('time' => strtotime($order->getCreatedAt())))
                    );

                    // wyślij event
                    $response = $this->snr->sendQueue();

                    // oznacz jako wysłane
                    if($response == true) {
                        $sent++;
                        $order->setData('synerise_send_at',date('Y-m-d H:i:s'))->save();
                        if ($order->getCustomerId()) {
                            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
                            $customer->setData('synerise_send_at',date('Y-m-d H:i:s'))->save();
                        }
                    }

                }

                $currentPage++;
                //clear collection and free memory
                $orderCollection->clear();
            } while ($currentPage <= $pages);        

            if($sent) {             
                 $this->_getSession()->addSuccess($this->helper->__('%s orders sent.', $sent));
            } else {
                $this->_getSession()->addError($this->helper->__('No orders sent.'));
            }
            
        }
        $this->_redirect('*/system_config/edit', array('section'=>'synerise_integration'));
        return false;
        
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/synerise/synerise_integration');
    }    
}
<?php
require_once Mage::getBaseDir() . '/vendor/autoload.php';

class Synerise_Integration_Adminhtml_Synerise_OrderController extends Mage_Adminhtml_Controller_Action {


    private $snr = null;

    private $apiKey = null;

    /**
     * @var Synerise_Integration_Helper_Data
     */
    private $helper = null;

    public function _construct()
    {
        $this->tracker = Mage::getStoreConfig('synerise_integration/tracking/code');
        $this->apiKey = Mage::getStoreConfig('synerise_integration/api/key');
        $this->helper = $helper = Mage::helper('synerise_integration/data');                
        
        try {
            $this->snr = Synerise\SyneriseTracker::getInstance([ //@todo wynieść do helpera
                'apiKey' => $this->apiKey,
                'apiVersion' => '2.1.0',
                'allowFork' => false
            ]);

            $this->snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');
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
            $this->_getSession()->addSuccess($this->_getHelper()->__('Orders already sent.'));
        } else {

            do {
                $orderCollection->setCurPage($currentPage);
                $orderCollection->load();

                foreach($orderCollection as $order) {

                    // dodaj kienta
                    $customerData = array(
                        '$email'    => $order->getCustomerEmail(),
                        'time'      => strtotime($order->getCreatedAt()),
                    );


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
                    }

                }

                $currentPage++;
                //clear collection and free memory
                $orderCollection->clear();
            } while ($currentPage <= $pages);        

            if($sent) {             
                 $this->_getSession()->addSuccess($this->_getHelper()->__('%s orders sent.', $sent));
            } else {
                $this->_getSession()->addError($this->_getHelper()->__('No orders sent.'));
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
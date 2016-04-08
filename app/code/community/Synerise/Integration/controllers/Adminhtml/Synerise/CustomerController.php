<?php
require_once Mage::getBaseDir() . '/vendor/autoload.php';

class Synerise_Integration_Adminhtml_Synerise_CustomerController extends Mage_Adminhtml_Controller_Action {


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
                'apiVersion' => '2.0.1',
                'allowFork' => false
            ]);

            $this->snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');
        } catch (Exception $e) {
            Mage::logException($e);
        }        

    }
    
    public function sendHistoryAction() 
    {
        $customerCollection = Mage::getModel('customer/customer')
                ->getCollection();
        $customerCollection
                ->addAttributeToSelect('firstname')
                ->addAttributeToSelect('lastname')
                ->addAttributeToSelect('created_in')
                ->addAttributeToFilter('synerise_send_at', array('null' => true), 'left');
        
        $customerCollection->setPageSize(100);

        $pages = $customerCollection->getLastPageNumber();
        $currentPage = 1;
        $sent = 0;
        
        if(!$customerCollection->getSize()) {
            $this->_getSession()->addSuccess($this->_getHelper()->__('Orders already sent.'));
        } else {

            do {
                $customerCollection->setCurPage($currentPage);
                $customerCollection->load();

                foreach($customerCollection as $customer) {

                    // dodaj kienta
                    $customerData = $this->helper->convertCustomerToDataSend($customer);
                    
                    $uuid = md5($customer->getEmail());
                    $this->snr->client->setUuid($uuid);               
                    
                    $this->snr->client->update(
                        $customerData
                    );

                    // wyślij event
                    $response = $this->snr->sendQueue();

                    // oznacz jako wysłane
                    if($response == true) {
                        $sent++;
                        $customer->setData('synerise_send_at',date('Y-m-d H:i:s'))->save();
                    }
                }

                $currentPage++;
                //clear collection and free memory
                $customerCollection->clear();
            } while ($currentPage <= $pages);        

            if($sent) {             
                 $this->_getSession()->addSuccess($this->_getHelper()->__('%s customers sent.', $sent));
            } else {
                $this->_getSession()->addError($this->_getHelper()->__('No customers sent.'));
            }
            
        }
        $this->_redirect('*/system_config/edit', array('section'=>'synerise_integration'));
        return false;
        
    }
}
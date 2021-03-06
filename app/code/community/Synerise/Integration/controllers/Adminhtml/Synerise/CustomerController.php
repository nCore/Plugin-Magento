<?php
class Synerise_Integration_Adminhtml_Synerise_CustomerController extends Mage_Adminhtml_Controller_Action {


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
            $this->_getSession()->addSuccess($this->_getHelper()->__('Customers already sent.'));
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
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/synerise/synerise_integration');
    }
}
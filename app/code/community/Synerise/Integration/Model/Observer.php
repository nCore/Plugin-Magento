<?php
class Synerise_Integration_Model_Observer
{

    private $snr = null;

    /**
     * @var Synerise_Integration_Helper_Tracker
     */
    private $helper = null;
    private $shutdown = false;

    public function __construct()
    {
        try {
            $this->helper = Mage::helper('synerise_integration/tracker');

            $this->snr = $this->helper->getInstance();

//            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
//                $this->snr->client->customIdentify(
//                    Mage::getSingleton('customer/session')->getCustomer()->getId()
//                );
//            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        register_shutdown_function(array($this, 'destruct'));
    }

    public function destruct()
    {
        $this->shutdown = true;

        if(empty($this->snr) && empty($this->snr->client)) {
            return $this;
        }

        try {

            $queue = $this->snr->client->getRequestQueue();
            foreach($queue as $item) {
                if(!empty($item['params']) && !empty($item['params']['$entityId'])) {
                    $attributeModel = Mage::getSingleton( 'eav/config' )
                        ->getAttribute( 'customer', 'synerise_send_at' );
                    $tableData = array(
                        'entity_type_id' => $attributeModel->getEntityTypeId(),
                        'attribute_id' => $attributeModel->getAttributeId(),
                        'entity_id' => $item['params']['$entityId'],
                        'value' => date('Y-m-d H:i:s')
                    );
                    $adapter = $attributeModel->getEntity()->getWriteConnection();
                    $tableName = $attributeModel->getBackendTable();
                    $adapter->insertOnDuplicate($tableName, $tableData, array('value'));
                }
            }

            if(empty($this->snr->transaction)) {
                return $this;
            }

            $queue = $this->snr->transaction->getRequestQueue();

            if(empty($queue)) {
                return $this;
            }

            $resource = Mage::getSingleton('core/resource');
            $writeConnection = $resource->getConnection('core_write');
            $table = $resource->getTableName('sales/order');

            foreach($queue as $item) {
                if(!empty($item['params']) && !empty($item['params']['$orderId'])) {
                    $incrementId = $item['params']['$orderId'];
                    $query = "UPDATE {$table} SET `synerise_send_at` = '".date('Y-m-d H:i:s')."' WHERE increment_id = '{$incrementId}'";
                    $writeConnection->query($query);
                }

            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Action for "customer_register_success" event
     * @param Varien_Event_Observer $observer
     */
    public function customerRegisterSuccess($observer)
    {

        try {

            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            $event = $observer->getEvent();
            $customer = $event->getCustomer();

            $dataSend = $this->helper->convertCustomerToDataSend($customer);

            $this->snr->client->update($dataSend);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
     * Action for "checkout_cart_add_product_complete" event
     * @param Varien_Event_Observer $observer
     */
    public function customerAddToCart($observer)
    {

        try {
            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            $this->snr->transaction->addProduct(
                $this->helper->convertProductToDataSend($observer->getProduct())
            );

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
     * Action for "sales_quote_remove_item" event
     * @param Varien_Event_Observer $observer
     */
    public function customerRemoveFromCart($observer)
    {
        try {

            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            $product = $observer->getQuoteItem()->getProduct();

            $this->snr->transaction->removeProduct(
                $this->helper->convertProductToDataSend($product)
            );

        } catch (Exception $e) {
            Mage::logException($e);
        }

    }


    /**
     * Action for "checkout_onepage_controller_success_action" event
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function customerOrderComplete(Varien_Event_Observer $observer)
    {
        try {

            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            $order = $observer->getEvent()->getOrder();

            $this->snr->client->update(
                $this->helper->convertCustomerByOrderToDataSend($order)
            );

            foreach ($order->getAllVisibleItems() as $key => $item) {
                $products[$item->getSku()] = $this->helper->convertProductToDataSend($item->getProduct());
                $products[$item->getSku()]['$quantity'] = (int)$item->getData('qty_ordered');
            }

            $sendData = $this->helper->convertOrderToDataSend($order);
            $sendData['products'] = array_values($products);

            //$order->getCas
            $this->snr->transaction->charge(
                $sendData
            );


        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;


    }


    /**
     * Action for "checkout_onepage_controller_success_action" event
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function cancelOrderItem(Varien_Event_Observer $observer)
    {
        try {

            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();
            $stateCanceled = $order::STATE_CANCELED;
            // Only trigger when an order enters processing state.
            if ($order->getState() == $stateCanceled && $order->getOrigData('state') != $stateCanceled) {

                if (!Mage::getSingleton('customer/session')->isLoggedIn() && !Mage::getSingleton('admin/session')->isLoggedIn()) {

                    $this->snr->client->update(
                        $this->helper->convertCustomerByOrderToDataSend($order)
                    );
                }


                foreach ($order->getAllVisibleItems() as $key => $item) {
                    $products[$item->getSku()] = $this->helper->convertProductToDataSend($item->getProduct());
                    $products[$item->getSku()]['$quantity'] = (int)$item->getData('qty_ordered');
                }

                $sendData = $this->helper->convertOrderToDataSend($order);
                $sendData['products'] = array_values($products);

                $this->snr->transaction->cancel(
                    $sendData
                );


            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;


    }

    /**
     * Action for "customer_login" event
     * @param $observer
     */
    public function customerLogin(Varien_Event_Observer $observer)
    {


        try {

            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            $this->snr->client->logIn();
            $this->snr->client->update(
                $this->helper->convertCustomerToDataSend(
                    $observer->getEvent()->getCustomer()
                )
            );

        } catch (Exception $e) {
            Mage::logException($e);
            die('stoop');
        }
    }


    /**
     * Action for "customer_logout" event
     * @param $observer
     */
    public function customerLogout(Varien_Event_Observer $observer)
    {

        try {

            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            $this->snr->client->logOut();
        } catch (Exception $e) {
            Mage::logException($e);
        }

    }

    /**
     * Action for "wishlist_product_add_after" event
     * @param $observer
     */
    public function wishlistProductAddAfter(Varien_Event_Observer $observer)
    {

        try {
            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }

            $sendData = array();
            $sendData['products'] = array();
            foreach ($observer->getItems() as $item) {
                $sendData['products'][] = $this->helper->convertProductToDataSend($item->getProduct());
            }

            $this->snr->transaction->addFavoriteProduct(
                $sendData
            );


        } catch (Exception $e) {
            Mage::logException($e);
        }

    }

    /**
     * Action for "customer_save_before" event
     * @param $observer
     */
    public function customerSaveBefore(Varien_Event_Observer $observer)
    {
        return $this;
    }

    /**
     * Action for "customer_save_after" event
     * @param $observer
     */
    public function customerSaveAfter(Varien_Event_Observer $observer)
    {
        if($this->shutdown) {
            return $this;
        }

        try {

            if(!$this->checkEventIsEnabled($observer)) {
                return $this;
            }
            $this->snr->client->update(
                $this->helper->convertCustomerToDataSend(
                    $observer->getEvent()->getCustomer()
                )
            );

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    private function checkEventIsEnabled(Varien_Event_Observer $observer) {
        
        return Mage::getStoreConfig('synerise_integration/trackingevents/'.$observer->getEvent()->getName()) == '1';
    }

}
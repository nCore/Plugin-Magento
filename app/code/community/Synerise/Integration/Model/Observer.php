<?php

require_once Mage::getBaseDir() . '/vendor/autoload.php';

class Synerise_Integration_Model_Observer
{

    private $snr = null;

    private $apiKey = null;

    /**
     * @var Synerise_Integration_Helper_Data
     */
    private $helper = null;

    public function __construct()
    {
        try {
            $this->tracker = Mage::getStoreConfig('synerise_integration/tracking/code');
            $this->apiKey = Mage::getStoreConfig('synerise_integration/api/key');

            $this->helper = $helper = Mage::helper('synerise_integration/data');

            $this->snr = Synerise\SyneriseTracker::getInstance([ //@todo wynieść do helpera
                'apiKey' => $this->apiKey,
                'apiVersion' => '2.0.1',
                'allowFork' => true
            ]);

            $this->snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');

//            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
//                $this->snr->client->customIdentify(
//                    Mage::getSingleton('customer/session')->getCustomer()->getId()
//                );
//            }
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
        try {
            $observer->getEvent()->getCustomer()->setData('synerise_send_at',date('Y-m-d H:i:s'));
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Action for "customer_save_after" event
     * @param $observer
     */
    public function customerSaveAfter(Varien_Event_Observer $observer)
    {
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
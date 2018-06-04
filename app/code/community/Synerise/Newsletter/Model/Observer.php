<?php
class Synerise_Newsletter_Model_Observer
{
    /**
     * Update subscriber status with value from Synerise Api
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function updateStatusWithSyneriseValue(Varien_Event_Observer $observer)
    {
        /** @var Synerise_Integration_Helper_Client $clientHelper */
        $clientHelper = Mage::helper('synerise_integration/client');

        /** @var Synerise_Newsletter_Helper_Data $newsletterHelper */
        $newsletterHelper = Mage::helper('synerise_newsletter');

        if(!$newsletterHelper->isEnabled()) {
            return $this;
        }

        $clientData = $clientHelper->getSyneriseData();

        if(!isset($clientData['newsletter'])) {
            return $this;
        }

        $status = (int) $newsletterHelper->convertSyneriseStatus($clientData['newsletter']);
        $isSubscribed = ($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);

        $customer = $observer->getCustomer();
        if(empty($customer)) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
        }

        // is customer synchronized
        if(!$customer->getSyneriseSendAt()) {
            return $this;
        }

        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);

        if($subscriber->getStatus() == $status) {
            return $this;
        }

        $subscriber->setStatus($status);
        $customer->setIsSubscribed($isSubscribed);

        Mage::getModel('core/resource_transaction')
            ->addObject($customer)
            ->addObject($subscriber)
            ->save();

        return $this;
    }

}
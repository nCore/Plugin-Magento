<?php
class Synerise_Newsletter_Model_Subscriber extends Mage_Newsletter_Model_Subscriber
{

    /**
     * Subscribes by email
     *
     * @param string $email
     * @throws Exception
     * @return int
     */
    public function subscribe($email)
    {
        $newsletterHelper = Mage::helper('synerise_newsletter');
        
        if(!$newsletterHelper->isEnabled()) {
            return parent::subscribe($email);
        }

        $postData = Mage::app()->getRequest()->getPost();
        unset($postData['email']);
        unset($postData['password']);
        unset($postData['confirmation']);
        
        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();

        $this->setSubscriberEmail($email);

        if ($isSubscribeOwnEmail) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());           
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

//        if($this->getStatus() == self::STATUS_SUBSCRIBED || ($isSubscribeOwnEmail && !$newsletterHelper->confirmRegisteredFlag())) {
//
//            // mark as subscribed, skip confirm email
//            $response = $newsletterHelper->updateNewsletterAgreement($email, 'enabled');
//
//        } else {

        // subscribe & send confirm email                
        $response = $newsletterHelper->subscribe($email, $postData);
//        }
        
        if(!$response) {
            throw new Exception('Empty response');            
        }
        
        $this->setStatus($newsletterHelper->convertSyneriseStatus($response->getNewsletterAgreement()));
        $this->setIsStatusChanged(true);

        try {
            $this->save();            

            return $this->getStatus();
        } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

    }

    /**
     * Saving customer subscription status
     *
     * @param   Mage_Customer_Model_Customer $customer
     * @return  Mage_Newsletter_Model_Subscriber
     */
    public function subscribeCustomer($customer)
    {
        /** @var Synerise_Newsletter_Helper_Data $newsletterHelper */
        $newsletterHelper = Mage::helper('synerise_newsletter');

        if(!$newsletterHelper->isEnabled()) {
            return parent::subscribeCustomer($customer);
        }

        $this->loadByCustomer($customer);

        if ($customer->getImportMode()) {
            $this->setImportMode(true);
        }

        if (!$customer->getIsSubscribed() && !$this->getId()) {
            // If subscription flag not set or customer is not a subscriber
            // and no subscribe below
            return $this;
        }

        $customer->setConfirmation(null);

        $postData = Mage::app()->getRequest()->getPost();
        unset($postData['email']);
        unset($postData['password']);
        unset($postData['confirmation']);

        $clientInstance = Mage::helper('synerise_integration/api')->getInstance('Client');
        try {
            /** @var $clientInstance Synerise\SyneriseClient */
            $response = $clientInstance->addOrUpdateClient(array(
                'email' => $customer->getEmail(),
                'newsletterAgreement' => $customer->getIsSubscribed() ? 'enabled' : 'disabled'
            ));
        } catch(Exception $e) {

        }

        $status = $customer->getIsSubscribed() ? self::STATUS_SUBSCRIBED : self::STATUS_UNSUBSCRIBED;
        $this->setStatus($status);

        if(!$this->getId()) {
            $storeId = $customer->getStoreId();
            if ($customer->getStoreId() == 0) {
                $storeId = Mage::app()->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            }
            $this->setStoreId($storeId)
                ->setCustomerId($customer->getId())
                ->setEmail($customer->getEmail());
        } else {
            $this->setStoreId($customer->getStoreId())
                ->setEmail($customer->getEmail());
        }

        $this->save();

        return $this;
    }


}
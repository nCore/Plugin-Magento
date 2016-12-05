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

        if($this->getStatus() == self::STATUS_SUBSCRIBED || ($isSubscribeOwnEmail && !$newsletterHelper->confirmRegisteredFlag())) {
            
            // mark as subscribed, skip confirm email                                
            $response = $newsletterHelper->updateNewsletterAgreement($email, 'enabled');
            
        } else {
            
            // subscribe & send confirm email                
            $response = $newsletterHelper->subscribe($email, $postData);
            
        }
        
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

}
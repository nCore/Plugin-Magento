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
        if(! Mage::helper('synerise_newsletter')->isEnabled()) {
            return parent::subscribe($email);
        }
        
        try{
            $postData = Mage::app()->getRequest()->getPost();
            unset($postData['email']);
            
            $snr = Mage::helper('synerise_integration/api')->getInstance('Newsletter', array('apiVersion' => '1.0' ));
            $snr->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');

            $snr->subscribe($email, $postData);

            // skip further processing
            if(!Mage::getStoreConfig('synerise_newsletter/settings/persist_locally')) {
               return self::STATUS_SUBSCRIBED;
            }
             
        } catch (Synerise\Exception\SyneriseException $e) {
            switch ($e->getCode()) {
                case Synerise\Exception\SyneriseException::EMPTY_NEWSLETTER_SETTINGS:
                    Mage::throwException(Mage::helper('synerise_integration')->__('Sorry, the newsletter has not yet been configured. Please try again later.'));
                    break;                
                case Synerise\Exception\SyneriseException::NEWLETTER_ALREADY_SUBSCRIBED:
                    Mage::throwException(Mage::helper('synerise_integration')->__('The address already exists in the database.'));
                    break;
                default:
                    throw new Exception($e->getMessage());
            }
        }
        
        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        $ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        $isSubscribeOwnEmail = $customerSession->isLoggedIn() && $ownerId == $customerSession->getId();

//        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED
//            || $this->getStatus() == self::STATUS_NOT_ACTIVE
//        ) {
            $this->setStatus(self::STATUS_SUBSCRIBED);
            $this->setSubscriberEmail($email);
//        }

        if ($isSubscribeOwnEmail) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            
            return $this->getStatus();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }

}
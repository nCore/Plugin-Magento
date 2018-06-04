<?php
include Mage::getModuleDir('controllers','Mage_Newsletter').DS.'SubscriberController.php';

class Synerise_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    /**
      * New subscription action
      */
    public function newAction()
    {
        $trackerInstance = Mage::helper('synerise_integration/tracker')->getInstance();
        if(!empty($this->getRequest()->getParam('email'))) {
            $trackerInstance->client->identifyByEmail($this->getRequest()->getParam('email'));
        }
        $label = !empty($this->getRequest()->getParam('label'))
            ? $this->getRequest()->getParam('label') : 'newsletterDefault';
         $trackerInstance->formSubmit($label, $this->getRequest()->getParams());

        if(!$this->getRequest()->isXmlHttpRequest() || !Mage::helper('synerise_newsletter')->ajaxSubmitFlag()) {
            parent::newAction();
        } else {
            if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
                $customerSession    = Mage::getSingleton('customer/session');
                $email              = (string) $this->getRequest()->getPost('email');

                try {
                    if (!Zend_Validate::is($email, 'EmailAddress')) {
                        Mage::throwException($this->__('Please enter a valid email address.'));
                    }

                    if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                        !$customerSession->isLoggedIn()) {
                        Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                    }

                    if(Mage::helper('synerise_newsletter')->forceLoginFlag()) {
                        $ownerId = Mage::getModel('customer/customer')
                                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                                ->loadByEmail($email)
                                ->getId();
                        if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                            Mage::throwException($this->__('This email address is already assigned to another user.'));
                        }
                    }

                    $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                    if ($status == Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED) {
                        $this->getResponse()->setBody(json_encode(array('message' => $this->__('Confirmation request has been sent.'), 'status' => 'ok')));
                    } elseif ($status == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED) {
                        $this->getResponse()->setBody(json_encode(array('message' => $this->__('Thank you for your subscription.'), 'status' => 'ok')));
                    } else {
                        $this->getResponse()->setBody(json_encode(array('message' => $this->__('There was a problem with your subscription. Please try again later.'), 'status' => 'error')));
                    }
                }
                catch (Mage_Core_Exception $e) {
                    $this->getResponse()->setBody(json_encode(array('status' => 'error', 'message' => ($this->__($e->getMessage())))));
                }
                catch (Exception $e) {
                    $this->getResponse()->setBody(json_encode(array('status' => 'error')));
                    Mage::logException($e);
                }
            }
        }
    }    
}

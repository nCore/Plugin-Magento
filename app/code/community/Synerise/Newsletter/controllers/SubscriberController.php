<?php
include Mage::getModuleDir('controllers','Mage_Newsletter').DS.'SubscriberController.php';

class Synerise_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
    /**
      * New subscription action
      */
    public function newAction()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');

        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');
            $sex              = (string) $this->getRequest()->getPost('sex');

            $postData = $this->getRequest()->getPost();

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                /**
                 * @var SyneriseNewsletter
                 */
                $api = Mage::getModel("synerise_newsletter/subscriber");


                try{
                    $additionalParams = array();
                    if($sex) {
                        $additionalParams['sex'] = $sex;
                    }

                    $additionalParams = array_merge($additionalParams, $postData);

                    $api->subscribe($email, $additionalParams);
                    $this->getResponse()->setBody(json_encode(array('message' => 'newsletter_request_success', 'status' => 'ok')));

                }catch (Synerise\Exception\SyneriseException $e) {

                    switch ($e->getCode()) {
                        case Synerise\Exception\SyneriseException::NEWLETTER_ALREADY_SUBSCRIBED:
                            $this->getResponse()->setBody(json_encode(array('message' => 'already_subscribed', 'status' => 'ok')));
                            break;
                        default:
                            $this->getResponse()->setBody(json_encode(array('message' => 'newsletter_error')));

                    }
                }
            }

            catch (Exception $e) {
                $this->getResponse()->setBody(json_encode(array('message' => 'newsletter_error')));
            }
        }
        //$this->getResponse()->sendResponse();
    }

    /**
     * Subscription confirm action
     */
    public function confirmAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $subscriber = Mage::getModel('newsletter/subscriber')->load($id);
            $session = Mage::getSingleton('core/session');

            if($subscriber->getId() && $subscriber->getCode()) {
                if($subscriber->confirm($code)) {
                    $session->addSuccess($this->__('Your subscription has been confirmed.'));
                } else {
                    $session->addError($this->__('Invalid subscription confirmation code.'));
                }
            } else {
                $session->addError($this->__('Invalid subscription ID.'));
            }
        }

        $this->_redirectUrl(Mage::getBaseUrl());
    }

    /**
     * Unsubscribe newsletter
     */
    public function unsubscribeAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $session = Mage::getSingleton('core/session');
            try {
                Mage::getModel('newsletter/subscriber')->load($id)
                    ->setCheckCode($code)
                    ->unsubscribe();
                $session->addSuccess($this->__('You have been unsubscribed.'));
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $e->getMessage());
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the un-subscription.'));
            }
        }
        $this->_redirectReferer();
    }
}

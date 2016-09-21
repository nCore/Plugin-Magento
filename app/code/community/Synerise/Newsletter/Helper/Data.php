<?php
class Synerise_Newsletter_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return Mage::getStoreConfig('synerise_newsletter/settings/enable');
    }
    
    public function confirmRegisteredFlag()
    {
        return Mage::getStoreConfig('synerise_newsletter/settings/confirm_registered');
    }
    
    public function convertSyneriseStatus($status)
    {
        switch ($status):
            case 'enabled':
                return Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED;
            case 'confirmation':
                return Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED;
            case 'disabled':
            default:
                return Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE;
        endswitch;        
    }
    
    public function subscribe($email, $postData)
    {
        try{

            /*
             * @var Synerise\SyneriseNewsletter
             */
            $newsletterInstance = Mage::helper('synerise_integration/api')->getInstance('Newsletter', array('apiVersion' => '1.0' ));
            $newsletterInstance->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');

            return $newsletterInstance->subscribe($email, $postData);   

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
    }
    
    public function updateNewsletterAgreement($email, $status)
    {
        $clientInstance = Mage::helper('synerise_integration/api')->getInstance('Client', array('apiVersion' => '1.0' ));
        $clientInstance->setPathLog(Mage::getBaseDir('var') . DS . 'log' . DS . 'synerise.log');

        $items = array(
            array(
                'email' => $email, 
                'data' => array(
                    'newsletterAgreement' => $status
                )
            )                
        );

        $response = $clientInstance->batchAddOrUpdateClients($items);
        
        if(isset($response['data']['clients'][$email]['newsletter_agreement'])) {
            return new Synerise\Response\Newsletter(array(
                'message' => $response['message'],
                'description' => $response['description'],
                'newsletterAgreement' => $response['data']['clients'][$email]['newsletter_agreement']
            ));
        }
            
        return false;
    }

}
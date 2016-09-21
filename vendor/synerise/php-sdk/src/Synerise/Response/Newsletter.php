<?php
namespace Synerise\Response;

use Synerise\Exception;

class Newsletter extends AbstractResponse
{

    /**
     * @var string
     */
    private $_status;

    /**
     * @var string
     */
    private $_message;
    
    /**
     * @var string
     */    
    private $_newsletterAgreement;

    public function __construct($response)
    {
        parent::__construct($response);

        if(!isset($response['data'])) {
            return;
        }

        $this->_status = isset($response['data']['status']) ? $response['data']['status'] : null;
        $this->_message = isset($response['data']['message']) ? $response['data']['message'] : null;
        $this->_newsletterAgreement = isset($response['data']['newsletterAgreement']) ? $response['data']['newsletterAgreement'] : null;
    }

    public function success()
    {
        if ($this->_status == 'ok' && $this->_message == 'newsletter_request_success') {
            return $this;
        }

        switch ($this->_message) {
            case 'already_subscribed':
                throw new Exception\SyneriseException('Newsletter.AlreadySubscribed', Exception\SyneriseException::NEWLETTER_ALREADY_SUBSCRIBED);
            default:
                throw new Exception\SyneriseException('Newsletter.UnknownError', Exception\SyneriseException::UNKNOWN_ERROR);
        }
    }

    public function fail()
    {
        switch ($this->_status) {
            case 'empty_newsletter_settings':
                throw new Exception\SyneriseException('Newsletter.NotConfigured', Exception\SyneriseException::EMPTY_NEWSLETTER_SETTINGS);
            default:
                throw new SyneriseException('API Synerise not responsed 200.', SyneriseException::API_RESPONSE_ERROR);
        }   
    }
    
    public function getNewsletterAgreement()
    {
        return $this->_newsletterAgreement;
    }
}
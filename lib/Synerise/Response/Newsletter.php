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

    /**
     *
     * @param array $response
     * @return void
     */
    public function __construct(array $response)
    {
        if(isset($response['data'])) {
            parent::__construct($response);
            $data = $response['data'];
        } else {
            $data = $response;
        }

        $this->_status = isset($data['status']) ? $data['status'] : null;
        $this->_message = isset($data['message']) ? $data['message'] : null;
        $this->_newsletterAgreement = isset($data['newsletterAgreement']) ? $data['newsletterAgreement'] : null;
    }

    /**
     * Return Newsletter response object.
     *
     * @return \Synerise\Response\Newsletter
     * @throws Exception\SyneriseException
     */
    public function success()
    {
        if ($this->_status == 'ok' && $this->_message == 'newsletter_request_success') {
            return $this;
        }
        if ($this->_status == 'empty_newsletter_settings') {
            throw new Exception\SyneriseException('Newsletter.NotConfigured', Exception\SyneriseException::EMPTY_NEWSLETTER_SETTINGS);
        }

        switch ($this->_message) {
            case 'already_subscribed':
                throw new Exception\SyneriseException('Newsletter.AlreadySubscribed', Exception\SyneriseException::NEWLETTER_ALREADY_SUBSCRIBED);
            default:
                throw new Exception\SyneriseException('Newsletter.UnknownError', Exception\SyneriseException::UNKNOWN_ERROR);
        }
    }

    /**
     * Throws exception based on response status
     *
     * @throws Exception\SyneriseException
     * @throws SyneriseException
     */
    public function fail()
    {
        switch ($this->_status) {
            case 'empty_newsletter_settings':
                throw new Exception\SyneriseException('Newsletter.NotConfigured', Exception\SyneriseException::EMPTY_NEWSLETTER_SETTINGS);
            default:
                throw new Exception\SyneriseException('API Synerise not responsed 200.', Exception\SyneriseException::API_RESPONSE_ERROR);
        }   
    }

    /**
     * Subscirption message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Newsletter agreement
     *
     * @return string
     */
    public function getNewsletterAgreement()
    {
        return $this->_newsletterAgreement;
    }

    /**
     * Subscription status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->_status;
    }
}
<?php
namespace Synerise\Response;

use Synerise\Exception;

class Newsletter
{

    /**
     * @var int
     */
    private $_status = false;

    /**
     *
     * @var string
     */
    private $_description;

    /**
     * @var
     */
    private $_message;

    public function __construct($response)
    {

        $this->_status = isset($response['status']) ? $response['status'] : false;
        $this->_message = isset($response['message']) ? $response['message'] : null;
        $this->_description = isset($response['description']) ? $response['description'] : null;

    }

    public function success()
    {

        if ($this->_status == 'ok' && $this->_message == 'newsletter_request_success') {
            return true;
        }

        switch ($this->_message) {
            case 'already_subscribed':
                throw new Exception\SyneriseException('Newsletter.AlreadySubscribed', Exception\SyneriseException::NEWLETTER_ALREADY_SUBSCRIBED);
                break;
            default:
                throw new Exception\SyneriseException('Newsletter.UnknownError', Exception\SyneriseException::UNKNOWN_ERROR);
        }

    }

    public function fail()
    {
        
        switch ($this->_status) {
            case 'empty_newsletter_settings':
                throw new Exception\SyneriseException('Newsletter.NotConfigured', Exception\SyneriseException::EMPTY_NEWSLETTER_SETTINGS);
                break;                
            default:
                throw new SyneriseException('API Synerise not responsed 200.', SyneriseException::API_RESPONSE_ERROR);
        }   
        
    }
    
}
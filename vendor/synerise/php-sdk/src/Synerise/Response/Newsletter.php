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

        $this->_status = $response['status'];
        $this->_message = $response['message'];
        $this->_description = $response['description'];

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

}
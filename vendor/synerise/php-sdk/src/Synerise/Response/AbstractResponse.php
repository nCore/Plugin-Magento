<?php
namespace Synerise\Response;

abstract class AbstractResponse
{

    /**
     * @var int
     */
    private $_responseCode;

    /**
     * @var string
     */
    private $_responseDescription;

    /**
     * @var string
     */
    private $_responseMessage;

    /**
     * Sets default response data
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->_responseCode = isset($response['code']) ? (int) $response['code'] : null;
        $this->_responseDescription = isset($response['description']) ? $response['description'] : null;
        $this->_responseMessage = isset($response['message']) ? $response['message'] : null;
    }

    public function getResponseCode()
    {
        return $this->_responseCode;
    }

    public function getResponseDescription()
    {
        return $this->_responseDescription;
    }

    public function getResponseMessage()
    {
        return $this->_responseMessage;
    }
}
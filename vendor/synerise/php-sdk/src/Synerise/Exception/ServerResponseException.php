<?php 

use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

/**
 * Exception when a server error is encountered (5xx codes)
 */
class ServerResponseException extends BadResponseException
{
}

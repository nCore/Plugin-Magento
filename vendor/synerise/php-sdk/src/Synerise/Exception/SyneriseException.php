<?php
namespace Synerise\Exception;

class SyneriseException extends \RuntimeException
{
    const NEWLETTER_ALREADY_SUBSCRIBED = 20000;


    const COUPON_ALREADY_USED = 20105;
    const COUPON_NOT_FOUND = 20101;


    const UNKNOWN_ERROR = -1;
    const API_RESPONSE_ERROR = 500;
    const API_RESPONSE_INVALID = 30000;
}

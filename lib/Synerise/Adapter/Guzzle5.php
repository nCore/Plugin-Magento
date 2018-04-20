<?php
namespace Synerise\Adapter;

use GuzzleHttp\Event\Emitter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use GuzzleHttp\Subscriber\Log\Formatter;

class Guzzle5
{
    static public function prepareConfig($config, $logger)
    {
        $emitter = new Emitter();
        if($logger) {
            if ($logger instanceof \Psr\Log\LoggerInterface ) {
                $emitter->attach(
                    new LogSubscriber($logger, Formatter::DEBUG)
                );
            } else {
                throw new \Exception('Logger must implement PsrLogLoggerInterface');
            }
        }
        $config['emitter'] = $emitter;

        return $config;
    }
}
<?php
namespace Synerise\Adapter;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\MessageFormatter;

class Guzzle6
{
    static public function prepareConfig($config, $logger)
    {
        if(!isset($config['base_uri']) && isset($config['base_url'])) {
            $config['base_uri'] = $config['base_url'];
        }
        $stack = HandlerStack::create();
        if($logger) {
            if ($logger instanceof \Psr\Log\LoggerInterface ) {
                $stack->push(
                    Middleware::log($logger, new MessageFormatter(MessageFormatter::DEBUG))
                );
            } else {
                throw new \Exception('Logger must implement PsrLogLoggerInterface');
            }
        }
        $config['handler'] = $stack;
        
        return $config;
    }
}
<?php
namespace Synerise;

use InvalidArgumentException;
use GuzzleHttp\Client;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Ring\Client\MockHandler;
use GuzzleHttp\Collection;


abstract class SyneriseAbstractHttpClient extends Client
{

    /** @var array The required config variables for this type of client */
    public static $required = [
        'apiKey',
        'headers',
    ];

    /** @var string */
    const DEFAULT_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    /** @var string */
    const DEFAULT_ACCEPT_HEADER = 'application/json';

    /** @var string */
    const USER_AGENT = 'synerise-php-sdk/2.1';

    /** @var string */
    const DEFAULT_API_VERSION = '2.1';

    /** @var string */
    const BASE_API_URL = 'http://api.synerise.com';

    /** @var string */
    const BASE_TCK_URL = 'http://tck.synerise.com/sdk-proxy';


    private static $_instances = array();

    protected $_pathLog = '/var/log/synerise.log';

    protected $_log = true;


    /**
     * Returns a singleton instance of SyneriseAbstractHttpClient
     * @param array $config
     * @return SyneriseAbstractHttpClient
     */
    public static function getInstance($config = array())
    {
        $class = get_called_class();

        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class($config);
        }
        return self::$_instances[$class];
    }

    /**
     * Instantiates a new SyneriseCoupon instance.
     * @param array $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $config = Collection::fromConfig($config, static::getDefaultConfig(), static::$required);
        $this->configure($config);
    }

    /**
     * Configures the client by setting the appropriate headers, service description and error handling
     *
     * @param Collection $config
     */
    protected function configure($config)
    {
        $this->setDefaultOption('headers', $this->margeHeaders($config));
        $this->setErrorHandler();
    }

    /**
     * Marge headers default and custom
     *
     * @return array
     */
    private function margeHeaders($config)
    {
        $default = $this->getDefaultConfig();

        $apiVersion = $config->get('apiVersion');
        $configHeaders = $config->get('headers');
        $defaultHeaders = $default['headers'];

        $headers = $configHeaders + $defaultHeaders;
        $headers['Api-Key'] = $config->get('apiKey');
        $headers['Api-Version'] = empty($apiVersion) ? self::DEFAULT_API_VERSION : $config->get('apiVersion');

        return $headers;
    }

    /**
     * Overrides the error handling in Guzzle so that when errors are encountered we throw
     * Synersie errors, not Guzzle ones.
     *
     */
    private function setErrorHandler()
    {
        $this->getEmitter()->on('error', function (ErrorEvent $e) {
            //@TODO ErrorHendler
            //if ($e->getResponse()->getStatusCode() >= 400 && $e->getResponse()->getStatusCode() < 600) {
            //}
        });
    }


    /**
     * Gets the default configuration options for the client
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            'base_url' => self::BASE_API_URL,
            'headers' => [
                'Content-Type' => self::DEFAULT_CONTENT_TYPE,
                'Accept' => self::DEFAULT_ACCEPT_HEADER,
                'User-Agent' => self::USER_AGENT,
            ]
        ];
    }

    /**
     * @return bool|string
     */
    protected function getUuid()
    {
        if($this->_uuid) {
            return $this->_uuid;
        }

        $snrsP = isset($_COOKIE['_snrs_p'])?$_COOKIE['_snrs_p']:false;
        if ($snrsP) {
            $snrsP = explode('&', $snrsP);
            foreach ($snrsP as $snrs_part) {
                if (strpos($snrs_part, 'uuid:') !== false) {
                    return str_replace('uuid:', null, $snrs_part);
                }
            }
        }

        return false;
    }

    public function setPathLog($pathFile)
    {
        $this->_pathLog = $pathFile;
    }

    public function _log($message, $tag)
    {
        if ($this->_log) {
            file_put_contents($this->_pathLog, print_r("----------------\n" .
                date("Y-m-d H:i:s") . " $tag: \n " . (string)$message . "\n", true), FILE_APPEND);
        }
    }

}
<?php
namespace Synerise\Helper;

class Cookie extends HelperAbstract
{
    const SNRS_P            = '_snrs_p';
    const SNRS_UUID         = '_snrs_uuid';
    const SNRS_RESET_UUID   = '_snrs_reset_uuid';

    protected $_data = array();

    protected $_uuid;

    protected $_context = \Synerise\SyneriseTracker::APP_CONTEXT_CLIENT;

    public function __construct(array $config = array())
    {
        $_cookie = array();
        if(isset($config['context']) && $config['context'] == \Synerise\SyneriseTracker::APP_CONTEXT_SYSTEM && isset($config['cookie'])) {
            $_cookie = is_string($config['cookie']) ? json_decode($config['cookie'], true) : (array) $config['cookie'];
        }

        $this->setCookiesData((empty($_cookie) && isset($_COOKIE)) ? $_COOKIE : $_cookie);
        if(isset($config['context'])) {
            $this->_context = $config['context'];
        }
    }

    /**
     * Determine whether current session allows cookie use
     *
     * @return bool
     */
    public static function isAllowedUse() {
        return (!headers_sent() && php_sapi_name() !== "cli") ? true : false;
    }

    /**
     * Return cookie if set
     *
     * @param string $name
     * @return string
     */
    public function getCookieString($name) {
        if(!isset($this->_data[$name])) {
           $this->_data[$name] = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
        }
        return htmlspecialchars_decode($this->_data[$name]);
    }

    /**
     * Return cookie as array
     *
     * @param string $name
     * @return array|null
     */
    public function getCookie($name) {
        return $this->_breakCookie($this->getCookieString($name));
    }

    public function getEmailHash() {
        $p = $this->getCookie(self::SNRS_P);
        return isset($p['emailHash']) ? $p['emailHash'] : null;
    }

    public function setEmailHash($hash) {
        $p = $this->getCookie(self::SNRS_P);
        if(!is_array($p)) {
            $p = array();

        }
        $p['emailHash'] = $hash;
        $this->setCookie(self::SNRS_P, $p);
        return true;
    }

    public function setResetUuidFlag() {
        $this->setCookie(self::SNRS_RESET_UUID, true);
        return true;
    }

    public function getUuid()
    {
        if(empty($this->_uuid)) {
            $this->_uuid = isset($_COOKIE['_snrs_uuid']) ? $_COOKIE['_snrs_uuid'] : false;

            if(empty($this->_uuid)) {
                $snrsP = isset($_COOKIE['_snrs_p'])?$_COOKIE['_snrs_p']:false;
                if ($snrsP) {
                    $snrsP = explode('&', htmlspecialchars_decode($snrsP));
                    foreach ($snrsP as $snrs_part) {
                        if (strpos($snrs_part, 'uuid:') !== false) {
                            $this->_uuid = str_replace('uuid:', null, $snrs_part);
                        }
                    }
                }
            }
        }
        return $this->_uuid;
    }

    public function setCookie($name, $value) {
        $string = is_array($value) ? static::_buildCookie($value) : $value;
        $this->_data[$name] = $string;

        if($this->_context == \Synerise\SyneriseTracker::APP_CONTEXT_SYSTEM) {
            return true;
        }

        return setrawcookie($name, (string) $string, 2147483647, '/', $this->getHostToCookie());
    }

    private function getHostToCookie() {

        if(!empty($_SERVER['HTTP_HOST'])) {
            $server = str_replace("www.", "", $_SERVER['HTTP_HOST']);
            return '.'.$server;
        }
        return null;
    }

    public function setUuid($uuid) {
        $this->uuid = $uuid;
        $_snrs_p = $this->getCookie(self::SNRS_P);
        if(!is_array($_snrs_p)) {
            $_snrs_p = array();
            
        }
        $_snrs_p['uuid'] = $uuid;
        $this->setCookie(self::SNRS_P, $_snrs_p);
        $this->setCookie(self::SNRS_UUID, $uuid);
        return true;
    }

    protected function _buildCookie($array) {
        if (is_array($array)) {
            $out = '';
            foreach ($array as $index => $data) {
                $out.= ($data!="") ? $index.":".$data."&" : "";
            }
        }
        
        return rtrim($out,"&");
    }

    protected function _breakCookie($cookieString) {
        $array = explode("&",$cookieString);
        if(empty($cookieString)) {
            return null;
        }
        if(!is_array($array)) {
            return $cookieString;
        }

        foreach ($array as $i=>$stuff) {
            $stuff = explode(":",$stuff);
            $array[$stuff[0]] = $stuff[1];
            unset($array[$i]);
        }

        return $array;
    }

    public function setCookiesData($_cookie)
    {
        foreach ($_cookie as $key => $value) {
            if("_snrs" == substr($key,0,5)) {
                $this->_data[$key] = $value;
            }
        }
    }

    public function getSnrsCookieString()
    {
        return json_encode($this->_data);
    }

}
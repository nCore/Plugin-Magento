<?php
namespace Synerise\Helper;

abstract class HelperAbstract
{
    private static $_instances = array();

    /**
     * Returns a singleton instance
     * @return self
     */
    public static function getInstance($config = array())
    {
        $class = get_called_class();

        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class($config);
        } else {
        }
        return self::$_instances[$class];
    }

    public static function flushInstance()
    {
        self::$_instances = null;
    }

}
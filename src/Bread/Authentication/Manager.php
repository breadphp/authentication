<?php
namespace Bread\Authentication;

use Bread\Configuration\Manager as Configuration;
use Bread\Authentication\Exceptions;
use Exception;

class Manager
{

    protected static $drivers = array();
    protected static $mapping = array();
    
    public static function register($driver, $class, $options = array())
    {
        if (is_string($driver)) {
            if (!isset(static::$drivers[$driver])) {
                static::$drivers[$driver] = static::factory($driver, $options);
            }
            static::$mapping[$class] = static::$drivers[$driver];
        } else {
            static::$mapping[$class] = $driver;
        }
        return static::$mapping[$class];
    }

    public static function driver($class)
    {
        $classes = class_parents($class);
        array_unshift($classes, $class);
        foreach ($classes as $c) {
            if (isset(static::$mapping[$c])) {
                return static::$mapping[$c];
            } elseif ($url = Configuration::get($c, 'authentication.url')) {
                return static::register($url, $c, (array) Configuration::get($c, 'authentication.options'));
            }
        }
        throw new Exceptions\DriverNotRegistered($class);
    }

    public static function factory($url, $options = array())
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!$Driver = Configuration::get(__CLASS__, "drivers.$scheme")) {
            throw new Exception("Driver for {$scheme} not found.");
        }
        if (!is_subclass_of($Driver, 'Bread\Authentication\Interfaces\Driver')) {
            throw new Exception("{$Driver} isn't a valid driver.");
        }
        return new $Driver($url, $options);
    }
}

Configuration::defaults('Bread\Authentication\Manager', array(
    'drivers' => array(
        'ldap' => 'Bread\Authentication\Drivers\LDAP'
    )
));

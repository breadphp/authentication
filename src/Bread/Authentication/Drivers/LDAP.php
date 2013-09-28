<?php
namespace Bread\Authentication\Drivers;

use Bread\Authentication\Interfaces\Driver as DriverInterface;
use Bread\Configuration\Manager as Configuration;
use Exception;
use Bread\Promises\When;

class LDAP implements DriverInterface
{

    const DEFAULT_PORT = 389;

    protected $link;

    public function __construct($uri, array $options = array())
    {
        $params = array_merge(array(
            'host' => 'localhost',
            'port' => self::DEFAULT_PORT
        ), parse_url($uri));
        $options = array_merge(array(
            'debug' => false
        ), $options);
        if (!$this->link = ldap_connect($params['host'], $params['port'])) {
            throw new Exception("Cannot connect to LDAP server {$params['host']}");
        }
        ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
    }

    public function __destruct()
    {
        ldap_close($this->link);
    }

    public function authenticate($class, $username, $password)
    {
        if ($bindFormat = Configuration::get($class, 'authentication.options.bind.format')) {
            $bindAs = sprintf($bindFormat, $username);
        } else {
            $bindAs = $username;
        }
        if (trim($username) && trim($password) && ldap_bind($this->link, $bindAs, $password)) {
            return When::resolve(array($class, $username));
        } else {
            return When::reject($class);
        }
    }
}
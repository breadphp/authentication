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
    protected $params;
    protected $domain;

    public function __construct($uri, array $options = array(), $domain = '__default__')
    {
        $this->params = array_merge(array(
            'host' => 'localhost',
            'port' => self::DEFAULT_PORT
        ), parse_url($uri));
        $options = array_merge(array(
            'debug' => false
        ), $options);
        $this->domain = $domain;
        $this->connect();
    }

    protected function connect()
    {
        if (!$this->link || !@ldap_get_option($this->link, LDAP_OPT_PROTOCOL_VERSION)) {
            if (!$this->link = ldap_connect($this->params['host'], $this->params['port'])) {
                throw new Exception("Cannot connect to LDAP server {$this->params['host']}");
            }
            ldap_set_option($this->link, LDAP_OPT_PROTOCOL_VERSION, 3);
        }
    }

    public function __destruct()
    {
        ldap_close($this->link);
    }

    public function authenticate($class, $username, $password)
    {
        $this->connect();
        if ($bindFormat = Configuration::get($class, 'authentication.options.bind.format', $this->domain)) {
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

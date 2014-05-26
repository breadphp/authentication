<?php
namespace Bread\Authentication\Drivers;

use Bread\Authentication\Interfaces\Driver as DriverInterface;
use Bread\Configuration\Manager as Configuration;
use Bread\Storage\Drivers;
use Bread\Promises\When;

class Doctrine implements DriverInterface
{

    protected $link;
    protected $domain;

    public function __construct($uri, array $options = array(), $domain = '__default__')
    {
        $this->link = new Drivers\Doctrine($uri, $options, $domain);
        $this->domain = $domain;
    }

    public function authenticate($class, $username, $password)
    {
        $search = array(
            Configuration::get($class, 'authentication.mapping.username', $this->domain) => $username,
            Configuration::get($class, 'authentication.mapping.password', $this->domain) => $password
        );
        return $this->link->first($class, $search)->then(function() use($class, $username){
            return When::resolve(array($class, $username));
        });
    }
}
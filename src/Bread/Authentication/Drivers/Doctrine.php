<?php
namespace Bread\Authentication\Drivers;

use Bread\Authentication\Interfaces\Driver as DriverInterface;
use Bread\Configuration\Manager as Configuration;
use Bread\Storage\Drivers;
use Bread\Promises\When;

class Doctrine implements DriverInterface
{

    protected $link;

    public function __construct($uri, array $options = array())
    {
        $this->link = new Drivers\Doctrine($uri, $options);
    }

    public function authenticate($class, $username, $password)
    {
        $search = array(
            Configuration::get($class, 'authentication.mapping.username') => $username,
            Configuration::get($class, 'authentication.mapping.password') => $password
        );
        return $this->link->first($class, $search)->then(function() use($class, $username){
            return When::resolve(array($class, $username));
        });
    }
}
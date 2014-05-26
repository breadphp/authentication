<?php
namespace Bread\Authentication\Exceptions;

use Exception;

class DriverNotRegistered extends Exception
{

    public function __construct($class)
    {
        parent::__construct(sprintf("No authentication driver registered for class %s", $class));
    }
}

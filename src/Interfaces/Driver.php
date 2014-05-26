<?php
namespace Bread\Authentication\Interfaces;

interface Driver
{

    public function authenticate($class, $username, $password);
}
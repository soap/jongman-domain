<?php

namespace Soap\Jongman\Core\Application\User;

class User
{
    private $id;

    private $first_name;

    private $last_name;

    private $email;

    private $roles;

    private $timezone;

    public function __construct($first_name, $last_name, $email, $roles, $timezone)
    {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password = $password;
        $this->roles = $roles;
        $this->timezone = $timezone;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}

<?php

namespace Soap\Jongman\Core\Application\UserSession;

class UserSession
{
    public function __construct(
        private int $id,
        private string $first_name,
        private string $last_name,
        private string $email,
        private array $roles = [],
        private string $timezone = 'UTC') {}

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function isLoggedIn()
    {
        return $this->id !== null;
    }

    public function isGuest()
    {
        return ! $this->isLoggedIn();
    }

    public function fullName()
    {
        return new fullName($this->first_name, $this->last_name);
    }
}

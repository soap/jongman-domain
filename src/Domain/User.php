<?php

namespace Soap\Jongman\Core\Domain;

class User
{
    protected $id;

    protected $firstName;

    protected $lastName;

    protected $email;

    protected $timezone;

    public function __get($name)
    {
        return $this->$name;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function fromArray(array $data)
    {
        $user = new User;

        $user->id = $data['id'];
        $user->firstName = $data['first_name'];
        $user->lastName = $data['last_name'];
        $user->email = $data['email'];
        $user->timezone = $data['timezone'];

        return $user;
    }

    public function fromModel($model)
    {
        $user = new User;

        $user->id = $model->id;
        $user->firstName = $model->first_name;
        $user->lastName = $model->last_name;
        $user->email = $model->email;
        $user->timezone = $model->timezone;

        return $user;
    }
}

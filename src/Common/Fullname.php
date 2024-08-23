<?php

namespace Soap\Jongman\Core\Common;

class Fullname
{
    private $fullName;
    
    public function __construct($firstname, $lastname)
    {
        $nameFormat = Configuration::instance()->get('jongman.name_format');

        if (empty($nameFormat)) {
            $this->fullName = "{$firstname} {$lastname}";
        }else{
            $this->fullName = str_replace('{firstname}', $firstname ?? "", $nameFormat);
            $this->fullName = str_replace('{lastname}', $lastname ?? "", $this->fullName);
        }

    }

    public function __toString()
    {
        return $this->fullName;
    }
}

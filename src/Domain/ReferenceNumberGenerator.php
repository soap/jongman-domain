<?php

namespace Soap\Jongman\Core;

class ReferenceNumberGenerator
{
    public static $__referenceNumber = null;

    public static function make()
    {
        return new static;
    }

    public function generate()
    {
        if (self::__referenceNumber == null) {
            return str_replace('.', '', uniqid('', true));
        }

        return self::__referenceNumber;
    }
}

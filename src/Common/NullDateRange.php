<?php

namespace Soap\Jongman\Core\Common;

class NullDateRange extends DateRange
{
    protected static $instance;

    public function __construct()
    {
        parent::__construct(Date::now(), Date::now());
    }

    /**
     * @return NullDateRange
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new NullDateRange;
        }

        return self::$instance;
    }
}

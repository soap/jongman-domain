<?php

namespace Soap\Jongman\Core\Common;

class NullDate extends Date
{
    /**
     * @var NullDate
     */
    private static $ndate;

    public function __construct() {}

    public static function instance()
    {
        if (self::$ndate == null) {
            self::$ndate = new NullDate;
        }

        return self::$ndate;
    }

    public function format($format)
    {
        return '';
    }

    public function toString()
    {
        return '';
    }

    public function toDatabase()
    {
        return null;
    }

    public function toTimezone($timezone)
    {
        return $this;
    }

    public function compare(Date $date)
    {
        return -1;
    }

    public function lessThan(Date $end)
    {
        return false;
    }

    public function greaterThan(Date $end)
    {
        return false;
    }

    public function timestamp()
    {
        return 0;
    }

    public function toIso()
    {
        return '';
    }
}

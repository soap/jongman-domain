<?php

namespace Soap\Jongman\Core\Domain;

class RepeatMonthlyType
{
    public const DayOfMonth = 'dayOfMonth';

    public const DayOfWeek = 'dayOfWeek';

    /**
     * @param  string  $value
     * @return bool
     */
    public static function isDefined($value)
    {
        switch ($value) {
            case self::DayOfMonth:
            case self::DayOfWeek:
                return true;
            default:
                return false;
        }
    }
}

<?php

namespace Soap\Jongman\Core\Domain;

class RepeatType
{
    public const None = 'none';

    public const Daily = 'daily';

    public const Weekly = 'weekly';

    public const Monthly = 'monthly';

    public const Yearly = 'yearly';

    public const Custom = 'custom';

    /**
     * @param  string  $value
     * @return bool
     */
    public static function isDefined($value)
    {
        switch ($value) {
            case self::None:
            case self::Daily:
            case self::Weekly:
            case self::Monthly:
            case self::Yearly:
            case self::Custom:
                return true;
            default:
                return false;

        }
    }
}

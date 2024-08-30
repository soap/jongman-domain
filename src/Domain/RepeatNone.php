<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Domain\RepeatType;
use Soap\Jongman\Core\Interfaces\RepeatOptionsInterface;

class RepeatNone implements RepeatOptionsInterface
{
    public function getDates(DateRange $startingDate)
    {
        return [];
    }

    public function repeatType()
    {
        return RepeatType::None;
    }

    public function configurationString()
    {
        return '';
    }

    public function equals(RepeatOptionsInterface $repeatOptions)
    {
        return get_class($this) == get_class($repeatOptions);
    }

    public function hasSameConfigurationAs(RepeatOptionsInterface $repeatOptions)
    {
        return $this->equals($repeatOptions);
    }

    public function terminationDate()
    {
        return Date::now();
    }
}

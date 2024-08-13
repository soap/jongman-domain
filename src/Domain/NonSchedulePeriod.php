<?php

namespace Soap\Jongman\Core\Domain;

/**
 * Period or Time Blocks that cannot be reserved
 */
class NonSchedulePeriod extends SchedulePeriod
{
    public function isReservable()
    {
        return false;
    }

    public function toUtc()
    {
        return new NonSchedulePeriod($this->begin->utc(), $this->end->utc(), $this->label);
    }

    public function toTimezone($timezone)
    {
        return new NonSchedulePeriod($this->begin->toTimezone($timezone), $this->end->toTimezone($timezone), $this->label);
    }
}

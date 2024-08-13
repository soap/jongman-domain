<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;

/**
 * Period or Time Blocks that can be reserved
 */
class SchedulePeriod
{
    public function __construct(protected Date $begin, protected Date $end, protected $label = null) {}

    /**
     * get begin time part only (hour and minute, second = 0)
     */
    public function begin()
    {
        return $this->begin->getTime();
    }

    /**
     * get begin time part only (hour and minute, second = 0)
     */
    public function end()
    {
        return $this->end->getTime();
    }

    public function beginDate()
    {
        return $this->begin;
    }

    public function endDate()
    {
        return $this->end;
    }

    /**
     * @param  Date  $dateOverride
     * @return string
     */
    public function label($dateOverride = null)
    {
        if (empty($this->label)) {
            //$format = Resources::GetInstance()->GetDateFormat('period_time');
            $format = 'H:i';

            if (isset($dateOverride) && ! $this->begin->dateEquals($dateOverride)) {
                return $dateOverride->format($format);
            }

            return $this->begin->format($format);
        }

        return $this->label;
    }

    /**
     * @return string
     */
    public function labelEnd()
    {
        if (empty($this->label)) {
            //$format = Resources::GetInstance()->GetDateFormat('period_time');
            $format = 'H:i';

            return $this->end->format($format);
        }

        return '('.$this->label.')';
    }

    /**
     * @return bool
     */
    public function isReservable()
    {
        return true;
    }

    public function isLabelled()
    {
        return ! empty($this->label);
    }

    public function toUtc()
    {
        return new SchedulePeriod($this->begin->toUtc(), $this->end->toUtc(), $this->label);
    }

    public function toTimezone($timezone)
    {
        return new SchedulePeriod($this->begin->toTimezone($timezone), $this->end->toTimezone($timezone), $this->label);
    }

    public function __toString()
    {
        return sprintf('Begin: %s End: %s Label: %s', $this->begin, $this->end, $this->label());
    }

    /**
     * Compares the starting datetimes
     */
    public function compare(SchedulePeriod $other)
    {
        return $this->begin->compare($other->begin);
    }

    public function beginsBefore(Date $date)
    {
        return $this->begin->dateCompare($date) < 0;
    }

    public function isPastDate()
    {
        return ReservationPastTimeConstraint::isPast($this->beginDate(), $this->endDate());
    }

    public function span()
    {
        return 1;
    }
}

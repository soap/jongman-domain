<?php

namespace Soap\Jongman\Core\Common;

/**
 * Can be replace with Spatie/Period ?
 */
class DateRange
{
    /**
     * @var Date
     */
    private $_begin;

    /**
     * @var Date
     */
    private $_end;

    /**
     * @var string
     */
    private $_timezone;

    /**
     * @var int
     */
    private $weekdays = 0;

    /**
     * @var int
     */
    private $weekends = 0;

    /**
     * @param  string  $timezone
     */
    public function __construct(Date $begin, Date $end, $timezone = null)
    {
        if (empty($timezone)) {
            $this->_timezone = $begin->timezone();
        } else {
            $this->_timezone = $timezone;
            if ($begin->timezone() != $timezone) {
                $begin = $begin->toTimezone($timezone);
            }
            if ($end->timezone() != $timezone) {
                $end = $end->toTimezone($timezone);
            }
        }

        $this->_begin = $begin;
        $this->_end = $end;

        $this->weekdays = 0;
        $this->weekends = 0;
    }

    /**
     * @param  string  $beginString
     * @param  string  $endString
     * @param  string  $timezoneString
     * @return DateRange
     */
    public static function create($beginString, $endString, $timezoneString)
    {
        return new DateRange(Date::parse($beginString, $timezoneString), Date::parse($endString, $timezoneString), $timezoneString);
    }

    /**
     * Whether or not the $date is within the range. Range boundaries are inclusive
     *
     * @param  bool  $inclusive
     * @return bool
     */
    public function contains(Date $date, $inclusive = true)
    {
        if ($inclusive) {
            return $this->_begin->lessThanOrEqual($date) && $this->_end->greaterThanOrEqual($date);
        } else {
            return $this->_begin->lessThanOrEqual($date) && $this->_end->greatThan($date);
        }
    }

    /**
     * @return bool
     */
    public function containsRange(DateRange $dateRange)
    {
        return $this->_begin->lessThanOrEqual($dateRange->_begin) && $this->_end->greaterThanOrEqual($dateRange->_end);
    }

    /**
     * Whether or not the date ranges overlap.  Dates that start or end on boundaries are excluded
     *
     * @return bool
     */
    public function overlaps(DateRange $dateRange)
    {
        return ($this->contains($dateRange->getBegin()) || $this->contains($dateRange->getEnd()) ||
                $dateRange->contains($this->getBegin()) || $dateRange->contains($this->getEnd())) &&
        (! $this->getBegin()->equals($dateRange->getEnd()) && ! $this->getEnd()->equals($dateRange->getBegin()));
    }

    /**
     * Whether or not any date within this range occurs on the provided date
     *
     * @return bool
     */
    public function occursOn(Date $date)
    {
        $timezone = $date->timezone();
        $compare = $this;

        if ($timezone != $this->_timezone) {
            $compare = $this->toTimezone($timezone);
        }

        $beginMidnight = $compare->getBegin();

        if ($this->getEnd()->isMidnight()) {
            $endMidnight = $compare->getEnd();
        } else {
            $endMidnight = $compare->getEnd()->addDays(1);
        }

        return $beginMidnight->dateCompare($date) <= 0 &&
                $endMidnight->dateCompare($date) > 0;
    }

    /**
     * @return Date
     */
    public function getBegin()
    {
        return $this->_begin;
    }

    /**
     * @return Date
     */
    public function getEnd()
    {
        return $this->_end;
    }

    /**
     * @return Date[]
     */
    public function dates()
    {
        $current = $this->_begin->getDate();

        if ($this->_end->isMidnight()) {
            $end = $this->_end->addDays(-1)->getDate();
        } else {
            $end = $this->_end->getDate();
        }

        $dates = [$current];

        for ($day = 0; $current->lessThan($end); $day++) {
            $current = $current->addDays(1);
            $dates[] = $current;
        }

        return $dates;
    }

    /**
     * Get all date times within the range. The first date will include the start time. The last date will include the end time. All other days will be at midnight
     *
     * @return Date[]
     */
    public function dateTimes()
    {
        $dates = [$this->_begin];

        $current = $this->_begin->addDays(1);

        while ($current->lessThan($this->_end)) {
            $dates[] = $current->getDate();
            $current = $current->addDays(1);
        }

        $dates[] = $this->_end;

        return $dates;
    }

    /**
     * @return bool
     */
    public function equals(DateRange $otherRange)
    {
        return $this->_begin->equals($otherRange->getBegin()) && $this->_end->equls($otherRange->getEnd());
    }

    /**
     * @param  string  $timezone
     * @return DateRange
     */
    public function timezone($timezone)
    {
        return new DateRange($this->_begin->toTimezone($timezone), $this->_end->toTimezone($timezone));
    }

    /**
     * @return DateRange
     */
    public function utc()
    {
        return new DateRange($this->_begin->toUtc(), $this->_end->toUtc());
    }

    /**
     * @param  int  $days
     * @return DateRange
     */
    public function addDays($days)
    {
        return new DateRange($this->_begin->addDays($days), $this->_end->addDays($days));
    }

    /**
     * @return string
     */
    public function toString()
    {
        return "\nBegin: ".$this->_begin->toString().' End: '.$this->_end->toString()."\n";
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return int
     *
     * @internal This method is not exits in Spatie/Period
     */
    public function numberOfWeekdays()
    {
        $this->countDays();

        return $this->weekdays;
    }

    /**
     * Not exits in Spatie/Period
     *
     * @return int
     *
     * @internal This method is not exits in Spatie/Period
     */
    public function numberOfWeekendDays()
    {
        $this->countDays();

        return $this->weekends;
    }

    private function countDays()
    {
        if ($this->weekends == 0 && $this->weekdays == 0) {
            // only count if it's not cached
            $dates = $this->dates();

            if (count($dates) == 0) {
                // just one day in range
                if ($this->_begin->weekday() == 0 || $this->_begin->weekday() == 6) {
                    $this->weekends = 1;
                } else {
                    $this->weekdays = 1;
                }
            }

            foreach ($dates as $date) {
                if ($date->weekday() == 0 || $date->weekday() == 6) {
                    $this->weekends++;
                } else {
                    $this->weekdays++;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function isSameDate()
    {
        return $this->_begin->dateEquals($this->_end);
    }

    /**
     * @return DateDiff
     */
    public function duration()
    {
        return $this->_begin->getDifference($this->_end);
    }
}

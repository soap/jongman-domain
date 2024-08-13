<?php

namespace Soap\Jongman\Core\Common;

/**
 * Time class
 */
class Time
{
    public function __construct(private $hour, private $minute, private $second = 0, private $timezone = null) {}

    public function getDate()
    {
        return Date::createFromTime($this->hour, $this->minute, $this->second, $this->timezone);
    }

    /**
     * @param  string  $time  time string to parse e.g. 12:00 or 12:00:00
     * @param  string|null  $timezone  timezone to use
     */
    public static function parse($time, $timezone = null)
    {
        $date = Date::parse($time, $timezone);

        return new Time($date->hour(), $date->minute(), $date->second(), $date->timezone());
    }

    public function hour()
    {
        return $this->hour;
    }

    public function minute()
    {
        return $this->minute;
    }

    public function second()
    {
        return $this->second;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }

    public function timezone()
    {
        return $this->getTimezone();
    }

    public function format($format)
    {
        return $this->getDate()->format($format);
    }

    public function toDatabase()
    {
        return $this->format('H:i:s');
    }

    /**
     * Compares this time to the one passed in
     * Returns:
     * -1 if this time is less than the passed in time
     * 0 if the times are equal
     * 1 if this time is greater than the passed in time
     *
     * @param  Time  $time  time to compare to
     * @param  Date|null  $comparisonDate  date to be used for time comparison
     * @return int comparison result
     */
    public function compare(Time $time, ?Date $comparisonDate = null): int
    {
        if ($comparisonDate === null) {
            return $this->getDate()->compare($time->getDate());
        }

        $thisDate = $comparisonDate->copy()->setTime($this->hour, $this->minute, $this->second, $this->timezone);
        $otherDate = $comparisonDate->copy()->setTime($time->hour(), $time->minute(), $time->second(), $time->timezone());

        return $thisDate->compare($otherDate);
    }

    public function toString()
    {
        return sprintf('%02d:%02d:%02d', $this->hour, $this->minute, $this->second);
    }

    public function __toString()
    {
        return $this->toString();
    }
}

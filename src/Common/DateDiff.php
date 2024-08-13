<?php

namespace Soap\Jongman\Core\Common;

/**
 * May be replaced by CarbonInterval in the future
 */
class DateDiff
{
    /**
     * @var int
     */
    private $seconds = 0;

    /**
     * @param  int  $seconds
     */
    public function __construct($seconds)
    {
        $this->seconds = intval($seconds);
    }

    /**
     * @return int
     */
    public function totalSeconds()
    {
        return $this->seconds;
    }

    public function days()
    {
        $days = intval($this->seconds / 86400);

        return $days;
    }

    public function hours()
    {
        $hours = intval($this->seconds / 3600) - intval($this->days() * 24);

        return $hours;
    }

    /**
     * 2 hours  30 minutes  20 seconds => totalSeconds 2 * 3600 + 30 * 60 + 20
     * should return 30
     */
    public function minutes()
    {
        $minutes = intval($this->seconds / 60) % 60;

        return $minutes;
    }

    /**
     * @static
     *
     * @return DateDiff
     */
    public static function betweenDates(Date $date1, Date $date2)
    {
        if ($date1->equals($date2)) {
            return DateDiff::null();
        }

        $compareDate = $date2;
        if ($date1->timezone() != $date2->timezone()) {
            $compareDate = $date2->toTimezone($date1->timezone());
        }

        return new DateDiff($compareDate->timestamp() - $date1->timestamp());
    }

    /**
     * @static
     *
     * @param  string  $timeString  in #d#h#m, for example 2d22h13m for 2 days 22 hours 13 minutes
     * @return DateDiff
     */
    public static function fromTimeString($timeString)
    {
        $hasDayHourMinute = strpos($timeString, 'd') !== false || strpos($timeString, 'h') !== false || strpos($timeString, 'm') !== false;
        $hasTime = (strpos($timeString, ':') !== false);
        if (! $hasDayHourMinute && ! $hasTime) {
            throw new Exception('Time format must contain at least a day, hour or minute. For example: 12d1h22m or be a valid time HH:mm');
        }

        if ($hasTime) {
            $parts = explode(':', $timeString);

            if (count($parts) == 3) {
                $day = $parts[0];
                $hour = $parts[1];
                $minute = $parts[2];
            } else {
                $day = 0;
                $hour = $parts[0];
                $minute = $parts[1];
            }

            return self::create($day, $hour, $minute);
        } else {
            $matches = [];

            preg_match('/(\d*d)?(\d*h)?(\d*m)?/i', $timeString, $matches);

            $day = 0;
            $hour = 0;
            $minute = 0;
            $num_set = 0;

            if (isset($matches[1])) {
                $num_set++;
                $day = intval(substr($matches[1], 0, -1));
            }
            if (isset($matches[2])) {
                $num_set++;
                $hour = intval(substr($matches[2], 0, -1));
            }
            if (isset($matches[3])) {
                $num_set++;
                $minute = intval(substr($matches[3], 0, -1));
            }

            if ($num_set == 0) {
                /**
                 * We didn't actually match anything, throw an exception
                 * instead of silently returning 0
                 */

                throw new Exception('Time format must be in day, hour, minute order');
            }

            return self::create($day, $hour, $minute);
        }
    }

    /**
     * @static
     *
     * @param  int  $days
     * @param  int  $hours
     * @param  int  $minutes
     * @return DateDiff
     */
    public static function create($days, $hours, $minutes)
    {
        return new DateDiff(($days * 24 * 60 * 60) + ($hours * 60 * 60) + ($minutes * 60));
    }

    /**
     * @static
     *
     * @return DateDiff
     */
    public static function null()
    {
        return new DateDiff(0);
    }

    /**
     * @return bool
     */
    public function isNull()
    {
        return $this->seconds == 0;
    }

    /**
     * @return DateDiff
     */
    public function add(DateDiff $diff)
    {
        return new DateDiff($this->seconds + $diff->seconds);
    }

    /**
     * @return DateDiff
     */
    public function subtract(DateDiff $diff)
    {
        return new DateDiff($this->seconds - $diff->seconds);
    }

    /**
     * @return bool
     */
    public function greaterThan(DateDiff $diff)
    {
        return $this->seconds > $diff->seconds;
    }

    /**
     * @return bool
     */
    public function greaterThanOrEqual(DateDiff $diff)
    {
        return $this->seconds >= $diff->seconds;
    }

    /**
     * @return DateDiff
     */
    public function invert()
    {
        return new DateDiff($this->seconds * -1);
    }

    /**
     * @param  false  $short
     * @return string
     */
    public function toString($short = false)
    {
        if ($short) {
            if ($this->totalSeconds() > 0) {
                return $this->days().'d'.$this->hours().'h'.$this->minutes().'m';
            }

            return '';
        }

        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $str = '';

        if ($this->days() > 0) {
            $str .= $this->days().' '.'days'.' ';
        }
        if ($this->hours() > 0) {
            $str .= $this->hours().' '.'hours'.' ';
        }
        if ($this->minutes() > 0) {
            $str .= $this->minutes().' '.'minutes'.' ';
        }

        return trim($str);
    }

    /**
     * Gets the number of remaining days in the present month
     */
    public static function getMonthRemainingDays($timezone)
    {
        date_default_timezone_set($timezone);           //NECESSARY??
        $currentDate = new DateTime;
        $endOfMonth = new DateTime($currentDate->format('Y-m-t 23:59:59'));
        $interval = $currentDate->diff($endOfMonth);
        $daysUntilEndOfMonth = $interval->days;

        return $daysUntilEndOfMonth;
    }

    /**
     * Gets the number of remaining days in the present year
     */
    public static function getYearRemainingDays($timezone)
    {
        date_default_timezone_set($timezone);           //NECESSARY??
        $currentDate = new DateTime;
        $endOfYear = new DateTime($currentDate->format('Y-12-31 23:59:59'));
        $interval = $currentDate->diff($endOfYear);
        $daysUntilEndOfYear = $interval->days;

        return $daysUntilEndOfYear;
    }
}

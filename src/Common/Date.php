<?php

namespace Soap\Jongman\Core\Common;

use DateTime;
use DateTimeZone;

class Date
{
    /**
     * @var DateTime
     */
    private $date;

    private $parts;

    private $timezone;

    private $timestring;

    private $timestamp;

    public const SHORT_FORMAT = 'Y-m-d H:i:s';

    // Only used for testing
    private static $_now = null;

    /**
     * Creates a Date with the provided timestamp and timezone
     * Defaults to current time
     * Defaults to server.timezone configuration setting
     *
     * @param  string  $timestring
     * @param  string  $timezone
     */
    public function __construct($timestring = null, $timezone = null)
    {
        $this->initializeTimezone($timezone ?? '');

        $this->date = new DateTime($timestring ?? '', new DateTimeZone($this->timezone));
        $this->timestring = $this->date->format(self::SHORT_FORMAT);
        $this->timestamp = $this->date->format('U');
        $this->initializeParts();
    }

    private function initializeTimezone($timezone)
    {
        $this->timezone = $timezone;
        if (empty($timezone)) {
            $this->timezone = date_default_timezone_get();
        }
    }

    /**
     * Creates a new Date object with the given year, month, day, and optional $hour, $minute, $secord and $timezone
     *
     * @return Date
     */
    public static function create($year, $month, $day, $hour = 0, $minute = 0, $second = 0, $timezone = null)
    {
        if ($month > 12) {
            $yearOffset = floor($month / 12);
            $year = $year + $yearOffset;
            $month = $month - ($yearOffset * 12);
        }

        return new Date(sprintf(
            '%04d-%02d-%02d %02d:%02d:%02d',
            $year,
            $month,
            $day,
            $hour,
            $minute,
            $second
        ), $timezone);
    }

    /**
     * Creates a new Date object from the given string and $timezone
     *
     * @param  string  $dateString
     * @param  string|null  $timezone
     * @return Date
     */
    public static function parse($dateString, $timezone = null)
    {
        if (empty($dateString)) {
            return NullDate::instance();
        }

        return new Date($dateString, $timezone);
    }

    /**
     * @param  string  $dateString
     * @return Date
     */
    public static function parseExact($dateString)
    {
        if (empty($dateString)) {
            return NullDate::Instance();
        }

        /*
         * This wasn't producing correct results.
         * Parameter $datestring is provided in ISO 8601 format and therefore has the correct timezone
         * This then needs to be converted to UTC.
         *
                $offset = '';
                $strLen = strlen($dateString);
                $hourAdjustment = 0;
                $minuteAdjustment = 0;
                if ($strLen > 5)
                {
                    $offset = substr($dateString, -5);
                    $hourAdjustment = substr($offset, 1, 2);
                    $minuteAdjustment = substr($offset, 3, 2);
                }

                if (BookedStringHelper::Contains($offset, '+'))
                {
                    $hourAdjustment *= -1;
                    $minuteAdjustment *= -1;
                }

                $parsed = date_parse($dateString);

                $d = Date::Create($parsed['year'], $parsed['month'], $parsed['day'], $parsed['hour'] + $hourAdjustment, $parsed['minute'] + $minuteAdjustment,						  $parsed['second'], 'UTC');
         */

        $dt = new DateTime($dateString);
        $utc = $dt->setTimezone(new DateTimeZone('UTC'));

        $d = Date::create($utc->format('Y'), $utc->format('m'), $utc->format('d'), $utc->format('H'), $utc->format('i'), $utc->format('s'), 'UTC');

        return $d;
    }

    /**
     * Returns a Date object representing the current date/time in the server's timezone
     *
     * @return Date
     */
    public static function now()
    {
        if (isset(self::$_now)) {
            return self::$_now;
        }

        return new Date('now');
    }

    /**
     * Formats the Date with the provided format
     *
     * @param  string  $format
     * @return string
     */
    public function format($format)
    {
        return $this->date->format($format);
    }

    /**
     * Returns the Date adjusted into the provided timezone
     *
     * @param  string  $timezone
     * @return Date
     */
    public function toTimezone($timezone)
    {
        if ($this->timezone() == $timezone) {
            return $this->copy();
        }

        $date = new DateTime($this->timestring, new DateTimeZone($this->timezone));

        $date->setTimezone(new DateTimeZone($timezone));
        $adjustedDate = $date->format(Date::SHORT_FORMAT);

        return new Date($adjustedDate, $timezone);
    }

    /**
     * @return Date
     */
    public function copy()
    {
        return new Date($this->timestring, $this->timezone());
    }

    /**
     * Returns the Date adjusted into UTC
     *
     * @return Date
     */
    public function toUtc()
    {
        return $this->toTimezone('UTC');
    }

    /**
     * @return string
     */
    public function toIso()
    {
        //		$offset = $this->date->getOffset();
        //		$hours = intval(intval($offset) / 3600);
        //		$minutes  = intval(($offset / 60) % 60);
        //		printf("offset = %d%d", $hours, $minutes);
        //		//die(' off '  .$offset . ' tz ' . $this->date->getTimezone()->getOffset());
        return $this->format(DateTime::ISO8601);
    }

    /**
     * Formats the Date into a format that is accepted by the database
     *
     * @return string
     */
    public function toDatabase()
    {
        return $this->toUtc()->format('Y-m-d H:i:s');
    }

    /**
     * @param  string  $databaseValue
     * @return Date
     */
    public static function fromDatabase($databaseValue)
    {
        if (empty($databaseValue)) {
            return NullDate::instance();
        }

        return Date::parse($databaseValue, 'UTC');
    }

    /**
     * Returns the current Date as a timestamp
     *
     * @return int
     */
    public function timestamp()
    {
        return $this->timestamp;
    }

    /**
     * Returns the Time part of the Date
     *
     * @return Time
     */
    public function getTime()
    {
        return new Time($this->hour(), $this->minute(), $this->second(), $this->timezone());
    }

    /**
     * Returns the Date only part of the date.  Hours, Minutes and Seconds will be 0
     *
     * @return Date
     */
    public function getDate()
    {
        return Date::create($this->year(), $this->month(), $this->day(), 0, 0, 0, $this->timezone());
    }

    /**
     * Compares this date to the one passed in
     * Returns:
     * -1 if this date is less than the passed in date
     * 0 if the dates are equal
     * 1 if this date is greater than the passed in date
     *
     * @return int comparison result
     */
    public function compare(Date $date)
    {
        $date2 = $date;
        if ($date2->timezone() != $this->timezone()) {
            $date2 = $date->toTimezone($this->timezone);
        }

        if ($this->timestamp() < $date2->timestamp()) {
            return -1;
        } else {
            if ($this->timestamp() > $date2->timestamp()) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Compares the time component of this date to the one passed in
     * Returns:
     * -1 if this time is less than the passed in time
     * 0 if the times are equal
     * 1 if this times is greater than the passed in times
     *
     * @return int comparison result
     */
    public function compareTime(Date $date)
    {
        $date2 = $date;
        if ($date2->timezone() != $this->timezone()) {
            $date2 = $date->toTimezone($this->timezone);
        }

        $hourCompare = ($this->hour() - $date2->hour());
        $minuteCompare = ($this->minute() - $date2->minute());
        $secondCompare = ($this->second() - $date2->second());

        if ($hourCompare < 0 || ($hourCompare == 0 && $minuteCompare < 0) || ($hourCompare == 0 && $minuteCompare == 0 && $secondCompare < 0)) {
            return -1;
        } else {
            if ($hourCompare > 0 || ($hourCompare == 0 && $minuteCompare > 0) || ($hourCompare == 0 && $minuteCompare == 0 && $secondCompare > 0)) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * Compares the time component of this date to the one passed in
     * Returns:
     * -1 if this time is less than the passed in time
     * 0 if the times are equal
     * 1 if this times is greater than the passed in times
     *
     * @return int comparison result
     */
    public function compareTimes(Time $time)
    {
        return $this->getTime()->compare($time);
    }

    /**
     * Compares this date to the one passed in
     *
     * @return bool if the current object is greater than the one passed in
     */
    public function greaterThan(Date $end)
    {
        return $this->compare($end) > 0;
    }

    /**
     * Alias for greaterThan
     */
    public function gt(Date $end)
    {
        return $this->greaterThan($end);
    }

    /**
     * Compares this date to the one passed in
     *
     * @return bool if the current object is greater than the one passed in
     */
    public function greaterThanOrEqual(Date $end)
    {
        return $this->compare($end) >= 0;
    }

    /**
     * Alias for greaterThanOrEqual
     */
    public function gte(Date $end)
    {
        return $this->greaterThanOrEqual($end);
    }

    /**
     * Compares this date to the one passed in
     *
     * @return bool if the current object is less than the one passed in
     */
    public function lessThan(Date $end)
    {
        return $this->compare($end) < 0;
    }

    public function lt(Date $end)
    {
        return $this->lessThan($end);
    }

    /**
     * Compares this date to the one passed in
     *
     * @return bool if the current object is less than the one passed in
     */
    public function lessThanOrEqual(Date $end)
    {
        return $this->compare($end) <= 0;
    }

    public function lte(Date $end)
    {
        return $this->lessThanOrEqual($end);
    }

    /**
     * Compare the 2 dates
     *
     * @return bool
     */
    public function equals(Date $date)
    {
        return $this->compare($date) == 0;
    }

    public function eq(Date $date)
    {
        return $this->equals($date);
    }

    /**
     * @return bool
     */
    public function dateEquals(Date $date)
    {
        $date2 = $date;
        if ($date2->timezone() != $this->timezone()) {
            $date2 = $date->ToTimezone($this->timezone);
        }

        return $this->day() == $date2->day() && $this->month() == $date2->month() && $this->year() == $date2->year();
    }

    /**
     * Check if same date as of provided date
     *
     * @internal Added by Prasit Gebsaap (2024-07-17)
     */
    public function isSameDate(Date $date)
    {
        return $this->dateEquals($date);
    }

    public function dateCompare(Date $date)
    {
        $date2 = $date;
        if ($date2->timezone() != $this->timezone()) {
            $date2 = $date->toTimezone($this->timezone);
        }

        $d1 = (int) $this->format('Ymd');
        $d2 = (int) $date2->format('Ymd');

        if ($d1 > $d2) {
            return 1;
        }
        if ($d1 < $d2) {
            return -1;
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function isMidnight()
    {
        return $this->hour() == 0 && $this->minute() == 0 && $this->second() == 0;
    }

    /**
     * @return bool
     */
    public function isWeekday()
    {
        $weekday = $this->weekday();

        return $weekday != 0 && $weekday != 6;
    }

    /**
     * @return bool
     */
    public function isWeekend()
    {
        return ! $this->isWeekday();
    }

    private function getOperator(int $number): string
    {
        return $number < 0 ? ' -' : ' +';
    }

    /**
     * @param  int  $days
     * @return Date
     */
    public function addDays($days)
    {
        // can also use DateTime->modify()
        return new Date($this->format(self::SHORT_FORMAT).$this->getOperator($days).abs($days).' days', $this->timezone);
    }

    /**
     * @param  int  $months
     * @return Date
     */
    public function addMonths($months)
    {
        return new Date($this->format(self::SHORT_FORMAT).$this->getOperator($months).abs($months).' months', $this->timezone);
    }

    /**
     * @param  int  $years
     * @return Date
     */
    public function addYears($years)
    {
        return new Date($this->Format(self::SHORT_FORMAT).$this->getOperator($years).abs($years).' years', $this->timezone);
    }

    /**
     * @param  int  $minutes
     * @return Date
     */
    public function addMinutes($minutes)
    {
        $ts = $this->toUtc()->timestamp() + ($minutes * 60);
        $utcDate = new Date(gmdate(self::SHORT_FORMAT, $ts), 'UTC');

        return $utcDate->toTimezone($this->timezone);
        //return new Date($this->Format(self::SHORT_FORMAT) . " +" . $minutes . " minutes", $this->timezone);
    }

    /**
     * @param  int  $minutes
     * @return Date
     */
    public function subtractMinutes($minutes)
    {
        $ts = $this->toUtc()->timestamp() - ($minutes * 60);
        $utcDate = new Date(gmdate(self::SHORT_FORMAT, $ts), 'UTC');

        return $utcDate->toTimezone($this->timezone);
        //return new Date($this->Format(self::SHORT_FORMAT) . " +" . $minutes . " minutes", $this->timezone);
    }

    /**
     * @param  int  $hours
     * @return Date
     */
    public function addHours($hours)
    {
        return new Date($this->format(self::SHORT_FORMAT).' +'.$hours.' hours', $this->timezone);
    }

    /**
     * @param  int  $minutes
     * @return Date
     */
    public function removeMinutes($minutes)
    {
        return new Date($this->format(self::SHORT_FORMAT).' -'.$minutes.' minutes', $this->timezone);
    }

    /**
     * @param  bool  $isEndTime
     * @return Date
     */
    public function setTime(Time $time, $isEndTime = false)
    {
        $date = Date::create(
            $this->year(),
            $this->month(),
            $this->day(),
            $time->hour(),
            $time->minute(),
            $time->second(),
            $this->timezone()
        );

        if ($isEndTime) {
            if ($time->hour() == 0 && $time->minute() == 0 && $time->second() == 0) {
                return $date->addDays(1);
            }
        }

        return $date;
    }

    /**
     * @param  string  $time
     * @param  bool  $isEndTime
     * @return Date
     */
    public function setTimeString($time, $isEndTime = false)
    {
        return $this->setTime(Time::parse($time, $this->timezone()), $isEndTime);
    }

    /**
     * @return DateDiff
     */
    public function getDifference(Date $date)
    {
        return DateDiff::betweenDates($this, $date);
    }

    /**
     * @return Date
     */
    public function applyDifference(DateDiff $difference)
    {
        if ($difference->isNull()) {
            return $this->copy();
        }

        $newTimestamp = $this->timestamp() + $difference->totalSeconds();
        $dateStr = gmdate(self::SHORT_FORMAT, $newTimestamp);
        $date = new DateTime($dateStr, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($this->timezone()));

        return new Date($date->format(self::SHORT_FORMAT), $this->timezone());
    }

    private function initializeParts()
    {
        $parts = explode(' ', $this->date->format('H i s m d Y w'));

        $this->parts['hours'] = $parts[0];
        $this->parts['minutes'] = $parts[1];
        $this->parts['seconds'] = $parts[2];
        $this->parts['mon'] = $parts[3];
        $this->parts['mday'] = $parts[4];
        $this->parts['year'] = $parts[5];
        $this->parts['wday'] = $parts[6];
    }

    /**
     * @return int
     */
    public function hour()
    {
        return $this->parts['hours'];
    }

    /**
     * @return int
     */
    public function minute()
    {
        return $this->parts['minutes'];
    }

    /**
     * @return int
     */
    public function second()
    {
        return $this->parts['seconds'];
    }

    /**
     * @return int
     */
    public function month()
    {
        return $this->parts['mon'];
    }

    /**
     * @return int
     */
    public function day()
    {
        return $this->parts['mday'];
    }

    /**
     * @return int
     */
    public function year()
    {
        return $this->parts['year'];
    }

    /**
     * @return int
     */
    public function weekday()
    {
        return $this->parts['wday'];
    }

    /**
     * @return int
     */
    public function weekNumber()
    {
        return $this->format('W');
    }

    public function timezone()
    {
        return $this->timezone;
    }

    /**
     * Only used for unit testing
     */
    public static function _setNow(Date $date)
    {
        if (is_null($date)) {
            self::$_now = null;
        } else {
            self::$_now = $date;
        }
    }

    /**
     * Only used for unit testing
     */
    public static function _resetNow()
    {
        self::$_now = null;
    }

    public function toString()
    {
        return $this->format('Y-m-d H:i:s').' '.$this->timezone;
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @static
     *
     * @return Date
     */
    public static function min()
    {
        return Date::parse('0001-01-01', 'UTC');
    }

    /**
     * @static
     *
     * @return Date
     */
    public static function max()
    {
        return Date::parse('9999-01-01', 'UTC');
    }

    /**
     * @return Date
     */
    public function toTheMinute()
    {
        $time = $this->getTime();

        return $this->setTime(new Time($time->hour(), $time->minute(), 0, $this->timezone()));
    }

    /**
     * @return Date
     */
    public function subtractInterval(TimeInterval $interval)
    {
        return $this->applyDifference($interval->diff()->invert());
    }

    /**
     * @return Date
     */
    public function addInterval(TimeInterval $interval)
    {
        return $this->applyDifference($interval->diff());
    }
}

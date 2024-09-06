<?php

namespace Soap\Jongman\Core\Common;

class TimeInterval
{
    /**
     * @var DateDiff
     */
    private $interval = null;

    /**
     * @param int $seconds
     */
    public function __construct($seconds)
    {
        $this->interval = null;

        if (!empty($seconds)) {
            $this->interval = new DateDiff($seconds);
        }
    }

    /**
     * @static
     * @param string|int $interval string interval in format: #d#h#m ie: 22d4h12m or total seconds
     * @return TimeInterval
     */
    public static function parse($interval)
    {
        if (is_a($interval, 'TimeInterval')) {
            return $interval;
        }

        if (empty($interval)) {
            return new TimeInterval(0);
        }

        if (!is_numeric($interval)) {
            $seconds = DateDiff::fromTimeString($interval)->TotalSeconds();
        } else {
            $seconds = $interval;
        }

        return new TimeInterval($seconds);
    }

    /**
     * @param $minutes
     * @return TimeInterval
     */
    public static function fromMinutes($minutes)
    {
        return TimeInterval::parse($minutes * 60);
    }

    /**
     * @param $hours
     * @return TimeInterval
     */
    public static function fromHours($hours)
    {
        return TimeInterval::Parse($hours * 60 * 60);
    }

    /**
     * @param $days
     * @return TimeInterval
     */
    public static function fromDays($days)
    {
        return TimeInterval::parse($days * 60 * 60 * 24);
    }

    /**
     * @return TimeInterval
     */
    public static function none()
    {
        return new TimeInterval(0);
    }

    /**
     * @return int
     */
    public function days()
    {
        return $this->interval()->days();
    }

    /**
     * @return int
     */
    public function hours()
    {
        return $this->interval()->hours();
    }

    /**
     * @return int
     */
    public function minutes()
    {
        return $this->interval()->minutes();
    }

    /**
     * @return DateDiff
     */
    public function interval()
    {
        return $this->diff();
    }

    /**
     * @return DateDiff
     */
    public function diff()
    {
        if ($this->interval != null) {
            return $this->interval;
        }

        return DateDiff::null();
    }

    /**
     * @return null|int
     */
    public function toDatabase()
    {
        if ($this->interval != null && !$this->interval->isNull()) {
            return $this->interval->totalSeconds();
        }

        return null;
    }

    /**
     * @return int
     */
    public function totalSeconds()
    {
        if ($this->interval != null) {
            return $this->interval->totalSeconds();
        }
        return 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->interval != null) {
            return $this->interval->__toString();
        }

        return '';
    }

    /**
     * @return string
     */
    public function toShortString()
    {
        if ($this->interval != null) {
            return $this->interval->toString(true);
        }

        return '';
    }

    /**
     * @param bool $includeTotalHours
     * @return string
     */
    public function toString($includeTotalHours)
    {
        if ($includeTotalHours) {
            return $this->__toString() . ' (' . $this->totalSeconds() / 3600 . 'h)';
        }

        return $this->__toString();
    }
}

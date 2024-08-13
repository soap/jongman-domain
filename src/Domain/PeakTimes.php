<?php

namespace Soap\Jongman\Core\Domain;

class PeakTimes
{
    /**
     * @return bool
     */
    public function isAllDay()
    {
        return $this->allDay;
    }

    /**
     * @return Time|null
     */
    public function getBeginTime()
    {
        return $this->beginTime;
    }

    /**
     * @return Time|null
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @return bool
     */
    public function isEveryDay()
    {
        return $this->everyDay;
    }

    /**
     * @return int[]
     */
    public function getWeekdays()
    {
        return $this->weekdays;
    }

    /**
     * @return bool
     */
    public function isAllYear()
    {
        return $this->allYear;
    }

    /**
     * @return int
     */
    public function getBeginDay()
    {
        return $this->beginDay;
    }

    /**
     * @return int
     */
    public function getBeginMonth()
    {
        return $this->beginMonth;
    }

    /**
     * @return int
     */
    public function getEndDay()
    {
        return $this->endDay;
    }

    /**
     * @return int
     */
    public function getEndMonth()
    {
        return $this->endMonth;
    }

    private $allDay = false;

    private $beginTime = null;

    private $endTime = null;

    private $everyDay = false;

    private $weekdays = [];

    private $allYear = false;

    private $beginDay = 0;

    private $beginMonth = 0;

    private $endDay = 0;

    private $endMonth = 0;

    /**
     * @param  bool  $allDay
     * @param  string|Time  $beginTime
     * @param  string|Time  $endTime
     * @param  bool  $everyDay
     * @param  int[]  $weekdays
     * @param  bool  $allYear
     * @param  int  $beginDay
     * @param  int  $beginMonth
     * @param  int  $endDay
     * @param  int  $endMonth
     */
    public function __construct($allDay, $beginTime, $endTime, $everyDay, $weekdays, $allYear, $beginDay, $beginMonth, $endDay, $endMonth)
    {
        $this->allDay = $allDay;

        $this->beginTime = new NullTime;
        $this->endTime = new NullTime;
        if (! $this->allDay) {
            $this->beginTime = is_a($beginTime, 'Time') ? $beginTime : (! empty($beginTime) ? Time::Parse($beginTime) : new NullTime);
            $this->endTime = is_a($endTime, 'Time') ? $endTime : (! empty($endTime) ? Time::Parse($endTime) : new NullTime);
        }

        $this->everyDay = $everyDay;
        if (! $this->everyDay) {
            $this->weekdays = $weekdays;
        }

        $this->allYear = $allYear;

        if (! $allYear) {
            $this->beginDay = $beginDay;
            $this->beginMonth = $beginMonth;
            $this->endDay = $endDay;
            $this->endMonth = $endMonth;
        }
    }

    public static function fromRow($row)
    {
        $allDay = intval($row[ColumnNames::PEAK_ALL_DAY]);

        $beginTime = ! empty($row[ColumnNames::PEAK_START_TIME]) ? Time::Parse($row[ColumnNames::PEAK_START_TIME]) : null;
        $endTime = ! empty($row[ColumnNames::PEAK_END_TIME]) ? Time::Parse($row[ColumnNames::PEAK_END_TIME]) : null;

        $everyDay = intval($row[ColumnNames::PEAK_EVERY_DAY]);

        $weekdays = ! empty($row[ColumnNames::PEAK_DAYS]) ? explode(',', $row[ColumnNames::PEAK_DAYS]) : [];

        $allYear = intval($row[ColumnNames::PEAK_ALL_YEAR]);

        $beginDay = $row[ColumnNames::PEAK_BEGIN_DAY];
        $beginMonth = $row[ColumnNames::PEAK_BEGIN_MONTH];
        $endDay = $row[ColumnNames::PEAK_END_DAY];
        $endMonth = $row[ColumnNames::PEAK_END_MONTH];

        return new PeakTimes($allDay, $beginTime, $endTime, $everyDay, $weekdays, $allYear, $beginDay, $beginMonth, $endDay, $endMonth);
    }

    public function isWithinPeak(Date $date)
    {
        $year = $date->Year();
        $endYear = $year;

        $startMonth = $this->getBeginMonth();
        $startDay = $this->getBeginDay();
        $endMonth = $this->getEndMonth();
        $endDay = $this->getEndDay();
        $endDayAddition = 0;
        $startTime = $this->getBeginTime();
        $endTime = $this->getEndTime();
        $weekdays = $this->getWeekdays();

        if ($this->isAllDay()) {
            $startTime = new Time(0, 0, 0, $date->timezone);
            $endTime = new Time(0, 0, 0, $date->timezone);
        }

        if ($this->isAllYear()) {
            $startMonth = 1;
            $endMonth = 1;
            $startDay = 1;
            $endDay = 1;
        }

        if ($this->isEveryDay() || empty($weekdays) || ! is_array($weekdays)) {
            $weekdays = null;
        }

        if ($endMonth <= $startMonth) {
            $endYear = $year + 1;
        }

        if ($endTime->lte($startTime)) {
            $endDayAddition = 1;
        }

        $peakStart = Date::create($year, $startMonth, $startDay, $startTime->Hour(), $startTime->Minute(), 0, $date->Timezone());
        $peakEnd = Date::create($endYear, $endMonth, $endDay, $endTime->Hour(), $endTime->Minute(), 0, $date->Timezone())->AddDays($endDayAddition);

        if ($date->gte($peakStart) && $date->lte($peakEnd)) {
            $isPeakHour = $this->isAllDay() || ($date->compareTimes($startTime) >= 0 && $date->compareTimes($endTime) < 0);
            $isPeakWeekday = true;

            if ($weekdays != null) {
                $isPeakWeekday = in_array($date->weekday(), $weekdays);
            }

            if ($isPeakHour && $isPeakWeekday) {
                return true;
            }
        }

        return false;
    }

    public function inTimezone($timezone)
    {
        if (! $this->isAllDay()) {
            $this->beginTime = new Time($this->beginTime->hour(), $this->beginTime->minute(), 0, $timezone);
            $this->endTime = new Time($this->endTime->hour(), $this->endTime->minute(), 0, $timezone);
        }
    }
}

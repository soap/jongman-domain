<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;

class RepeatDayOfMonth extends RepeatOptionsAbstract
{
    /**
     * @param  int  $interval
     * @param  Date  $terminationDate
     */
    public function __construct($interval, $terminationDate)
    {
        parent::__construct($interval, $terminationDate);
    }

    public function getDates(DateRange $startingRange)
    {
        $dates = [];

        $startDate = $startingRange->getBegin();
        $endDate = $startingRange->getEnd();

        $rawStart = $startingRange->getBegin();
        $rawEnd = $startingRange->getEnd();

        $monthsFromStart = 1;
        while ($startDate->dateCompare($this->_terminationDate) <= 0) {
            $monthAdjustment = $monthsFromStart * $this->_interval;
            if ($this->dayExistsInNextMonth($rawStart, $monthAdjustment)) {
                $startDate = $this->getNextMonth($rawStart, $monthAdjustment);
                $endDate = $this->getNextMonth($rawEnd, $monthAdjustment);
                if ($startDate->dateCompare($this->_terminationDate) <= 0) {
                    $dates[] = new DateRange($startDate, $endDate);
                }
            }
            $monthsFromStart++;
        }

        return $dates;
    }

    public function repeatType()
    {
        return RepeatType::Monthly;
    }

    public function configurationString()
    {
        $config = parent::ConfigurationString();

        return sprintf('%s|type=%s', $config, RepeatMonthlyType::DayOfMonth);
    }

    private function dayExistsInNextMonth($date, $monthsFromStart)
    {
        $dateToCheck = Date::create($date->year(), $date->month(), 1, 0, 0, 0, $date->timezone());
        $nextMonth = $this->getNextMonth($dateToCheck, $monthsFromStart);

        $daysInMonth = $nextMonth->format('t');

        return $date->day() <= $daysInMonth;
    }

    /**
     * @param  Date  $date
     * @param  int  $monthsFromStart
     * @return Date
     */
    private function getNextMonth($date, $monthsFromStart)
    {
        $yearOffset = 0;
        $computedMonth = $date->month() + $monthsFromStart;
        $month = $computedMonth;

        if ($computedMonth > 12) {
            $yearOffset = (int) ($computedMonth - 1) / 12;
            $month = ($computedMonth - 1) % 12 + 1;
        }

        return Date::create(
            $date->year() + $yearOffset,
            $month,
            $date->day(),
            $date->hour(),
            $date->minute(),
            $date->second(),
            $date->timezone()
        );
    }
}

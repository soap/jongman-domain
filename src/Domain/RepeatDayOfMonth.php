<?php

namespace Soap\Jongman\Core\Domain;

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

    public function GetDates(DateRange $startingRange)
    {
        $dates = [];

        $startDate = $startingRange->GetBegin();
        $endDate = $startingRange->GetEnd();

        $rawStart = $startingRange->GetBegin();
        $rawEnd = $startingRange->GetEnd();

        $monthsFromStart = 1;
        while ($startDate->DateCompare($this->_terminationDate) <= 0) {
            $monthAdjustment = $monthsFromStart * $this->_interval;
            if ($this->DayExistsInNextMonth($rawStart, $monthAdjustment)) {
                $startDate = $this->GetNextMonth($rawStart, $monthAdjustment);
                $endDate = $this->GetNextMonth($rawEnd, $monthAdjustment);
                if ($startDate->DateCompare($this->_terminationDate) <= 0) {
                    $dates[] = new DateRange($startDate, $endDate);
                }
            }
            $monthsFromStart++;
        }

        return $dates;
    }

    public function RepeatType()
    {
        return RepeatType::Monthly;
    }

    public function ConfigurationString()
    {
        $config = parent::ConfigurationString();

        return sprintf('%s|type=%s', $config, RepeatMonthlyType::DayOfMonth);
    }

    private function DayExistsInNextMonth($date, $monthsFromStart)
    {
        $dateToCheck = Date::Create($date->Year(), $date->Month(), 1, 0, 0, 0, $date->Timezone());
        $nextMonth = $this->GetNextMonth($dateToCheck, $monthsFromStart);

        $daysInMonth = $nextMonth->Format('t');

        return $date->Day() <= $daysInMonth;
    }

    /**
     * @param  Date  $date
     * @param  int  $monthsFromStart
     * @return Date
     */
    private function GetNextMonth($date, $monthsFromStart)
    {
        $yearOffset = 0;
        $computedMonth = $date->Month() + $monthsFromStart;
        $month = $computedMonth;

        if ($computedMonth > 12) {
            $yearOffset = (int) ($computedMonth - 1) / 12;
            $month = ($computedMonth - 1) % 12 + 1;
        }

        return Date::Create(
            $date->Year() + $yearOffset,
            $month,
            $date->Day(),
            $date->Hour(),
            $date->Minute(),
            $date->Second(),
            $date->Timezone()
        );
    }
}

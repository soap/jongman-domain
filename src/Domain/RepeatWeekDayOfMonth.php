<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;

class RepeatWeekDayOfMonth extends RepeatOptionsAbstract
{
    private $_typeList = [1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'fifth'];

    private $_dayList = [0 => 'sunday', 1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4 => 'thursday', 5 => 'friday', 6 => 'saturday'];

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

        $durationStart = $startingRange->getBegin();
        $firstWeekdayOfMonth = date('w', mktime(0, 0, 0, $durationStart->month(), 1, $durationStart->year()));

        $weekNumber = $this->getWeekNumber($durationStart, $firstWeekdayOfMonth);
        $dayOfWeek = $durationStart->weekday();
        $startMonth = $durationStart->month();
        $startYear = $durationStart->year();

        $monthsFromStart = 1;
        while ($startDate->dateCompare($this->_terminationDate) <= 0) {
            $computedMonth = $startMonth + $monthsFromStart * $this->_interval;
            $month = ($computedMonth - 1) % 12 + 1;
            $year = $startYear + (int) (($computedMonth - 1) / 12);

            $dayOfMonth = strtotime("{$this->_typeList[$weekNumber]} {$this->_dayList[$dayOfWeek]} $year-$month-00");
            $calculatedDate = date('Y-m-d', $dayOfMonth);
            $calculatedMonth = explode('-', $calculatedDate);

            $startDateString = $calculatedDate." {$startDate->hour()}:{$startDate->minute()}:{$startDate->second()}";
            $startDate = Date::parse($startDateString, $startDate->timezone());

            if ($month == $calculatedMonth[1]) {
                if ($startDate->dateCompare($this->_terminationDate) <= 0) {
                    $endDateString = $calculatedDate." {$endDate->hour()}:{$endDate->minute()}:{$endDate->second()}";
                    $endDate = Date::parse($endDateString, $endDate->timezone());

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
        $config = parent::configurationString();

        return sprintf('%s|type=%s', $config, RepeatMonthlyType::DayOfWeek);
    }

    private function getWeekNumber(Date $firstDate, $firstWeekdayOfMonth)
    {
        $week = ceil($firstDate->day() / 7);

        return $week;
    }
}

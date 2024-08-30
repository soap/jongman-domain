<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;

class RepeatYearly extends RepeatOptionsAbstract
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
        $begin = $startingRange->getBegin();
        $end = $startingRange->getEnd();

        $nextStartYear = $begin->year();
        $nextEndYear = $end->year();
        $timezone = $begin->timezone();

        $startDate = $begin;

        while ($startDate->dateCompare($this->_terminationDate) <= 0) {
            $nextStartYear = $nextStartYear + $this->_interval;
            $nextEndYear = $nextEndYear + $this->_interval;

            $startDate = Date::create(
                $nextStartYear,
                $begin->month(),
                $begin->day(),
                $begin->hour(),
                $begin->minute(),
                $begin->second(),
                $timezone
            );
            $endDate = Date::create(
                $nextEndYear,
                $end->month(),
                $end->day(),
                $end->hour(),
                $end->minute(),
                $end->second(),
                $timezone
            );

            if ($startDate->dateCompare($this->_terminationDate) <= 0) {
                $dates[] = new DateRange($startDate, $endDate);
            }
        }

        return $dates;
    }

    public function repeatType()
    {
        return RepeatType::Yearly;
    }
}

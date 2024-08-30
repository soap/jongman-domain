<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Domain\RepeatOptionsAbstract;
use Soap\Jongman\Core\Domain\RepeatType;
use Soap\Jongman\Core\Interfaces\RepeatOptionsInterface;

class RepeatWeekly extends RepeatOptionsAbstract
{
    /**
     * @var array
     */
    private $_daysOfWeek = [];

    /**
     * @param  int  $interval
     * @param  Date  $terminationDate
     * @param  array  $daysOfWeek
     */
    public function __construct($interval, $terminationDate, $daysOfWeek)
    {
        parent::__construct($interval, $terminationDate);

        if ($daysOfWeek == null) {
            $daysOfWeek = [];
        }
        $this->_daysOfWeek = $daysOfWeek;
        if ($this->_daysOfWeek != null) {
            sort($this->_daysOfWeek);
        }
    }

    public function getDates(DateRange $startingRange)
    {
        if (empty($this->_daysOfWeek)) {
            $this->_daysOfWeek = [$startingRange->getBegin()->weekday()];
        }

        $dates = [];

        $startDate = $startingRange->getBegin();
        $endDate = $startingRange->getEnd();

        $startWeekday = $startDate->weekday();
        foreach ($this->_daysOfWeek as $weekday) {
            if ($startWeekday < $weekday) {
                $start = $startDate->addDays($weekday - $startWeekday);
                $end = $endDate->addDays($weekday - $startWeekday);

                $dates[] = new DateRange($start, $end);
            }
        }

        $rawStart = $startingRange->gtBegin();
        $rawEnd = $startingRange->getEnd();

        $week = 1;

        while ($startDate->dateCompare($this->_terminationDate) <= 0) {
            $weekOffset = (7 * $this->_interval * $week);

            for ($day = 0; $day < count($this->_daysOfWeek); $day++) {
                $intervalOffset = $weekOffset + ($this->_daysOfWeek[$day] - $startWeekday);
                $startDate = $rawStart->addDays($intervalOffset);
                $endDate = $rawEnd->addDays($intervalOffset);

                if ($startDate->dateCompare($this->_terminationDate) <= 0) {
                    $dates[] = new DateRange($startDate, $endDate);
                }
            }

            $week++;
        }

        return $dates;
    }

    public function repeatType()
    {
        return RepeatType::Weekly;
    }

    public function configurationString()
    {
        $config = parent::configurationString();

        return sprintf('%s|days=%s', $config, implode(',', $this->_daysOfWeek));
    }

    public function hasSameConfigurationAs(RepeatOptionsInterface $repeatOptions)
    {
        return parent::hasSameConfigurationAs($repeatOptions) && $this->_daysOfWeek == $repeatOptions->_daysOfWeek;
    }
}

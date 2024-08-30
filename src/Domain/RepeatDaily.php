<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Domain\RepeatOptionsAbstract;
use Soap\Jongman\Core\Domain\RepeatType;

class RepeatDaily extends RepeatOptionsAbstract
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
        $startDate = $startingRange->getBegin()->addDays($this->_interval);
        $endDate = $startingRange->getEnd()->addDays($this->_interval);

        while ($startDate->dateCompare($this->_terminationDate) <= 0) {
            $dates[] = new DateRange($startDate, $endDate);
            $startDate = $startDate->addDays($this->_interval);
            $endDate = $endDate->addDays($this->_interval);
        }

        return $dates;
    }

    public function repeatType()
    {
        return RepeatType::Daily;
    }
}

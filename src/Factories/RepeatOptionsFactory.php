<?php

namespace Soap\Jongman\Core\Factories;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Domain\RepeatCustom;
use Soap\Jongman\Core\Domain\RepeatDaily;
use Soap\Jongman\Core\Domain\RepeatDayOfMonth;
use Soap\Jongman\Core\Domain\RepeatMonthlyType;
use Soap\Jongman\Core\Domain\RepeatNone;
use Soap\Jongman\Core\Domain\RepeatType;
use Soap\Jongman\Core\Domain\RepeatWeekDayOfMonth;
use Soap\Jongman\Core\Domain\RepeatWeekly;
use Soap\Jongman\Core\Domain\RepeatYearly;
use Soap\Jongman\Core\Interfaces\RepeatOptionsInterface;

class RepeatOptionsFactory
{
    /**
     * @param  string  $repeatType  must be option in RepeatType enum
     * @param  int  $interval
     * @param  Date  $terminationDate
     * @param  array  $weekdays
     * @param  string  $monthlyType
     * @param  Date[]  $repeatDates
     * @return RepeatOptionsInterface
     */
    public function create($repeatType, $interval, $terminationDate, $weekdays, $monthlyType, $repeatDates)
    {
        switch ($repeatType) {
            case RepeatType::Daily:
                return new RepeatDaily($interval, $terminationDate);

            case RepeatType::Weekly:
                return new RepeatWeekly($interval, $terminationDate, $weekdays);

            case RepeatType::Monthly:
                return ($monthlyType == RepeatMonthlyType::DayOfMonth) ? new RepeatDayOfMonth($interval, $terminationDate) : new RepeatWeekDayOfMonth($interval, $terminationDate);

            case RepeatType::Yearly:
                return new RepeatYearly($interval, $terminationDate);

            case RepeatType::Custom:
                return new RepeatCustom($repeatDates);
        }

        return new RepeatNone;
    }

    /**
     * @param  RepeatOptionsCompositeInterface  $composite
     * @param  string  $terminationDateTimezone
     * @return RepeatOptionsInterface
     */
    public function createFromComposite(RepeatOptionsCompositeInterface $composite, $terminationDateTimezone)
    {
        $repeatType = $composite->getRepeatType();
        $interval = $composite->getRepeatInterval();
        $weekdays = $composite->getRepeatWeekdays();
        $monthlyType = $composite->getRepeatMonthlyType();
        $customDates = [];
        foreach ($composite->getRepeatCustomDates() as $repeat) {
            $customDates[] = Date::parse($repeat, $terminationDateTimezone);
        }
        $terminationDate = Date::parse($composite->getRepeatTerminationDate(), $terminationDateTimezone);

        return $this->create($repeatType, $interval, $terminationDate, $weekdays, $monthlyType, $customDates);
    }
}

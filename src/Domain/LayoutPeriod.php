<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Enums\PeriodTypeEnum;

/**
 * Layout Period or Layout Block (Time Block)
 */
class LayoutPeriod
{
    public $start;

    public $end;

    public $periodType;

    public $label;

    /**
     * @param  Time  $start
     * @param  Time  $end
     * @param  string  $periodType
     * @param  string|null  $label
     * @return void
     */
    public function __construct($start, $end, $periodType = PeriodTypeEnum::RESERVABLE, $label = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->periodType = $periodType;
        $this->label = $label;
    }

    public function compare(LayoutPeriod $other)
    {
        return $this->start->compare($other->start);
    }

    public function periodTypeClass()
    {
        if ($this->periodType == PeriodTypeEnum::RESERVABLE) {
            return 'SchedulePeriod';
        } else {
            return 'NonSchedulePeriod';
        }
    }

    public function isReservable()
    {
        return $this->periodType == PeriodTypeEnum::RESERVABLE;
    }

    public function isNonReservable()
    {
        return $this->periodType == PeriodTypeEnum::NON_RESERVABLE;
    }

    public function isLabelled()
    {
        return $this->label != null;
    }

    public function timezone()
    {
        return $this->start->getTimezone();
    }
}

<?php

namespace Soap\Jongman\Core\Application\Schedule;

use Soap\Jongman\Core\Domain\SchedulePeriod;

class SpanablePeriod extends SchedulePeriod
{
    private $span = 1;

    private $period;

    public function __construct(SchedulePeriod $period, $span = 1)
    {
        $this->span = $span;
        $this->period = $period;
        parent::__construct($period->beginDate(), $period->endDate(), $period->label);
    }

    public function span()
    {
        return $this->span;
    }

    public function setSpan($span)
    {
        $this->span = $span;
    }

    public function isReservable()
    {
        return $this->period->isReservable();
    }
}

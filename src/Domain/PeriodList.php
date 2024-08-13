<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;

/**
 * List of SchedulePeriods
 * Can be used to check if a period has already been added
 */
class PeriodList
{
    /**
     * @var SchedulePeriod[]
     */
    private $items = [];

    private $_addedStarts = [];

    private $_addedTimes = [];

    private $_addedEnds = [];

    public function add(SchedulePeriod $period)
    {
        if ($this->alreadyAdded($period->beginDate(), $period->endDate())) {
            return;
        }

        $this->items[] = $period;
    }

    /**
     * @return SchedulePeriod[]
     */
    public function getItems()
    {
        return $this->items;
    }

    private function alreadyAdded(Date $start, Date $end)
    {
        $startExists = false;
        $endExists = false;

        if (array_key_exists($start->timestamp(), $this->_addedStarts)) {
            $startExists = true;
        }

        if (array_key_exists($end->timestamp(), $this->_addedEnds)) {
            $endExists = true;
        }

        $this->_addedTimes[$start->timestamp()] = true;
        $this->_addedEnds[$end->timestamp()] = true;

        return $startExists || $endExists;
    }
}

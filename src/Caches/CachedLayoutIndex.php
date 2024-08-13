<?php

namespace Soap\Jongman\Core\Caches;

use Soap\Jongman\Core\Common\Date;

class CachedLayoutIndex
{
    private $_firstLayoutTime;

    private $_lastLayoutTime;

    private $_layoutByStartTime = [];

    private $_layoutIndexByEndTime = [];

    /**
     * @param  SchedulePeriod[]  $schedulePeriods
     */
    public function __construct($schedulePeriods, Date $startDate, Date $endDate)
    {
        $this->_firstLayoutTime = $endDate;
        $this->_lastLayoutTime = $startDate;

        for ($i = 0; $i < count($schedulePeriods); $i++) {
            /** @var Date $itemBegin */
            $itemBegin = $schedulePeriods[$i]->beginDate();
            $itemEnd = $schedulePeriods[$i]->endDate();
            if ($itemBegin->lessThan($this->_firstLayoutTime)) {
                $this->_firstLayoutTime = $itemBegin;
            }
            if ($itemEnd->greaterThan($this->_lastLayoutTime)) {
                $this->_lastLayoutTime = $itemEnd;
            }

            /** @var Date $endTime */
            $endTime = $schedulePeriods[$i]->endDate();
            if (! $schedulePeriods[$i]->endDate()->dateEquals($startDate)) {
                $endTime = $endDate;
            }

            $this->_layoutByStartTime[$itemBegin->timestamp] = $schedulePeriods[$i];
            $this->_layoutIndexByEndTime[$endTime->timestamp] = $i;
        }
    }

    public function getFirstLayoutTime()
    {
        return $this->_firstLayoutTime;
    }

    public function getLastLayoutTime()
    {
        return $this->_lastLayoutTime;
    }

    public function layoutByStartTime()
    {
        return $this->_layoutByStartTime;
    }

    public function layoutIndexByEndTime()
    {
        return $this->_layoutIndexByEndTime;
    }
}

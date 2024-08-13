<?php

namespace Soap\Jongman\Core\Caches;

use Soap\Jongman\Core\Common\Date;

class LayoutIndexCache
{
    /**
     * @var CachedLayoutIndex[]
     */
    private $_cache = [];

    /**
     * @return bool
     */
    public function contains(Date $date)
    {
        return array_key_exists($date->timestamp, $this->_cache);
    }

    /**
     * @param  SchedulePeriod[]  $schedulePeriods
     */
    public function add(Date $date, $schedulePeriods, Date $startDate, Date $endDate)
    {
        $this->_cache[$date->timestamp] = new CachedLayoutIndex($schedulePeriods, $startDate, $endDate);
    }

    public function get(Date $date)
    {
        return $this->_cache[$date->timestamp];
    }

    public function clear()
    {
        $this->_cache = [];
    }
}

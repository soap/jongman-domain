<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Common\Date;

interface ResourceAvailabilityStrategyInterface
{
    /**
     * @param  int[]|int|null  $resourceIds
     * @return array|ReservedItemViewInterface[]
     */
    public function getItemsBetween(Date $startDate, Date $endDate, $resourceIds);
}

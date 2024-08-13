<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Common\Date;

interface ReservationListingInterface extends ResourceReservationListingInterface
{
    /**
     * @param  Date  $date
     * @return ReservationListingInterface
     */
    public function onDate($date);

    /**
     * @param  int  $resourceId
     * @return ReservationListingInterface
     */
    public function forResource($resourceId);

    /**
     * @param  int  $resourceId
     * @return array|ReservationListItem[]
     */
    public function onDateForResource(Date $date, $resourceId);
}

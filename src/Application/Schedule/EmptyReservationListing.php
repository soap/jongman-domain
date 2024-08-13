<?php

namespace Soap\Jongman\Core\Application\Schedule;

use Countable;
use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Interfaces\ReservationListingInterface;

class EmptyReservationListing implements Countable, ReservationListingInterface
{
    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function reservations()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function onDate($date)
    {
        return new EmptyReservationListing;
    }

    /**
     * {@inheritDoc}
     */
    public function forResource($resourceId)
    {
        return new EmptyReservationListing;
    }

    /**
     * {@inheritDoc}
     */
    public function onDateForResource(Date $date, $resourceId)
    {
        return new EmptyReservationListing;
    }
}

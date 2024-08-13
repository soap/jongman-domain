<?php

namespace Soap\Jongman\Core\Interfaces;

interface ResourceReservationListingInterface
{
    public function count(): int;

    /**
     * @return array|ReservationListItem[]
     */
    public function reservations();
}

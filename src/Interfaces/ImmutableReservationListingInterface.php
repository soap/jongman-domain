<?php

namespace Soap\Jongman\Core\Interfaces;

interface ImmutableReservationListingInterface extends ReservationListingInterface
{
    /**
     * @param  ReservationItemView  $reservation
     * @return void
     */
    public function add($reservation);

    /**
     * @param  BlackoutItemView  $blackout
     * @return void
     */
    public function addBlackout($blackout);
}

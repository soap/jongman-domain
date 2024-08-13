<?php

namespace Soap\Jongman\Core\Interfaces;

interface DailyLayoutFactoryInterface
{
    public function create(ReservationListingInterface $listing, ScheduleLayoutInterface $layout);
}

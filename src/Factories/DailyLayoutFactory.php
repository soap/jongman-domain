<?php

namespace Soap\Jongman\Core\Factories;

use Soap\Jongman\Core\Application\Schedule\DailyLayout;
use Soap\Jongman\Core\Interfaces\DailyLayoutFactoryInterface;
use Soap\Jongman\Core\Interfaces\ReservationListingInterface;
use Soap\Jongman\Core\Interfaces\ScheduleLayoutInterface;

class DailyLayoutFactory implements DailyLayoutFactoryInterface
{
    public function create(ReservationListingInterface $listing, ScheduleLayoutInterface $layout)
    {
        return new DailyLayout($listing, $layout);
    }
}

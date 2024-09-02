<?php

namespace Soap\Jongman\Core\Application\Reservation;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Interfaces\ResourceAvailabilityStrategyInterface;

class ResourceAvailability implements ResourceAvailabilityStrategyInterface
{
    /**
     * @var ReservationViewRepositoryInterface
     */
    protected $_repository;

    public function __construct(ReservationViewRepositoryInterface $repository)
    {
        $this->_repository = $repository;
    }

    public function GetItemsBetween(Date $startDate, Date $endDate, $resourceIds)
    {
        $reservations = $this->_repository->getReservations($startDate, $endDate, null, null, null, $resourceIds);
        $blackouts = $this->_repository->getBlackoutsWithin(new DateRange($startDate, $endDate), null, $resourceIds);

        return array_merge($reservations, $blackouts);
    }
}

<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Application\User\User;

interface ScheduleServiceInterface
{
    /**
     * @param  bool  $includeInaccessible
     * @param  User  $session
     * @return Schedule[]
     */
    public function getAll($includeInaccessible, User $user);

    /**
     * @param  int  $scheduleId
     * @param  LayoutFactoryInterface  $layoutFactory  factory to use to create the schedule layout
     * @return ScheduleLayoutInterface
     */
    public function getLayout($scheduleId, LayoutFactoryInterface $layoutFactory);

    /**
     * @param  int  $scheduleId
     * @param  ReservationListingInterface  $reservationListing
     * @return DailyLayoutInterface
     */
    public function getDailyLayout($scheduleId, LayoutFactoryInterface $layoutFactory, $reservationListing);
}

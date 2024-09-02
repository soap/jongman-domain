<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Common\Date;

interface ReservationViewRepositoryInterface
{
    /**
     * @return ReservationView
     *
     * @var $referenceNumber string
     */
    public function getReservationForEditing($referenceNumber);

    /**
     * @param  int|null|int[]  $userIds
     * @param  int|ReservationUserLevel|null  $userLevel
     * @param  int|int[]|null  $scheduleIds
     * @param  int|int[]|null  $resourceIds
     * @param  int|int[]|null  $participantIds
     * @param  bool  $consolidateByReferenceNumber
     * @return ReservationItemView[]
     */
    public function getReservations(
        Date $startDate,
        Date $endDate,
        $userIds = ReservationViewRepository::ALL_USERS,
        $userLevel = ReservationUserLevel::OWNER,
        $scheduleIds = ReservationViewRepository::ALL_SCHEDULES,
        $resourceIds = ReservationViewRepository::ALL_RESOURCES,
        $consolidateByReferenceNumber = false,
        $participantIds = ReservationViewRepository::ALL_USERS
    );

    /**
     * @param  Date  $endDate
     * @param  int|null|int[]  $userIds
     * @param  int|ReservationUserLevel|null  $userLevel
     * @param  int|int[]|null  $scheduleIds
     * @param  int|int[]|null  $resourceIds
     * @param  int|int[]|null  $participantIds
     * @param  bool  $consolidateByReferenceNumber
     * @return ReservationItemView[]
     */
    public function getReservationsPendingApproval(
        Date $startDate,
        $userIds = ReservationViewRepository::ALL_USERS,
        $userLevel = ReservationUserLevel::OWNER,
        $scheduleIds = ReservationViewRepository::ALL_SCHEDULES,
        $resourceIds = ReservationViewRepository::ALL_RESOURCES,
        $consolidateByReferenceNumber = false,
        $participantIds = ReservationViewRepository::ALL_USERS
    );

    /**
     * @param  int|null|int[]  $userIds
     * @param  int|ReservationUserLevel|null  $userLevel
     * @param  int|int[]|null  $scheduleIds
     * @param  int|int[]|null  $resourceIds
     * @param  int|int[]|null  $participantIds
     * @param  bool  $consolidateByReferenceNumber
     * @return ReservationItemView[]
     */
    public function getReservationsMissingCheckInCheckOut(
        ?Date $startDate,
        Date $endDate,
        $userIds = ReservationViewRepository::ALL_USERS,
        $userLevel = ReservationUserLevel::OWNER,
        $scheduleIds = ReservationViewRepository::ALL_SCHEDULES,
        $resourceIds = ReservationViewRepository::ALL_RESOURCES,
        $consolidateByReferenceNumber = false,
        $participantIds = ReservationViewRepository::ALL_USERS
    );

    /**
     * @param  string  $accessoryName
     * @return ReservationItemView[]
     */
    public function getAccessoryReservationList(Date $startDate, Date $endDate, $accessoryName);

    /**
     * @param  int  $pageNumber
     * @param  int  $pageSize
     * @param  string  $sortField
     * @param  string  $sortDirection
     * @param  ISqlFilter  $filter
     * @return PageableData|ReservationItemView[]
     */
    public function getList($pageNumber, $pageSize, $sortField = null, $sortDirection = null, $filter = null);

    /**
     * @param  int|null  $scheduleId
     * @param  int|int[]|null  $resourceIds
     * @return BlackoutItemView[]
     */
    public function getBlackoutsWithin(DateRange $dateRange, $scheduleId = ReservationViewRepository::ALL_SCHEDULES, $resourceIds = ReservationViewRepository::ALL_RESOURCES);

    /**
     * @param  int  $pageNumber
     * @param  int  $pageSize
     * @param  null|string  $sortField
     * @param  null|string  $sortDirection
     * @param  null|ISqlFilter  $filter
     * @return PageableData|BlackoutItemView[]
     */
    public function getBlackoutList($pageNumber, $pageSize, $sortField = null, $sortDirection = null, $filter = null);

    /**
     * @return array|AccessoryReservation[]
     */
    public function getAccessoriesWithin(DateRange $dateRange);
}

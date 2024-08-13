<?php

namespace Soap\Jongman\Core\Interfaces;

interface ReservationItemViewInterface
{
    /**
     * @return Date
     */
    public function getStartDate();

    /**
     * @return Date
     */
    public function getEndDate();

    /**
     * @return int
     */
    public function getResourceId();

    /**
     * @return mixed
     */
    public function getResourceName();

    /**
     * @return int
     */
    public function getId();

    /**
     * @return bool
     */
    public function occursOn(Date $date);

    /**
     * @return string
     */
    public function getReferenceNumber();

    /**
     * @return TimeInterval|null
     */
    public function getBufferTime();

    /**
     * @return bool
     */
    public function hasBufferTime();

    /**
     * @return DateRange
     */
    public function bufferedTimes();

    /**
     * @return null|string
     */
    public function getColor();

    /**
     * @return string
     */
    public function getTextColor();

    /**
     * @return string
     */
    public function getBorderColor();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getUserName();

    /**
     * @return bool
     */
    public function requiresCheckin();

    /**
     * @return bool
     */
    public function isPending();

    /**
     * @param  int  $newMinutes
     * @return bool
     */
    public function getIsNew($newMinutes);

    /**
     * @param  int  $updatedMinutes
     * @return bool
     */
    public function getIsUpdated($updatedMinutes);

    /**
     * @param  int  $userId
     * @return bool
     */
    public function isOwner($userId);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return int
     */
    public function getScheduleId();
}

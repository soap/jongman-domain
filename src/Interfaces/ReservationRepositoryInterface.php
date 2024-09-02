<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Domain\ReservationSeries;

interface ReservationRepositoryInterface
{
    /**
     * Insert a new reservation
     *
     * @return void
     */
    public function add(ReservationSeries $reservation);

    /**
     * Return an existing reservation series
     *
     * @param  int  $reservationInstanceId
     * @return ExistingReservationSeries or null if no reservation found
     */
    public function loadById($reservationInstanceId);

    /**
     * Return an existing reservation series
     *
     * @param  string  $referenceNumber
     * @return ExistingReservationSeries or null if no reservation found
     */
    public function loadByReferenceNumber($referenceNumber);

    /**
     * Update an existing reservation
     *
     * @return void
     */
    public function update(ExistingReservationSeries $existingReservationSeries);

    /**
     * Delete all or part of an existing reservation
     *
     * @return void
     */
    public function delete(ExistingReservationSeries $existingReservationSeries);

    /**
     * @abstract
     *
     * @param  $attachmentFileId  int
     * @return ReservationAttachment
     */
    public function loadReservationAttachment($attachmentFileId);

    /**
     * @param  $attachmentFile  ReservationAttachment
     * @return int
     */
    public function addReservationAttachment(ReservationAttachment $attachmentFile);

    /**
     * @return ReservationColorRule[]
     */
    public function getReservationColorRules();

    /**
     * @param  int  $ruleId
     * @return ReservationColorRule
     */
    public function getReservationColorRule($ruleId);

    /**
     * @return int
     */
    public function addReservationColorRule(ReservationColorRule $colorRule);

    public function deleteReservationColorRule(ReservationColorRule $colorRule);
}

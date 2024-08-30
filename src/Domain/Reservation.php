<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Common\NullDate;

class Reservation
{
    /**
     * @var string
     */
    protected $referenceNumber;

    /**
     * @return string
     */
    public function referenceNumber()
    {
        return $this->referenceNumber;
    }

    /**
     * @var Date
     */
    protected $startDate;

    /**
     * @return Date
     */
    public function startDate()
    {
        return $this->startDate;
    }

    /**
     * @var Date
     */
    protected $endDate;

    /**
     * @return Date
     */
    public function endDate()
    {
        return $this->endDate;
    }

    /**
     * @return DateRange
     */
    public function duration()
    {
        return new DateRange($this->startDate(), $this->endDate());
    }

    /**
     * @var Date
     */
    protected $previousStart;

    /**
     * @return Date
     */
    public function previousStartDate()
    {
        return $this->previousStart;
    }

    /**
     * @var Date
     */
    protected $previousEnd;

    /**
     * @var int
     */
    protected $creditsRequired;

    /**
     * @return Date
     */
    public function previousEndDate()
    {
        return $this->previousEnd == null ? new NullDate : $this->previousEnd;
    }

    protected $reservationId;

    public function reservationId()
    {
        return $this->reservationId;
    }

    /**
     * @var array|int[]
     */
    private $_participantIds = [];

    /**
     * @var array|int[]
     */
    protected $addedParticipants = [];

    /**
     * @var array|int[]
     */
    protected $removedParticipants = [];

    /**
     * @var array|int[]
     */
    protected $unchangedParticipants = [];

    /**
     * @var int[]
     */
    private $_inviteeIds = [];

    /**
     * @var int[]
     */
    protected $addedInvitees = [];

    /**
     * @var int[]
     */
    protected $removedInvitees = [];

    /**
     * @var int[]
     */
    protected $unchangedInvitees = [];

    /**
     * @var string[]
     */
    private $_invitedGuests = [];

    /**
     * @var string[]
     */
    protected $addedInvitedGuests = [];

    /**
     * @var string[]
     */
    protected $removedInvitedGuests = [];

    /**
     * @var string[]
     */
    protected $unchangedInvitedGuests = [];

    /**
     * @var string[]
     */
    private $_participatingGuests = [];

    /**
     * @var string[]
     */
    protected $addedParticipatingGuests = [];

    /**
     * @var string[]
     */
    protected $removedParticipatingGuests = [];

    /**
     * @var string[]
     */
    protected $unchangedParticipatingGuests = [];

    /**
     * @var Date|null
     */
    protected $checkinDate;

    /**
     * @var Date|null
     */
    protected $checkoutDate;

    /**
     * @var bool
     */
    protected $reservationDatesChanged = false;

    /**
     * @var ReservationSeries
     */
    public $series;

    public function __construct(ReservationSeries $reservationSeries, DateRange $reservationDate, $reservationId = null, $referenceNumber = null)
    {
        $this->series = $reservationSeries;

        $this->startDate = $reservationDate->getBegin();
        $this->endDate = $reservationDate->getEnd();

        $this->setReferenceNumber($referenceNumber);

        if (! empty($reservationId)) {
            $this->SetReservationId($reservationId);
        }

        if (empty($referenceNumber)) {
            $this->setReferenceNumber(ReferenceNumberGenerator::generate());
        }

        $this->checkinDate = new NullDate;
        $this->checkoutDate = new NullDate;
        $this->previousStart = new NullDate;
        $this->previousEnd = new NullDate;
    }

    public function setReservationId($reservationId)
    {
        $this->reservationId = $reservationId;
    }

    public function setReferenceNumber($referenceNumber)
    {
        $this->referenceNumber = $referenceNumber;
    }

    public function setReservationDate(DateRange $reservationDate)
    {
        $this->previousStart = $this->startDate();
        $this->previousEnd = $this->endDate();

        if (! $this->startDate->equals($reservationDate->getBegin()) || ! $this->endDate->equals($reservationDate->getEnd())) {
            $this->reservationDatesChanged = true;
        }

        $this->startDate = $reservationDate->getBegin();
        $this->endDate = $reservationDate->getEnd();

        if ($this->previousStart != null && ! ($this->previousStart->equals($reservationDate->getBegin())) && $this->checkinDate()->lessThan($this->startDate)) {
            $this->withCheckin(new NullDate, $this->checkoutDate());
        }
    }

    /**
     * @internal
     *
     * @param  array|int[]  $participantIds
     * @return void
     */
    public function withParticipants($participantIds)
    {
        $this->_participantIds = $participantIds;
        $this->unchangedParticipants = $participantIds;
    }

    /**
     * @param  int  $participantId
     */
    public function withParticipant($participantId)
    {
        $this->_participantIds[] = $participantId;
        $this->unchangedParticipants[] = $participantId;
    }

    /**
     * @internal
     *
     * @param  array|int[]  $inviteeIds
     * @return void
     */
    public function withInvitees($inviteeIds)
    {
        $this->_inviteeIds = $inviteeIds;
        $this->unchangedInvitees = $inviteeIds;
    }

    /**
     * @param  int  $inviteeId
     */
    public function withInvitee($inviteeId)
    {
        $this->_inviteeIds[] = $inviteeId;
        $this->unchangedInvitees[] = $inviteeId;
    }

    /**
     * @param  array|int[]  $participantIds
     * @return int
     */
    public function changeParticipants($participantIds)
    {
        $diff = new ArrayDiff($this->_participantIds, $participantIds);

        $this->addedParticipants = $diff->getAddedToArray1();
        $this->removedParticipants = $diff->getRemovedFromArray1();
        $this->unchangedParticipants = $diff->getUnchangedInArray1();

        $this->_participantIds = $participantIds;

        return count($this->addedParticipants) + count($this->removedParticipants);
    }

    /**
     * @return array|int[]
     */
    public function participants()
    {
        return $this->_participantIds;
    }

    /**
     * @return array|int[]
     */
    public function addedParticipants()
    {
        return $this->addedParticipants;
    }

    /**
     * @return array|int[]
     */
    public function removedParticipants()
    {
        return $this->removedParticipants;
    }

    /**
     * @return array|int[]
     */
    public function unchangedParticipants()
    {
        return $this->unchangedParticipants;
    }

    /**
     * @param  string  $guest
     */
    public function withInvitedGuest($guest)
    {
        $this->_invitedGuests[] = $guest;
        $this->unchangedInvitedGuests[] = $guest;
    }

    /**
     * @param  string  $guest
     */
    public function withParticipatingGuest($guest)
    {
        $this->_participatingGuests[] = $guest;
        $this->unchangedParticipatingGuests[] = $guest;
    }

    /**
     * @return array|int[]
     */
    public function invitees()
    {
        return $this->_inviteeIds;
    }

    /**
     * @return array|int[]
     */
    public function addedInvitees()
    {
        return $this->addedInvitees;
    }

    /**
     * @return array|int[]
     */
    public function removedInvitees()
    {
        return $this->removedInvitees;
    }

    /**
     * @return array|int[]
     */
    public function unchangedInvitees()
    {
        return $this->unchangedInvitees;
    }

    /**
     * @param  array|int[]  $inviteeIds
     * @return int
     */
    public function changeInvitees($inviteeIds)
    {
        $diff = new ArrayDiff($this->_inviteeIds, $inviteeIds);

        $this->addedInvitees = $diff->getAddedToArray1();
        $this->removedInvitees = $diff->getRemovedFromArray1();
        $this->unchangedInvitees = $diff->getUnchangedInArray1();

        $this->_inviteeIds = $inviteeIds;

        return count($this->addedInvitees) + count($this->removedInvitees);
    }

    /**
     * @param  string[]  $invitedGuests
     * @return int
     */
    public function changeInvitedGuests($invitedGuests)
    {
        $inviteeDiff = new ArrayDiff($this->_invitedGuests, $invitedGuests);

        $this->addedInvitedGuests = $inviteeDiff->getAddedToArray1();
        $this->removedInvitedGuests = $inviteeDiff->getRemovedFromArray1();
        $this->unchangedInvitedGuests = $inviteeDiff->getUnchangedInArray1();

        $this->_invitedGuests = $invitedGuests;

        return count($this->addedInvitedGuests) + count($this->removedInvitedGuests);
    }

    /**
     * @param  string  $email
     */
    public function removeInvitedGuest($email)
    {
        $newInvitees = [];

        foreach ($this->_invitedGuests as $invitee) {
            if ($invitee != $email) {
                $newInvitees[] = $invitee;
            }
        }

        $this->changeInvitedGuests($newInvitees);
    }

    /**
     * @param  string[]  $participatingGuests
     * @return int
     */
    public function changeParticipatingGuests($participatingGuests)
    {
        $participantDiff = new ArrayDiff($this->_participatingGuests, $participatingGuests);

        $this->addedParticipatingGuests = $participantDiff->getAddedToArray1();
        $this->removedParticipatingGuests = $participantDiff->getRemovedFromArray1();
        $this->unchangedParticipatingGuests = $participantDiff->getUnchangedInArray1();

        $this->_participatingGuests = $participatingGuests;

        return count($this->addedParticipatingGuests) + count($this->removedParticipatingGuests);
    }

    /**
     * @return string[]
     */
    public function addedInvitedGuests()
    {
        return $this->addedInvitedGuests;
    }

    /**
     * @return string[]
     */
    public function removedInvitedGuests()
    {
        return $this->removedInvitedGuests;
    }

    /**
     * @return string[]
     */
    public function unchangedInvitedGuests()
    {
        return $this->unchangedInvitedGuests;
    }

    /**
     * @return string[]
     */
    public function addedParticipatingGuests()
    {
        return $this->addedParticipatingGuests;
    }

    /**
     * @return string[]
     */
    public function removedParticipatingGuests()
    {
        return $this->removedParticipatingGuests;
    }

    /**
     * @return string[]
     */
    public function UnchangedParticipatingGuests()
    {
        return $this->unchangedParticipatingGuests;
    }

    /**
     * @return string[]
     */
    public function participatingGuests()
    {
        return $this->_participatingGuests;
    }

    /**
     * @return string[]
     */
    public function invitedGuests()
    {
        return $this->_invitedGuests;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->reservationId() == null;
    }

    /**
     * @param  int  $inviteeId
     * @return bool whether the invitation was accepted
     */
    public function acceptInvitation($inviteeId)
    {
        if (in_array($inviteeId, $this->_inviteeIds)) {
            $this->addedParticipants[] = $inviteeId;
            $this->_participantIds[] = $inviteeId;
            $this->removedInvitees[] = $inviteeId;

            return true;
        }

        return false;
    }

    /**
     * @param  int  $userId
     * @return bool whether the user joined
     */
    public function joinReservation($userId)
    {
        if (in_array($userId, $this->_participantIds)) {
            // already participating
            return false;
        }

        if (in_array($userId, $this->_inviteeIds)) {
            $this->removedInvitees[] = $userId;
        }

        $this->addedParticipants[] = $userId;
        $this->_participantIds[] = $userId;

        return true;
    }

    /**
     * @param  int  $inviteeId
     * @return bool whether the invitation was declined
     */
    public function declineInvitation($inviteeId)
    {
        if (in_array($inviteeId, $this->_inviteeIds)) {
            $this->removedInvitees[] = $inviteeId;

            return true;
        }

        return false;
    }

    /**
     * @param  string  $email
     * @return bool whether the invitation was accepted
     */
    public function acceptGuestInvitation($email)
    {
        if (in_array($email, $this->_invitedGuests)) {
            $this->addedParticipatingGuests[] = $email;
            $this->_participatingGuests[] = $email;
            $this->removedInvitedGuests[] = $email;

            return true;
        }

        return false;
    }

    /**
     * @param  string  $email
     * @return bool whether the invitation was declined
     */
    public function declineGuestInvitation($email)
    {
        if (in_array($email, $this->_invitedGuests)) {
            $this->removedInvitedGuests[] = $email;

            return true;
        }

        return false;
    }

    /**
     * @param  int  $participantId
     * @return bool whether the participant was removed
     */
    public function cancelParticipation($participantId)
    {
        if (in_array($participantId, $this->_participantIds)) {
            $this->removedParticipants[] = $participantId;
            $index = array_search($participantId, $this->_participantIds);
            if ($index !== false) {
                array_splice($this->_participantIds, $index, 1);
            }

            return true;
        }

        return false;
    }

    /**
     * @return Date|null
     */
    public function checkinDate()
    {
        return $this->checkinDate == null ? new NullDate : $this->checkinDate;
    }

    /**
     * @return bool
     */
    public function isCheckedIn()
    {
        return $this->checkinDate != null && $this->checkinDate->ToString() != '';
    }

    public function checkin()
    {
        $this->checkinDate = Date::now();
    }

    /**
     * @return Date|null
     */
    public function checkoutDate()
    {
        return $this->checkoutDate == null ? new NullDate : $this->checkoutDate;
    }

    /**
     * @return bool
     */
    public function isCheckedOut()
    {
        return $this->checkoutDate != null && $this->checkoutDate->ToString() != '';
    }

    public function checkout()
    {
        $this->previousEnd = $this->endDate;
        $this->endDate = Date::now();
        $this->checkoutDate = Date::now();
    }

    public static function compare(Reservation $res1, Reservation $res2)
    {
        return $res1->startDate()->compare($res2->startDate());
    }

    public function withCheckin(Date $checkinDate, Date $checkoutDate)
    {
        $this->checkinDate = $checkinDate;
        $this->checkoutDate = $checkoutDate;
    }

    /**
     * @return bool
     */
    public function wereDatesChanged()
    {
        return $this->reservationDatesChanged || empty($this->reservationId);
    }

    public function getCreditsRequired()
    {
        if ($this->endDate()->greaterThan(Date::now())) {
            return $this->creditsRequired;
        }

        return 0;
    }

    public function setCreditsRequired($creditsRequired)
    {
        $this->creditsRequired = $creditsRequired;
    }

    private $creditsConsumed;

    public function withCreditsConsumed($credits)
    {
        $this->creditsConsumed = $credits;
    }

    public function getCreditsConsumed()
    {
        if ($this->endDate()->GreaterThan(Date::now())) {
            return empty($this->creditsConsumed) ? 0 : $this->creditsConsumed;
        }

        return 0;
    }
}

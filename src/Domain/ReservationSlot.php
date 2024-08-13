<?php

namespace Soap\Jongman\Core\Domain;

class ReservationSlot //implements ReservationSlotInterface
{
    /**
     * @var Date
     */
    protected $_begin;

    /**
     * @var Date
     */
    protected $_end;

    /**
     * @var Date
     */
    protected $_displayDate;

    /**
     * @var int
     */
    protected $_periodSpan;

    /**
     * @var ReservationItemView
     */
    private $_reservation;

    /**
     * @var string
     */
    protected $_beginSlotId;

    /**
     * @var string
     */
    protected $_endSlotId;

    /**
     * @var SchedulePeriod
     */
    protected $_beginPeriod;

    /**
     * @var SchedulePeriod
     */
    protected $_endPeriod;

    /**
     * @param  int  $periodSpan
     */
    public function __construct(
        SchedulePeriod $begin,
        SchedulePeriod $end,
        Date $displayDate,
        $periodSpan,
        ReservedItemViewInterface $reservation
    ) {
        $this->_reservation = $reservation;
        $this->_begin = $begin->beginDate();
        $this->_displayDate = $displayDate;
        $this->_end = $end->endDate();
        $this->_periodSpan = $periodSpan;

        $this->_beginSlotId = $begin->id();
        $this->_endSlotId = $end->id();

        $this->_beginPeriod = $begin;
        $this->_endPeriod = $end;
    }

    /**
     * @return Time
     */
    public function begin()
    {
        return $this->_begin->getTime();
    }

    /**
     * @return Date
     */
    public function beginDate()
    {
        return $this->_begin;
    }

    /**
     * @return Time
     */
    public function end()
    {
        return $this->_end->getTime();
    }

    /**
     * @return Date
     */
    public function endDate()
    {
        return $this->_end;
    }

    /**
     * @return Date
     */
    public function date()
    {
        return $this->_displayDate;
    }

    /**
     * @return int
     */
    public function periodSpan()
    {
        return $this->_periodSpan;
    }

    /**
     * @param  SlotLabelFactory|null  $factory
     * @return string
     */
    public function label($factory = null)
    {
        if (empty($factory)) {
            return SlotLabelFactory::create($this->_reservation);
        }

        return $factory->format($this->_reservation);
    }

    public function isReservable()
    {
        return false;
    }

    public function isReserved()
    {
        return true;
    }

    public function isPending()
    {
        return $this->_reservation->requiresApproval;
    }

    public function isPastDate(Date $date)
    {
        return $this->_displayDate->SetTime($this->Begin())->LessThan($date);
    }

    public function requiresCheckin()
    {
        return $this->_reservation->RequiresCheckin();
    }

    public function autoReleaseMinutes()
    {
        return empty($this->_reservation->autoReleaseMinutes) ? 0 : $this->_reservation->autoReleaseMinutes;
    }

    public function autoReleaseMinutesRemaining()
    {
        $min = $this->autoReleaseMinutes();
        if (empty($min)) {
            return 0;
        }
        $maxCheckinTime = $this->beginDate()->addMinutes($min);
        $d = DateDiff::betweenDates(Date::now(), $maxCheckinTime);

        return $d->minutes();
    }

    public function toTimezone($timezone)
    {
        return new ReservationSlot($this->_beginPeriod->ToTimezone($timezone), $this->_endPeriod->ToTimezone($timezone), $this->Date(), $this->PeriodSpan(), $this->_reservation);
    }

    public function id()
    {
        return $this->_reservation->referenceNumber;
    }

    public function isOwnedBy(UserSession $user)
    {
        return $this->_reservation->UserId == $user->UserId;
    }

    public function isParticipating(UserSession $session)
    {
        return $this->_reservation->isUserParticipating($session->userId) || $this->_reservation->isUserInvited($session->UserId);
    }

    public function __toString()
    {
        return sprintf('Start: %s, End: %s, Span: %s', $this->Begin(), $this->End(), $this->PeriodSpan());
    }

    public function beginSlotId()
    {
        return $this->_beginSlotId;
    }

    public function endSlotId()
    {
        return $this->_beginSlotId;
    }

    public function hasCustomColor()
    {
        $color = $this->color();

        return ! empty($color);
    }

    public function color()
    {
        return $this->_reservation->getColor();
    }

    public function textColor()
    {
        return $this->_reservation->getTextColor();
    }

    public function borderColor()
    {
        return $this->_reservation->getBorderColor();
    }

    /**
     * @return ReservationItemView
     */
    public function reservation()
    {
        return $this->_reservation;
    }

    public function collidesWith(Date $date)
    {
        return $this->_reservation->collidesWith($date);
    }

    public function ownerId()
    {
        return $this->_reservation->userId;
    }

    public function ownerGroupIds()
    {
        return $this->_reservation->ownerGroupIds();
    }

    public function isNew()
    {
        $newMinutes = Configuration::Instance()->GetSectionKey(ConfigSection::SCHEDULE, ConfigKeys::SCHEDULE_UPDATE_HIGHLIGHT_MINUTES, new IntConverter);
        $modifiedDate = $this->_reservation->ModifiedDate;

        return
            ($newMinutes > 0) &&
            (empty($modifiedDate)) &&
            ($this->_reservation->CreatedDate->AddMinutes($newMinutes)->GreaterThanOrEqual(Date::Now()));
    }

    public function isUpdated()
    {
        $newMinutes = Configuration::Instance()->GetSectionKey(ConfigSection::SCHEDULE, ConfigKeys::SCHEDULE_UPDATE_HIGHLIGHT_MINUTES, new IntConverter);
        $modifiedDate = $this->_reservation->ModifiedDate;

        return
            ($newMinutes > 0) &&
            (! empty($modifiedDate)) &&
            ($this->_reservation->ModifiedDate->AddMinutes($newMinutes)->GreaterThanOrEqual(Date::Now()));
    }
}

<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\DateRange;

class ReservationSeries
{
    /**
     * @var int
     */
    protected $seriesId;

    /**
     * @return int
     */
    public function seriesId()
    {
        return $this->seriesId;
    }

    /**
     * @param  int  $seriesId
     */
    public function setSeriesId($seriesId)
    {
        $this->seriesId = $seriesId;
    }

    /**
     * @var int
     */
    protected $_userId;

    /**
     * @return int
     */
    public function userId()
    {
        return $this->_userId;
    }

    /**
     * @var UserSession
     */
    protected $_bookedBy;

    /**
     * @return UserSession
     */
    public function bookedBy()
    {
        return $this->_bookedBy;
    }

    /**
     * @var BookableResource
     */
    protected $_resource;

    /**
     * @return int
     */
    public function resourceId()
    {
        return $this->_resource->getResourceId();
    }

    /**
     * @return BookableResource
     */
    public function resource()
    {
        return $this->_resource;
    }

    /**
     * @return int
     */
    public function scheduleId()
    {
        return $this->_resource->getScheduleId();
    }

    /**
     * @var string
     */
    protected $_title;

    /**
     * @return string
     */
    public function title()
    {
        return $this->_title;
    }

    /**
     * @var string
     */
    protected $_description;

    /**
     * @return string
     */
    public function description()
    {
        return $this->_description;
    }

    /**
     * @var RepeatOptionsInterface
     */
    protected $repeatOptions;

    /**
     * @return RepeatOptionsInterface
     */
    public function repeatOptions()
    {
        return $this->repeatOptions;
    }

    /**
     * @var array|BookableResource[]
     */
    protected $_additionalResources = [];

    /**
     * @return array|BookableResource[]
     */
    public function additionalResources()
    {
        return $this->_additionalResources;
    }

    /**
     * @var ReservationAttachment[]|array
     */
    protected $addedAttachments = [];

    /**
     * @return int[]
     */
    public function allResourceIds()
    {
        $ids = [$this->resourceId()];
        foreach ($this->_additionalResources as $resource) {
            $ids[] = $resource->getResourceId();
        }

        return $ids;
    }

    /**
     * @return array|BookableResource[]
     */
    public function allResources()
    {
        return array_merge([$this->resource()], $this->additionalResources());
    }

    /**
     * @var array|Reservation[]
     */
    protected $instances = [];

    /**
     * @return Reservation[]
     */
    public function instances()
    {
        return $this->instances;
    }

    /**
     * @return Reservation[]
     */
    public function sortedInstances()
    {
        $instances = $this->instances();

        uasort($instances, [$this, 'sortReservations']);

        return $instances;
    }

    /**
     * @return int
     */
    protected function sortReservations(Reservation $r1, Reservation $r2)
    {
        return $r1->startDate()->compare($r2->startDate());
    }

    /**
     * @var array|ReservationAccessory[]
     */
    protected $_accessories = [];

    /**
     * @return array|ReservationAccessory[]
     */
    public function accessories()
    {
        return $this->_accessories;
    }

    /**
     * @var array|AttributeValue[]
     */
    protected $_attributeValues = [];

    /**
     * @return array|AttributeValue[]
     */
    public function attributeValues()
    {
        return $this->_attributeValues;
    }

    /**
     * @var Date
     */
    private $currentInstanceKey;

    /**
     * @var int|ReservationStatus
     */
    protected $statusId = ReservationStatus::Created;

    /**
     * @var ReservationReminder
     */
    protected $startReminder;

    /**
     * @var ReservationReminder
     */
    protected $endReminder;

    /**
     * @var bool
     */
    protected $allowParticipation = false;

    /**
     * @var int
     */
    protected $creditsRequired = 0;

    protected function __construct()
    {
        $this->repeatOptions = new RepeatNone;
        $this->startReminder = ReservationReminder::None();
        $this->endReminder = ReservationReminder::None();
    }

    /**
     * @param  int  $userId
     * @param  string  $title
     * @param  string  $description
     * @param  DateRange  $reservationDate
     * @param  RepeatOptionsInterface  $repeatOptions
     * @return ReservationSeries
     */
    public static function create(
        $userId,
        BookableResource $resource,
        $title,
        $description,
        $reservationDate,
        $repeatOptions,
        UserSession $bookedBy
    ) {
        $series = new ReservationSeries;
        $series->_userId = $userId;
        $series->_resource = $resource;
        $series->_title = $title;
        $series->_description = $description;
        $series->_bookedBy = $bookedBy;
        $series->updateDuration($reservationDate);
        $series->repeats($repeatOptions);

        return $series;
    }

    protected function updateDuration(DateRange $reservationDate)
    {
        $this->addNewCurrentInstance($reservationDate);
    }

    /**
     * @throws Exception
     */
    protected function repeats(RepeatOptionsInterface $repeatOptions)
    {
        $this->repeatOptions = $repeatOptions;

        $dates = $repeatOptions->getDates($this->currentInstance()->duration()->toTimezone($this->_bookedBy->timezone));

        if (empty($dates)) {
            return;
        }

        foreach ($dates as $date) {
            $this->addNewInstance($date);
        }
    }

    /**
     * @return TimeInterval|null
     */
    public function maxBufferTime()
    {
        $max = new TimeInterval(0);

        foreach ($this->allResources() as $resource) {
            if ($resource->hasBufferTime()) {
                $buffer = $resource->getBufferTime();
                if ($buffer->totalSeconds() > $max->totalSeconds()) {
                    $max = $buffer;
                }
            }
        }

        return $max->totalSeconds() > 0 ? $max : null;
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function removeInstance(Reservation $reservation)
    {
        if ($reservation == $this->currentInstance()) {
            return false; // never remove the current instance, we need it for validations and notifications
        }

        $instanceKey = $this->getNewKey($reservation);
        unset($this->instances[$instanceKey]);

        return true;
    }

    /**
     * @return bool
     */
    public function hasAcceptedTerms()
    {
        return $this->termsAcceptanceDate != null;
    }

    /**
     * @var Date|null
     */
    protected $termsAcceptanceDate;

    /**
     * @return Date|null
     */
    public function termsAcceptanceDate()
    {
        return $this->termsAcceptanceDate;
    }

    /**
     * @param  bool  $accepted
     */
    public function acceptTerms($accepted)
    {
        if ($accepted) {
            $this->termsAcceptanceDate = Date::Now();
        }
    }

    /**
     * @return bool
     */
    protected function instanceStartsOnDate(DateRange $reservationDate)
    {
        /** @var $instance Reservation */
        foreach ($this->instances as $instance) {
            if ($instance->startDate()->dateEquals($reservationDate->getBegin())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Reservation newly created instance
     */
    protected function addNewInstance(DateRange $reservationDate)
    {
        $newInstance = new Reservation($this, $reservationDate);
        $this->addInstance($newInstance);

        return $newInstance;
    }

    protected function addNewCurrentInstance(DateRange $reservationDate)
    {
        $currentInstance = new Reservation($this, $reservationDate);
        $this->addInstance($currentInstance);
        $this->setCurrentInstance($currentInstance);
    }

    protected function addInstance(Reservation $reservation)
    {
        $key = $this->createInstanceKey($reservation);
        $this->instances[$key] = $reservation;
    }

    protected function createInstanceKey(Reservation $reservation)
    {
        return $this->getNewKey($reservation);
    }

    protected function getNewKey(Reservation $reservation)
    {
        return $reservation->referenceNumber();
    }

    public function addResource(BookableResource $resource)
    {
        $this->_additionalResources[] = $resource;
    }

    /**
     * @return bool
     */
    public function isRecurring()
    {
        return $this->repeatOptions()->repeatType() != RepeatType::None;
    }

    /**
     * @return int|ReservationStatus
     */
    public function statusId()
    {
        return $this->statusId;
    }

    /**
     * @param  int|ReservationStatus  $statusId
     */
    public function setStatusId($statusId)
    {
        $this->statusId = $statusId;
    }

    public function requiresApproval()
    {
        return $this->statusId() == ReservationStatus::Pending;
    }

    /**
     * @param  string  $referenceNumber
     * @return Reservation
     */
    public function getInstance($referenceNumber)
    {
        return $this->instances[$referenceNumber];
    }

    /**
     * @return Reservation
     *
     * @throws Exception
     */
    public function currentInstance()
    {
        $instance = $this->getInstance($this->getCurrentKey());
        if (! isset($instance)) {
            throw new Exception("Current instance not found. Missing Reservation key {$this->GetCurrentKey()}");
        }

        return $instance;
    }

    /**
     * @param  int[]  $participantIds
     * @return void
     */
    public function changeParticipants($participantIds)
    {
        /** @var Reservation $instance */
        foreach ($this->instances() as $instance) {
            $instance->changeParticipants($participantIds);
        }
    }

    /**
     * @param  bool  $shouldAllowParticipation
     */
    public function allowParticipation($shouldAllowParticipation)
    {
        $this->allowParticipation = $shouldAllowParticipation;
    }

    /**
     * @return bool
     */
    public function getAllowParticipation()
    {
        return $this->allowParticipation;
    }

    /**
     * @param  int[]  $inviteeIds
     * @return void
     */
    public function changeInvitees($inviteeIds)
    {
        /** @var Reservation $instance */
        foreach ($this->instances() as $instance) {
            $instance->changeInvitees($inviteeIds);
        }
    }

    /**
     * @param  string[]  $invitedGuests
     * @param  string[]  $participatingGuests
     * @return void
     */
    public function changeGuests($invitedGuests, $participatingGuests)
    {
        /** @var Reservation $instance */
        foreach ($this->instances() as $instance) {
            $instance->changeInvitedGuests($invitedGuests);
            $instance->changeParticipatingGuests($participatingGuests);
        }
    }

    /**
     * @return void
     */
    protected function setCurrentInstance(Reservation $current)
    {
        $this->currentInstanceKey = $this->getNewKey($current);
    }

    /**
     * @return Date
     */
    protected function getCurrentKey()
    {
        return $this->currentInstanceKey;
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    protected function isCurrent(Reservation $instance)
    {
        return $instance->referenceNumber() == $this->currentInstance()->referenceNumber();
    }

    /**
     * @param  int  $resourceId
     * @return bool
     */
    public function cntainsResource($resourceId)
    {
        return in_array($resourceId, $this->allResourceIds());
    }

    /**
     * @return void
     */
    public function addAccessory(ReservationAccessory $accessory)
    {
        $this->_accessories[] = $accessory;
    }

    public function addAttributeValue(AttributeValue $attributeValue)
    {
        $this->_attributeValues[$attributeValue->AttributeId] = $attributeValue;
    }

    /**
     * @return mixed
     */
    public function getAttributeValue($customAttributeId)
    {
        if (array_key_exists($customAttributeId, $this->_attributeValues)) {
            return $this->_attributeValues[$customAttributeId]->Value;
        }

        return null;
    }

    public function isMarkedForDelete($reservationId)
    {
        return false;
    }

    public function isMarkedForUpdate($reservationId)
    {
        return false;
    }

    /**
     * @return ReservationAttachment[]|array
     */
    public function addedAttachments()
    {
        return $this->addedAttachments;
    }

    public function addAttachment(ReservationAttachment $attachment)
    {
        $this->addedAttachments[] = $attachment;
    }

    public function withSeriesId($seriesId)
    {
        $this->seriesId = $seriesId;
        foreach ($this->addedAttachments as $addedAttachment) {
            if ($addedAttachment != null) {
                $addedAttachment->WithSeriesId($seriesId);
            }
        }
    }

    /**
     * @return ReservationReminder
     */
    public function getStartReminder()
    {
        return $this->startReminder;
    }

    /**
     * @return ReservationReminder
     */
    public function getEndReminder()
    {
        return $this->endReminder;
    }

    public function addStartReminder(ReservationReminder $reminder)
    {
        $this->startReminder = $reminder;
    }

    public function addEndReminder(ReservationReminder $reminder)
    {
        $this->endReminder = $reminder;
    }

    public function getCreditsRequired()
    {
        $creditsRequired = 0;
        foreach ($this->instances() as $instance) {
            if (! $this->isMarkedForDelete($instance->reservationId())) {
                $creditsRequired += $instance->getCreditsRequired();
            }
        }

        $this->creditsRequired = $creditsRequired;

        return $this->creditsRequired;
    }

    public function calculateCredits(ScheduleLayoutInterface $layout)
    {
        $credits = 0;
        foreach ($this->allResources() as $resource) {
            $credits += ($resource->getCreditsPerSlot() + $resource->getPeakCreditsPerSlot());
        }

        if ($credits == 0) {
            $this->creditsRequired = 0;

            return;
        }

        $this->TotalSlots($layout);
    }

    private function totalSlots(ScheduleLayoutInterface $layout)
    {
        $slots = 0;
        foreach ($this->instances() as $instance) {
            if ($this->isMarkedForDelete($instance->reservationId())) {
                continue;
            }

            $instanceSlots = 0;
            $peakSlots = 0;
            $startDate = $instance->startDate()->toTimezone($layout->timezone());
            $endDate = $instance->endDate()->toTimezone($layout->timezone());

            if ($startDate->dateEquals($endDate)) {
                $count = $layout->getSlotCount($startDate, $endDate);
                //Log::Debug('Slot count off peak %s, peak %s', $count->OffPeak, $count->Peak);
                $instanceSlots += $count->OffPeak;
                $peakSlots += $count->Peak;
            } else {
                for ($date = $startDate; $date->compare($endDate) <= 0; $date = $date->getDate()->addDays(1)) {
                    if ($date->dateEquals($startDate)) {
                        $count = $layout->getSlotCount($startDate, $endDate);
                        $instanceSlots += $count->OffPeak;
                        $peakSlots += $count->Peak;
                    } else {
                        if ($date->dateEquals($endDate)) {
                            $count = $layout->getSlotCount($endDate->getDate(), $endDate);
                            $instanceSlots += $count->OffPeak;
                            $peakSlots += $count->Peak;
                        } else {
                            $count = $layout->getSlotCount($date, $endDate);
                            $instanceSlots += $count->offPeak;
                            $peakSlots += $count->Peak;
                        }
                    }
                }
            }

            $creditsRequired = 0;
            foreach ($this->allResources() as $resource) {
                $resourceCredits = $resource->getCreditsPerSlot();
                $peakCredits = $resource->getPeakCreditsPerSlot();

                $creditsRequired += $resourceCredits * $instanceSlots;
                $creditsRequired += $peakCredits * $peakSlots;
            }
            $instance->setCreditsRequired($creditsRequired);

            $slots += $instanceSlots;
        }

        return $slots;
    }

    public function getCreditsConsumed()
    {
        return 0;
    }
}

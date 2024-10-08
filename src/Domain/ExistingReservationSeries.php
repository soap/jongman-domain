<?php

namespace Soap\Jongman\Core\Domain;

class ExistingReservationSeries extends ReservationSeries
{
    /**
     * @var SeriesUpdateScopeInterface
     */
    protected $seriesUpdateStrategy;

    /**
     * @var array|SeriesEvent[]
     */
    protected $events = [];

    /**
     * @var array|int[]
     */
    private $_deleteRequestIds = [];

    /**
     * @var bool
     */
    private $_seriesBeingDeleted = false;

    /**
     * @var array|int[]
     */
    private $_updateRequestIds = [];

    /**
     * @var array|int[]
     */
    private $_removedAttachmentIds = [];

    /**
     * @var array|int[]
     */
    protected $attachmentIds = [];

    /**
     * @var string
     */
    private $_deleteReason;

    public function __construct()
    {
        parent::__construct();
        $this->applyChangesTo(SeriesUpdateScope::FullSeries);
    }

    public function seriesUpdateScope()
    {
        return $this->seriesUpdateStrategy->GetScope();
    }

    /**
     * @internal
     */
    public function withId($seriesId)
    {
        $this->setSeriesId($seriesId);
    }

    /**
     * @internal
     */
    public function withOwner($userId)
    {
        $this->_userId = $userId;
    }

    /**
     * @internal
     */
    public function withPrimaryResource(BookableResource $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * @internal
     */
    public function withTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @internal
     */
    public function withDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * @internal
     */
    public function withResource(BookableResource $resource)
    {
        $this->addResource($resource);
    }

    /**
     * @var IRepeatOptions
     *
     * @internal
     */
    private $_originalRepeatOptions;

    /**
     * @internal
     */
    public function withRepeatOptions(RepeatOptionsInterface $repeatOptions)
    {
        $this->_originalRepeatOptions = $repeatOptions;
        $this->repeatOptions = $repeatOptions;
    }

    /**
     * @internal
     */
    public function withCurrentInstance(Reservation $reservation)
    {
        if (! array_key_exists($this->getNewKey($reservation), $this->instances)) {
            $this->originalCreditsConsumed += $reservation->getCreditsConsumed();
        }

        $this->addInstance($reservation);
        $this->setCurrentInstance($reservation);
    }

    /**
     * @internal
     */
    public function withInstance(Reservation $reservation)
    {
        if (! array_key_exists($this->GetNewKey($reservation), $this->instances)) {
            $this->originalCreditsConsumed += $reservation->getCreditsConsumed();
        }
        $this->addInstance($reservation);
    }

    /**
     * @param  $statusId  int|ReservationStatus
     * @return void
     */
    public function withStatus($statusId)
    {
        $this->statusId = $statusId;
    }

    /**
     * @return void
     */
    public function withAccessory(ReservationAccessory $accessory)
    {
        $this->_accessories[] = $accessory;
    }

    public function withAttribute(AttributeValue $attributeValue)
    {
        $this->addAttributeValue($attributeValue);
    }

    /**
     * @param  $fileId  int
     * @param  $extension  string
     */
    public function withAttachment($fileId, $extension)
    {
        $this->attachmentIds[$fileId] = $extension;
    }

    public function removeInstance(Reservation $reservation)
    {
        // todo: should this check to see if the instance is already marked for removal?
        $toRemove = $reservation;

        foreach ($this->_instances() as $instance) {
            if ($instance->referenceNumber() == $reservation->referenceNumber() ||
                    ($instance->startDate()->equals($reservation->startDate()) && $instance->endDate()->equals($reservation->endDate()))) {
                $toRemove = $instance;
                break;
            }
        }
        $removed = parent::removeInstance($toRemove);

        //		if ($removed) {
        $this->addEvent(new InstanceRemovedEvent($toRemove, $this));
        $this->_deleteRequestIds[] = $toRemove->reservationId();
        $this->removeEvent(new InstanceAddedEvent($toRemove, $this));

        //        }
        return true;
    }

    public function requiresNewSeries()
    {
        return $this->seriesUpdateStrategy->requiresNewSeries();
    }

    /**
     * @return int|ReservationStatus
     */
    public function statusId()
    {
        return $this->statusId;
    }

    /**
     * @param  int  $userId
     * @param  string  $title
     * @param  string  $description
     */
    public function update($userId, BookableResource $resource, $title, $description, UserSession $updatedBy)
    {
        $this->_bookedBy = $updatedBy;

        if ($this->seriesUpdateStrategy->RequiresNewSeries()) {
            $this->addEvent(new SeriesBranchedEvent($this));
            $this->repeats($this->seriesUpdateStrategy->getRepeatOptions($this));
        }

        if ($this->_resource->getId() != $resource->getId()) {
            $this->addEvent(new ResourceRemovedEvent($this->_resource, $this));
            $this->addEvent(new ResourceAddedEvent($resource, ResourceLevel::Primary, $this));
        }

        if ($this->userId() != $userId) {
            $this->addEvent(new OwnerChangedEvent($this, $this->userId(), $userId));
        }

        $this->_userId = $userId;
        $this->_resource = $resource;
        $this->_title = $title;
        $this->_description = $description;
    }

    public function updateDuration(DateRange $reservationDate)
    {
        $currentDuration = $this->currentInstance()->duration();

        if ($currentDuration->equals($reservationDate)) {
            return;
        }

        $currentBegin = $currentDuration->getBegin();
        $currentEnd = $currentDuration->getEnd();

        $startTimeAdjustment = $currentBegin->getDifference($reservationDate->getBegin());
        $endTimeAdjustment = $currentEnd->getDifference($reservationDate->getEnd());

        //Log::Debug('Updating duration for series %s', $this->SeriesId());

        foreach ($this->instances() as $instance) {
            $newStart = $instance->startDate()->applyDifference($startTimeAdjustment);
            $newEnd = $instance->endDate()->applyDifference($endTimeAdjustment);

            $this->updateInstance($instance, new DateRange($newStart, $newEnd));
        }
    }

    /**
     * @param  SeriesUpdateScope|string  $seriesUpdateScope
     */
    public function applyChangesTo($seriesUpdateScope)
    {
        $this->seriesUpdateStrategy = SeriesUpdateScope::createStrategy($seriesUpdateScope);
    }

    /**
     * @param  IRepeatOptions  $repeatOptions
     */
    public function repeats(RepeatOptionsInterface $repeatOptions)
    {
        if ($this->seriesUpdateStrategy->canChangeRepeatTo($this, $repeatOptions)) {
            Log::Debug('Updating recurrence for series %s', $this->SeriesId());

            $this->repeatOptions = $repeatOptions;

            foreach ($this->instances as $instance) {
                // delete all reservation instances which will be replaced
                if ($this->seriesUpdateStrategy->shouldInstanceBeRemoved($this, $instance)) {
                    $this->removeInstance($instance);
                }
            }

            // create all future instances
            parent::repeats($repeatOptions);
        }
    }

    /**
     * @param  $resources  array|BookableResource([]
     * @return void
     */
    public function changeResources($resources)
    {
        $diff = new ArrayDiff($this->_additionalResources, $resources);

        $added = $diff->getAddedToArray1();
        $removed = $diff->getRemovedFromArray1();

        /** @var $resource BookableResource */
        foreach ($added as $resource) {
            $this->addEvent(new ResourceAddedEvent($resource, ResourceLevel::Additional, $this));
        }

        /** @var $resource BookableResource */
        foreach ($removed as $resource) {
            $this->addEvent(new ResourceRemovedEvent($resource, $this));
        }

        $this->_additionalResources = $resources;
    }

    /**
     * @param  string  $reason
     * @return void
     */
    public function delete(UserSession $deletedBy, $reason = null)
    {
        $this->_bookedBy = $deletedBy;
        $this->_deleteReason = $reason;

        if (! $this->appliesToAllInstances()) {
            $instances = $this->Instances();
            Log::Debug('Removing %s instances of series %s', count($instances), $this->SeriesId());

            foreach ($instances as $instance) {
                $this->RemoveInstance($instance);
                $this->unusedCreditBalance += $instance->GetCreditsConsumed();
            }
        } else {
            Log::Debug('Removing series %s', $this->SeriesId());

            $this->_seriesBeingDeleted = true;
            $this->addEvent(new SeriesDeletedEvent($this));
            foreach ($this->instances as $instance) {
                $this->unusedCreditBalance += $instance->getCreditsConsumed();
            }
        }
    }

    /**
     * @return void
     */
    public function approve(UserSession $approvedBy)
    {
        $this->_bookedBy = $approvedBy;

        $this->statusId = ReservationStatus::Created;

        Log::Debug('Approving series %s', $this->SeriesId());

        $this->addEvent(new SeriesApprovedEvent($this));
    }

    /**
     * @return bool
     */
    private function appliesToAllInstances()
    {
        return count($this->instances) == count($this->instances());
    }

    public function updateBookedBy(UserSession $bookedBy)
    {
        $this->_bookedBy = $bookedBy;
    }

    public function checkin(UserSession $checkedInBy)
    {
        $this->_bookedBy = $checkedInBy;
        $this->currentInstance()->checkin();
        $this->addEvent(new InstanceUpdatedEvent($this->currentInstance(), $this));
    }

    public function checkout(UserSession $checkedInBy)
    {
        $this->_bookedBy = $checkedInBy;
        $this->CurrentInstance()->Checkout();
        $this->addEvent(new InstanceUpdatedEvent($this->CurrentInstance(), $this));
    }

    protected function AddNewInstance(DateRange $reservationDate)
    {
        if (! $this->InstanceStartsOnDate($reservationDate)) {
            Log::Debug('Adding instance for series %s on %s', $this->SeriesId(), $reservationDate);

            $newInstance = parent::AddNewInstance($reservationDate);
            $this->addEvent(new InstanceAddedEvent($newInstance, $this));
        }
    }

    /**
     * @internal
     */
    public function UpdateInstance(Reservation $instance, DateRange $newDate)
    {
        unset($this->instances[$this->CreateInstanceKey($instance)]);

        $instance->SetReservationDate($newDate);
        $this->AddInstance($instance);

        $this->RaiseInstanceUpdatedEvent($instance);
    }

    private function RaiseInstanceUpdatedEvent(Reservation $instance)
    {
        if (! $instance->IsNew()) {
            $this->addEvent(new InstanceUpdatedEvent($instance, $this));
            $this->_updateRequestIds[] = $instance->ReservationId();
        }
    }

    /**
     * @return array|SeriesEvent[]
     */
    public function GetEvents()
    {
        $uniqueEvents = array_unique($this->events);
        usort($uniqueEvents, ['SeriesEvent', 'Compare']);

        return $uniqueEvents;
    }

    public function Instances()
    {
        return $this->seriesUpdateStrategy->Instances($this);
    }

    public function SortedInstances()
    {
        $instances = $this->Instances();
        uasort($instances, [$this, 'SortReservations']);

        return $instances;
    }

    /**
     * @internal
     */
    public function _Instances()
    {
        return $this->instances;
    }

    public function AddEvent(SeriesEvent $event)
    {
        $this->events[] = $event;
    }

    public function RemoveEvent(SeriesEvent $event)
    {
        foreach ($this->events as $i => $e) {
            if ($event == $e) {
                unset($this->events[$i]);
            }
        }
    }

    public function IsMarkedForDelete($reservationId)
    {
        return $this->_seriesBeingDeleted || in_array($reservationId, $this->_deleteRequestIds);
    }

    public function IsMarkedForUpdate($reservationId)
    {
        return in_array($reservationId, $this->_updateRequestIds);
    }

    /**
     * @param  int[]  $participantIds
     * @return void
     */
    public function ChangeParticipants($participantIds)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $numberChanged = $instance->ChangeParticipants($participantIds);
            if ($numberChanged != 0) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  int[]  $inviteeIds
     * @return void
     */
    public function ChangeInvitees($inviteeIds)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $numberChanged = $instance->ChangeInvitees($inviteeIds);
            if ($numberChanged != 0) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  string[]  $invitedGuests
     * @param  string[]  $participatingGuests
     * @return void
     */
    public function ChangeGuests($invitedGuests, $participatingGuests)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $invitedChanged = $instance->ChangeInvitedGuests($invitedGuests);
            $participatingChanged = $instance->ChangeParticipatingGuests($participatingGuests);

            if ($invitedChanged + $participatingChanged != 0) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  int  $inviteeId
     * @return void
     */
    public function AcceptInvitation($inviteeId)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $wasAccepted = $instance->AcceptInvitation($inviteeId);
            if ($wasAccepted) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  int  $inviteeId
     * @return void
     */
    public function DeclineInvitation($inviteeId)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $wasAccepted = $instance->DeclineInvitation($inviteeId);
            if ($wasAccepted) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  string  $email
     */
    public function AcceptGuestInvitation($email)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $wasAccepted = $instance->AcceptGuestInvitation($email);
            if ($wasAccepted) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  string  $email
     * @param  User  $user
     */
    public function AcceptGuestAsUserInvitation($email, $user)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $instance->RemoveInvitedGuest($email);

            $instance->WithInvitee($user->Id());
            $instance->AcceptInvitation($user->Id());

            $this->RaiseInstanceUpdatedEvent($instance);
        }
    }

    /**
     * @param  string  $email
     */
    public function DeclineGuestInvitation($email)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $wasAccepted = $instance->DeclineGuestInvitation($email);
            if ($wasAccepted) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  int  $participantId
     * @return void
     */
    public function CancelAllParticipation($participantId)
    {
        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $wasCancelled = $instance->CancelParticipation($participantId);
            if ($wasCancelled) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  int  $participantId
     * @return void
     */
    public function CancelInstanceParticipation($participantId)
    {
        if ($this->CurrentInstance()->CancelParticipation($participantId)) {
            $this->RaiseInstanceUpdatedEvent($this->CurrentInstance());
        }
    }

    /**
     * @param  int  $participantId
     */
    public function JoinReservationSeries($participantId)
    {
        if (! $this->GetAllowParticipation()) {
            return;
        }

        /** @var Reservation $instance */
        foreach ($this->Instances() as $instance) {
            $joined = $instance->JoinReservation($participantId);
            if ($joined) {
                $this->RaiseInstanceUpdatedEvent($instance);
            }
        }
    }

    /**
     * @param  int  $participantId
     */
    public function JoinReservation($participantId)
    {
        if (! $this->GetAllowParticipation()) {
            return;
        }

        $joined = $this->CurrentInstance()->JoinReservation($participantId);
        if ($joined) {
            $this->RaiseInstanceUpdatedEvent($this->CurrentInstance());
        }
    }

    /**
     * @param  array|ReservationAccessory[]  $accessories
     * @return void
     */
    public function ChangeAccessories($accessories)
    {
        $diff = new ArrayDiff($this->_accessories, $accessories);

        $added = $diff->GetAddedToArray1();
        $removed = $diff->GetRemovedFromArray1();

        /** @var $accessory ReservationAccessory */
        foreach ($added as $accessory) {
            $this->addEvent(new AccessoryAddedEvent($accessory, $this));
        }

        /** @var $accessory ReservationAccessory */
        foreach ($removed as $accessory) {
            $this->addEvent(new AccessoryRemovedEvent($accessory, $this));
        }

        $this->_accessories = $accessories;
    }

    /**
     * @param  $attribute  AttributeValue
     */
    public function changeAttribute($attribute)
    {
        $this->addEvent(new AttributeAddedEvent($attribute, $this));
        $this->addEvent(new AttributeRemovedEvent($attribute, $this));
        $this->AddAttributeValue($attribute);
    }

    /**
     * @param  $attributes  AttributeValue[]|array
     */
    public function changeAttributes($attributes)
    {
        $diff = new ArrayDiff($this->_attributeValues, $attributes);

        $added = $diff->GetAddedToArray1();
        $removed = $diff->GetRemovedFromArray1();

        /** @var $attribute AttributeValue */
        foreach ($added as $attribute) {
            $this->addEvent(new AttributeAddedEvent($attribute, $this));
        }

        /** @var $accessory ReservationAccessory */
        foreach ($removed as $attribute) {
            $this->addEvent(new AttributeRemovedEvent($attribute, $this));
        }

        $this->_attributeValues = [];
        foreach ($attributes as $attribute) {
            $this->AddAttributeValue($attribute);
        }
    }

    /**
     * @param  $fileId  int
     */
    public function removeAttachment($fileId)
    {
        if (array_key_exists($fileId, $this->attachmentIds)) {
            $this->addEvent(new AttachmentRemovedEvent($this, $fileId, $this->attachmentIds[$fileId]));
            $this->_removedAttachmentIds[] = $fileId;
        }
    }

    /**
     * @return array|int[]
     */
    public function removedAttachmentIds()
    {
        return $this->_removedAttachmentIds;
    }

    public function addStartReminder(ReservationReminder $reminder)
    {
        if ($reminder->MinutesPrior() != $this->startReminder->MinutesPrior()) {
            $this->addEvent(new ReminderAddedEvent($this, $reminder->MinutesPrior(), ReservationReminderType::Start));
            parent::AddStartReminder($reminder);
        }
    }

    public function addEndReminder(ReservationReminder $reminder)
    {
        if ($reminder->MinutesPrior() != $this->endReminder->MinutesPrior()) {
            $this->addEvent(new ReminderAddedEvent($this, $reminder->MinutesPrior(), ReservationReminderType::End));
            parent::AddEndReminder($reminder);
        }
    }

    public function removeStartReminder()
    {
        if ($this->startReminder->Enabled()) {
            $this->startReminder = ReservationReminder::None();
            $this->addEvent(new ReminderRemovedEvent($this, ReservationReminderType::Start));
        }
    }

    public function removeEndReminder()
    {
        if ($this->endReminder->Enabled()) {
            $this->endReminder = ReservationReminder::None();
            $this->addEvent(new ReminderRemovedEvent($this, ReservationReminderType::End));
        }
    }

    public function WithStartReminder(ReservationReminder $reminder)
    {
        $this->startReminder = $reminder;
    }

    public function withEndReminder(ReservationReminder $reminder)
    {
        $this->endReminder = $reminder;
    }

    public function getCreditsConsumed()
    {
        $consumed = 0;
        foreach ($this->instances() as $instance) {
            $consumed += $instance->getCreditsConsumed();
        }

        return $consumed;
    }

    /**
     * @var float
     */
    protected $unusedCreditBalance = 0;

    public function getUnusedCreditBalance()
    {
        return $this->unusedCreditBalance;
    }

    public function getDeleteReason()
    {
        return $this->_deleteReason;
    }

    /**
     * @var int
     */
    protected $originalCreditsConsumed = 0;

    public function getOriginalCreditsConsumed()
    {
        return $this->originalCreditsConsumed;
    }
}

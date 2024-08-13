<?php

namespace Soap\Jongman\Core\Domain;

use Illuminate\Support\Str;

class ReservationListItem implements ReservationItemViewInterface
{
    /**
     * @var string
     */
    public $referenceNumber;

    /**
     * @var Date
     */
    public $startDate;

    /**
     * @var Date
     */
    public $endDate;

    /**
     * @var DateRange
     */
    public $date;

    /**
     * @var string
     */
    public $resourceName;

    /**
     * @var int
     */
    public $reservationId;

    /**
     * @var int|ReservationUserLevel
     */
    public $userLevelId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $scheduleId;

    /**
     * @var null|string
     */
    public $firstName;

    /**
     * @var null|string
     */
    public $lastName;

    /**
     * @var null|int
     */
    public $userId;

    /**
     * @var null|Date
     */
    public $createdDate;

    /**
     * alias of $CreatedDate
     *
     * @var null|Date
     */
    public $dateCreated;

    /**
     * @var null|Date
     */
    public $modifiedDate;

    /**
     * @var null|bool
     */
    public $isRecurring;

    /**
     * @var null|bool
     */
    public $requiresApproval;

    /**
     * @var string|RepeatType
     */
    public $repeatType;

    /**
     * @var int
     */
    public $repeatInterval;

    /**
     * @var array
     */
    public $repeatWeekdays;

    /**
     * @var string|RepeatMonthlyType
     */
    public $repeatMonthlyType;

    /**
     * @var Date
     */
    public $repeatTerminationDate;

    /**
     * @var int
     */
    public $ownerId;

    /**
     * @var string
     */
    public $ownerEmailAddress;

    /**
     * @var string
     */
    public $ownerPhone;

    /**
     * @var string
     */
    public $ownerOrganization;

    /**
     * @var string
     */
    public $ownerPosition;

    /**
     * @var string
     */
    public $ownerLanguage;

    /**
     * @var string
     */
    public $ownerTimezone;

    /**
     * @var int
     */
    public $seriesId;

    /**
     * @var array|int[]
     */
    public $participantIds = [];

    /** @var array|string[]
     */
    public $participantNames = [];

    /**
     * @var array|int[]
     */
    public $inviteeIds = [];

    /**
     * @var array|string[]
     */
    public $inviteeNames = [];

    /**
     * @var CustomAttributes
     */
    public $attributes;

    /**
     * @var UserPreferences
     */
    public $userPreferences;

    /**
     * @var int
     */
    public $resourceStatusId;

    /**
     * @var int|null
     */
    public $resourceStatusReasonId;

    /**
     * @var ReservationReminderView|null
     */
    public $startReminder;

    /**
     * @var ReservationReminderView|null
     */
    public $endReminder;

    /**
     * @var string|null
     */
    public $resourceColor;

    /**
     * @var int|null
     */
    public $resourceId;

    /**
     * @var null|string
     */
    public $ownerFirstName;

    /**
     * @var null|string
     */
    public $ownerLastName;

    /**
     * @var Date
     */
    public $checkinDate;

    /**
     * @var Date
     */
    public $checkoutDate;

    /**
     * @var bool
     */
    public $isCheckInEnabled;

    /**
     * @var int|null
     */
    public $autoReleaseMinutes;

    /**
     * @var Date
     */
    public $originalEndDate;

    /**
     * @var int|null
     */
    public $creditsConsumed;

    /**
     * @var string[]
     */
    public $participatingGuests = [];

    /**
     * @var string[]
     */
    public $invitedGuests = [];

    /**
     * @var string[]
     */
    public $resourceNames = [];

    /**
     * @var null|int
     */
    public $resourceAdminGroupId = null;

    /**
     * @var null|int
     */
    public $scheduleAdminGroupId = null;

    /**
     * @var int|null
     */
    private $bufferSeconds = 0;

    private $ownerGroupIds = [];

    /**
     * @param  $referenceNumber  string
     * @param  $startDate  Date
     * @param  $endDate  Date
     * @param  $resourceName  string
     * @param  $resourceId  int
     * @param  $reservationId  int
     * @param  $userLevelId  int|ReservationUserLevel
     * @param  $title  string
     * @param  $description  string
     * @param  $scheduleId  int
     * @param  $userFirstName  string
     * @param  $userLastName  string
     * @param  $userId  int
     * @param  $userPhone  string
     * @param  $userPosition  string
     * @param  $userOrganization  string
     * @param  $participant_list  string
     * @param  $invitee_list  string
     * @param  $attribute_list  string
     * @param  $preferences  string
     */
    public function __construct(
        $referenceNumber = null,
        $startDate = null,
        $endDate = null,
        $resourceName = null,
        $resourceId = null,
        $reservationId = null,
        $userLevelId = null,
        $title = null,
        $description = null,
        $scheduleId = null,
        $userFirstName = null,
        $userLastName = null,
        $userId = null,
        $userPhone = null,
        $userOrganization = null,
        $userPosition = null,
        $participant_list = null,
        $invitee_list = null,
        $attribute_list = null,
        $preferences = null
    ) {
        $this->ReferenceNumber = $referenceNumber;
        $this->StartDate = $startDate;
        $this->EndDate = $endDate;
        $this->ResourceName = $resourceName;
        $this->ResourceNames[] = $resourceName;
        $this->ResourceId = $resourceId;
        $this->ReservationId = $reservationId;
        $this->Title = $title;
        $this->Description = $description;
        $this->ScheduleId = $scheduleId;
        $this->FirstName = $userFirstName;
        $this->OwnerFirstName = $userFirstName;
        $this->LastName = $userLastName;
        $this->OwnerLastName = $userLastName;
        $this->OwnerPhone = $userPhone;
        $this->OwnerOrganization = $userOrganization;
        $this->OwnerPosition = $userPosition;
        $this->UserId = $userId;
        $this->OwnerId = $userId;
        $this->UserLevelId = $userLevelId;

        if (! empty($startDate) && ! empty($endDate)) {
            $this->Date = new DateRange($startDate, $endDate);
        }

        if (! empty($participant_list)) {
            $participants = explode('!sep!', $participant_list);

            foreach ($participants as $participant) {
                $pair = explode('=', $participant);

                $id = $pair[0];
                $name = $pair[1];
                $name_parts = explode(' ', $name);
                $firstnames = $name_parts[0];
                $lastnames = $name_parts[1];
                if (count($name_parts) > 2) {
                    // more than just one first and one last name
                    $lastIndex = count($name_parts) - 1;
                    $firstnames = implode(' ', array_splice($name_parts, 0, $lastIndex));
                    $lastnames = $name_parts[0];
                    // could be extended to guess which is a middle name etc.
                }
                $this->ParticipantIds[] = $id;
                $name = new FullName($firstnames, $lastnames);
                $this->ParticipantNames[$id] = $name->__toString();
            }
        }

        if (! empty($invitee_list)) {
            $invitees = explode('!sep!', $invitee_list);

            foreach ($invitees as $invitee) {
                $pair = explode('=', $invitee);

                $id = $pair[0];
                $name = $pair[1];
                $name_parts = explode(' ', $name);
                $firstnames = $name_parts[0];
                $lastnames = $name_parts[1];
                if (count($name_parts) > 2) {
                    // more than just one first and one last name
                    $lastIndex = count($name_parts) - 1;
                    $firstnames = implode(' ', array_splice($name_parts, 0, $lastIndex));
                    $lastnames = $name_parts[$lastIndex];
                    // could be extended to guess which is a middle name etc.
                }
                $this->InviteeIds[] = $id;
                $name = new FullName($firstnames, $lastnames);
                $this->InviteeNames[$id] = $name->__toString();
            }
        }

        $this->Attributes = CustomAttributes::Parse($attribute_list);
        $this->UserPreferences = UserPreferences::Parse($preferences);
    }

    /**
     * @static
     *
     * @param  $row  array
     * @return ReservationItemView
     */
    public static function populate($row)
    {
        $view = new ReservationItemView(
            $row[ColumnNames::REFERENCE_NUMBER],
            Date::FromDatabase($row[ColumnNames::RESERVATION_START]),
            Date::FromDatabase($row[ColumnNames::RESERVATION_END]),
            $row[ColumnNames::RESOURCE_NAME],
            $row[ColumnNames::RESOURCE_ID],
            $row[ColumnNames::RESERVATION_INSTANCE_ID],
            $row[ColumnNames::RESERVATION_USER_LEVEL],
            $row[ColumnNames::RESERVATION_TITLE],
            $row[ColumnNames::RESERVATION_DESCRIPTION],
            $row[ColumnNames::SCHEDULE_ID],
            $row[ColumnNames::OWNER_FIRST_NAME],
            $row[ColumnNames::OWNER_LAST_NAME],
            $row[ColumnNames::OWNER_USER_ID],
            $row[ColumnNames::OWNER_PHONE],
            $row[ColumnNames::OWNER_ORGANIZATION],
            $row[ColumnNames::OWNER_POSITION],
            $row[ColumnNames::PARTICIPANT_LIST],
            $row[ColumnNames::INVITEE_LIST],
            $row[ColumnNames::ATTRIBUTE_LIST],
            $row[ColumnNames::USER_PREFERENCES]
        );

        if (isset($row[ColumnNames::RESERVATION_CREATED])) {
            $view->CreatedDate = Date::FromDatabase($row[ColumnNames::RESERVATION_CREATED]);
            $view->DateCreated = Date::FromDatabase($row[ColumnNames::RESERVATION_CREATED]);
        }

        if (isset($row[ColumnNames::RESERVATION_MODIFIED])) {
            $view->ModifiedDate = Date::FromDatabase($row[ColumnNames::RESERVATION_MODIFIED]);
        }

        if (isset($row[ColumnNames::REPEAT_TYPE])) {
            $repeatConfig = RepeatConfiguration::Create(
                $row[ColumnNames::REPEAT_TYPE],
                $row[ColumnNames::REPEAT_OPTIONS]
            );

            $view->RepeatType = $repeatConfig->Type;
            $view->RepeatInterval = $repeatConfig->Interval;
            $view->RepeatWeekdays = $repeatConfig->Weekdays;
            $view->RepeatMonthlyType = $repeatConfig->MonthlyType;
            $view->RepeatTerminationDate = $repeatConfig->TerminationDate;

            $view->IsRecurring = $row[ColumnNames::REPEAT_TYPE] != RepeatType::None;
        }

        if (isset($row[ColumnNames::RESERVATION_STATUS])) {
            $view->RequiresApproval = $row[ColumnNames::RESERVATION_STATUS] == ReservationStatus::Pending;
        }

        if (isset($row[ColumnNames::EMAIL])) {
            $view->OwnerEmailAddress = $row[ColumnNames::EMAIL];
        }

        if (isset($row[ColumnNames::SERIES_ID])) {
            $view->SeriesId = $row[ColumnNames::SERIES_ID];
        }

        if (isset($row[ColumnNames::RESOURCE_STATUS_REASON_ID])) {
            $view->ResourceStatusReasonId = $row[ColumnNames::RESOURCE_STATUS_REASON_ID];
        }

        if (isset($row[ColumnNames::RESOURCE_STATUS_ID_ALIAS])) {
            $view->ResourceStatusId = $row[ColumnNames::RESOURCE_STATUS_ID_ALIAS];
        }

        if (isset($row[ColumnNames::RESOURCE_BUFFER_TIME])) {
            $view->WithBufferTime($row[ColumnNames::RESOURCE_BUFFER_TIME]);
        }

        if (isset($row[ColumnNames::GROUP_LIST])) {
            $view->WithOwnerGroupIds(explode(',', $row[ColumnNames::GROUP_LIST]));
        }

        if (isset($row[ColumnNames::START_REMINDER_MINUTES_PRIOR])) {
            $view->StartReminder = new ReservationReminderView($row[ColumnNames::START_REMINDER_MINUTES_PRIOR]);
        }
        if (isset($row[ColumnNames::END_REMINDER_MINUTES_PRIOR])) {
            $view->EndReminder = new ReservationReminderView($row[ColumnNames::END_REMINDER_MINUTES_PRIOR]);
        }
        if (isset($row[ColumnNames::RESERVATION_COLOR])) {
            $view->ResourceColor = $row[ColumnNames::RESERVATION_COLOR];
        }
        if (isset($row[ColumnNames::GUEST_LIST])) {
            $guests = explode('!sep!', $row[ColumnNames::GUEST_LIST]);
            foreach ($guests as $guest) {
                $emailAndLevel = explode('=', $guest);
                if ($emailAndLevel[1] == ReservationUserLevel::INVITEE) {
                    $view->InvitedGuests[] = $emailAndLevel[0];
                } else {
                    $view->ParticipatingGuests[] = $emailAndLevel[0];
                }
            }
        }

        if (isset($row[ColumnNames::LANGUAGE_CODE])) {
            $view->OwnerLanguage = $row[ColumnNames::LANGUAGE_CODE];
        }
        if (isset($row[ColumnNames::TIMEZONE_NAME])) {
            $view->OwnerTimezone = $row[ColumnNames::TIMEZONE_NAME];
        }

        $view->CheckinDate = Date::FromDatabase($row[ColumnNames::CHECKIN_DATE]);
        $view->CheckoutDate = Date::FromDatabase($row[ColumnNames::CHECKOUT_DATE]);
        $view->OriginalEndDate = Date::FromDatabase($row[ColumnNames::PREVIOUS_END_DATE]);
        $view->IsCheckInEnabled = (bool) $row[ColumnNames::ENABLE_CHECK_IN];
        $view->AutoReleaseMinutes = $row[ColumnNames::AUTO_RELEASE_MINUTES];
        $view->CreditsConsumed = $row[ColumnNames::CREDIT_COUNT];
        $view->ResourceAdminGroupId = $row[ColumnNames::RESOURCE_ADMIN_GROUP_ID_RESERVATIONS];
        $view->ScheduleAdminGroupId = $row[ColumnNames::SCHEDULE_ADMIN_GROUP_ID_RESERVATIONS];

        return $view;
    }

    /**
     * @return ReservationItemView
     */
    public static function FromReservationView(ReservationView $r)
    {
        $item = new ReservationItemView(
            $r->ReferenceNumber,
            $r->StartDate,
            $r->EndDate,
            $r->ResourceName,
            $r->ResourceId,
            $r->ReservationId,
            ReservationUserLevel::OWNER,
            $r->Title,
            $r->Description,
            $r->ScheduleId,
            $r->OwnerFirstName,
            $r->OwnerLastName,
            $r->OwnerId,
            null,
            null,
            null,
            null,
            null,
            null
        );

        foreach ($r->Participants as $u) {
            $item->ParticipantIds[] = $u->UserId;
        }

        foreach ($r->Invitees as $u) {
            $item->InviteeIds[] = $u->UserId;
        }

        foreach ($r->Attributes as $a) {
            $item->Attributes->Add($a->AttributeId, $a->Value);
        }

        $item->RepeatInterval = $r->RepeatInterval;
        $item->RepeatMonthlyType = $r->RepeatMonthlyType;
        $item->RepeatTerminationDate = $r->RepeatTerminationDate;
        $item->RepeatType = $r->RepeatType;
        $item->RepeatWeekdays = $r->RepeatWeekdays;
        $item->StartReminder = $r->StartReminder;
        $item->EndReminder = $r->EndReminder;
        $item->CreatedDate = $r->DateCreated;
        $item->DateCreated = $r->DateCreated;
        $item->ModifiedDate = $r->DateModified;
        $item->OwnerEmailAddress = $r->OwnerEmailAddress;
        $item->OwnerPhone = $r->OwnerPhone;

        return $item;
    }

    /**
     * @return bool
     */
    public function occursOn(Date $date)
    {
        return $this->date->occursOn($date);
    }

    /**
     * @return Date
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return Date
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @return int
     */
    public function GetReservationId()
    {
        return $this->reservationId;
    }

    /**
     * @return int
     */
    public function GetResourceId()
    {
        return $this->resourceId;
    }

    /**
     * @return string
     */
    public function getReferenceNumber()
    {
        return $this->referenceNumber;
    }

    public function getId()
    {
        return $this->getReservationId();
    }

    /**
     * @return DateDiff
     */
    public function getDuration()
    {
        return $this->startDate->getDifference($this->endDate);
    }

    public function isUserOwner($userId)
    {
        return $this->userId == $userId && $this->userLevelId == ReservationUserLevel::OWNER;
    }

    /**
     * @param  $userId  int
     * @return bool
     */
    public function isUserParticipating($userId)
    {
        return in_array($userId, $this->participantIds);
    }

    /**
     * @param  $userId  int
     * @return bool
     */
    public function isUserInvited($userId)
    {
        return in_array($userId, $this - inviteeIds);
    }

    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @param  int  $seconds
     */
    public function withBufferTime($seconds)
    {
        $this->bufferSeconds = $seconds;
    }

    /**
     * @param  int[]  $ownerGroupIds
     */
    public function withOwnerGroupIds($ownerGroupIds)
    {
        $this->ownerGroupIds = $ownerGroupIds;
    }

    /**
     * @return bool
     */
    public function hasBufferTime()
    {
        return ! empty($this->bufferSeconds);
    }

    /**
     * @return int[]
     */
    public function ownerGroupIds()
    {
        return $this->ownerGroupIds;
    }

    /**
     * @param  int  $attributeId
     * @return null|string
     */
    public function getAttributeValue($attributeId)
    {
        return $this->attributes->get($attributeId);
    }

    /**
     * @return TimeInterval
     */
    public function getBufferTime()
    {
        return TimeInterval::parse($this->bufferSeconds);
    }

    /**
     * @return DateRange
     */
    public function bufferedTimes()
    {
        if (! $this->HasBufferTime()) {
            return new DateRange($this->getStartDate(), $this->getEndDate());
        }

        $buffer = $this->getBufferTime();

        return new DateRange(
            $this->getStartDate()->subtractInterval($buffer),
            $this->getEndDate()->addInterval($buffer)
        );
    }

    /**
     * @return bool
     */
    public function collidesWith(Date $date)
    {
        if ($this->HasBufferTime()) {
            $range = new DateRange(
                $this->startDate->subtractInterval($this->bufferTime),
                $this->endDate->addInterval($this->bufferTime)
            );
        } else {
            $range = new DateRange($this->StartDate, $this->EndDate);
        }

        return $range->contains($date, false);
    }

    public function isCheckinEnabled()
    {
        return $this->isCheckInEnabled;
    }

    public function RequiresCheckin()
    {
        $checkinMinutes = Configuration::instance()->getSectionKey(ConfigSection::RESERVATION, ConfigKeys::RESERVATION_CHECKIN_MINUTES, new IntConverter);

        return $this->checkinDate->toString() == '' &&
                $this->isCheckinEnabledsCheckInEnabled &&
                $this->endDate->greaterThan(Date::now()) &&
                Date::now()->addMinutes($checkinMinutes)->greaterThanOrEqual($this->startDate);
    }

    public function requiresCheckOut()
    {
        if ($this->startDate->lessThan(Date::now()) &&
                $this->checkoutDate->toString() == '' &&
                $this->checkinDate->toString() != '') {
            return $this->isCheckinEnabled();
        }

        return false;
    }

    /**
     * @var null|string
     */
    private $_color = null;

    /**
     * @var ReservationColorRule[]
     */
    private $_colorRules = [];

    /**
     * @param  ReservationColorRule[]  $colorRules
     */
    public function withColorRules($colorRules = [])
    {
        $this->_colorRules = $colorRules;
    }

    /**
     * @return null|string
     */
    public function getColor()
    {
        if ($this->requiresApproval) {
            return '';
        }
        if ($this->_color == null) {
            $this->_color = '';
            // cache the color after the first call to prevent multiple iterations of this logic
            $userColor = $this->userPreferences->Get(UserPreferences::RESERVATION_COLOR);
            $resourceColor = $this->resourceColor;

            if (! empty($resourceColor)) {
                $this->_color = "$resourceColor";
            }

            if (! empty($userColor)) {
                $this->_color = "$userColor";
            }

            if (count($this->_colorRules) > 0) {
                foreach ($this->_colorRules as $rule) {
                    if ($rule->isSatisfiedBy($this)) {
                        $this->_color = "{$rule->color}";
                        //						break;
                    }
                }
            }
        }

        if (! empty($this->_color) && ! Str::startsWith($this->_color, '#')) {
            $this->_color = "#$this->_color";
        }

        return $this->_color;
    }

    /**
     * @return string
     */
    public function getTextColor()
    {
        if ($this->RequiresApproval) {
            return '';
        }
        $color = $this->GetColor();
        if (! empty($color)) {
            $contrastingColor = new ContrastingColor($color);

            return $contrastingColor->__toString();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getBorderColor()
    {
        $color = $this->GetColor();
        if (! empty($color)) {
            $contrastingColor = new AdjustedColor($color, 80);

            return $contrastingColor->__toString();
        }

        return '';
    }

    public function getTitle()
    {
        return $this->Title;
    }

    public function getUserName()
    {
        return new FullName($this->firstName, $this->lastName);
    }

    public function getScheduleId()
    {
        return $this->scheduleId;
    }

    public function isPending()
    {
        return $this->requiresApproval;
    }

    public function getIsNew($newMinutes)
    {
        $modifiedDate = $this->ModifiedDate;

        return
                ($newMinutes > 0) &&
                (empty($modifiedDate)) &&
                ($this->createdDate->addMinutes($newMinutes)->greaterThanOrEqual(Date::now()));
    }

    public function getIsUpdated($updatedMinutes)
    {
        $modifiedDate = $this->modifiedDate;

        return
                ($updatedMinutes > 0) &&
                (! empty($modifiedDate)) &&
                ($this->modifiedDate->addMinutes($updatedMinutes)->greaterThanOrEqual(Date::now()));
    }

    public function IsOwner($userId)
    {
        return $this->isUserOwner($userId);
    }

    public function getLabel()
    {
        return SlotLabelFactory::create($this);
    }
}

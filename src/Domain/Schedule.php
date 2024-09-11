<?php

namespace Soap\Jongman\Core\Domain;

class Schedule
{
    public static function fromModel($model)
    {
        $schedule = new Schedule(
            $model->id,
            $model->name,
            $model->is_default,
            $model->weekday_start,
            $model->visible_days,
            $model->timezone,
            $model->schedule_layout_id
        );
        /*
        $schedule->withSubscription($row[ColumnNames::ALLOW_CALENDAR_SUBSCRIPTION]);
        $schedule->withPublicId($row[ColumnNames::PUBLIC_ID]);
        $schedule->setAdminGroupId($row[ColumnNames::SCHEDULE_ADMIN_GROUP_ID]);
        $schedule->setAvailability(Date::FromDatabase($row[ColumnNames::SCHEDULE_AVAILABLE_START_DATE]), Date::FromDatabase($row[ColumnNames::SCHEDULE_AVAILABLE_END_DATE]));
        $schedule->setDefaultStyle($row[ColumnNames::SCHEDULE_DEFAULT_STYLE]);
        if (in_array(ColumnNames::LAYOUT_TYPE, $row)) $schedule->SetLayoutType($row[ColumnNames::LAYOUT_TYPE]);
        $schedule->setTotalConcurrentReservations($row[ColumnNames::TOTAL_CONCURRENT_RESERVATIONS]);
        $schedule->setMaxResourcesPerReservation($row[ColumnNames::MAX_RESOURCES_PER_RESERVATION]);
        */

        return $schedule;
    }
}

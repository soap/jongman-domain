<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Common\Date;

interface ScheduleLayoutInterface extends DailyScheduleLayoutInterface, LayoutTimezoneInterface
{
    /**
     * @param  bool  $hideBlockedPeriods
     * @return SchedulePeriod[]|array of SchedulePeriod objects
     */
    public function getLayout(Date $layoutDate, $hideBlockedPeriods = false);

    /**
     * @return SchedulePeriod|null period which occurs at this datetime. Includes start time, excludes end time. null if no match is found
     */
    public function getPeriod(Date $date);

    /**
     * @return SlotCount
     */
    public function getSlotCount(Date $startDate, Date $endDate);

    /**
     * @param  PeakTimes  $peakTimes
     */
    //public function ChangePeakTimes(PeakTimes $peakTimes);

    //public function RemovePeakTimes();

    /**
     * @return bool
     */
    public function fitsToHours();

    /**
     * @return bool
     */
    //public function UsesCustomLayout();
}

<?php

namespace Soap\Jongman\Core\Domain;

use Exception;
use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DayOfWeek;
use Soap\Jongman\Core\Common\Time;
use Soap\Jongman\Core\Enums\PeriodTypeEnum;
use Soap\Jongman\Core\Interfaces\ScheduleLayoutInterface;

class ScheduleLayout implements ScheduleLayoutInterface
{
    /**
     * @var array|LayoutPeriod
     *                         An array of periods for each day of the week
     */
    private $_periods = [];

    /**
     * @var bool
     *           Whether to use different layouts for different days of the week
     */
    private $usingDailyLayouts = false;

    /**
     * @var string
     */
    private $layoutTimezone;

    /**
     * @var string[]
     */
    private $startTimes = [];

    /**
     * @var bool
     */
    private $cached = false;

    private $cachedPeriods = [];

    public function __construct(private $targetTimezone = null)
    {
        if ($this->targetTimezone === null) {
            $this->targetTimezone = 'Asia/Bangkok'; //auth()->user()->timezone;
        }
    }

    /**
     * test passed
     */
    public function getSlots($dayOfWeek = null)
    {
        if (is_null($dayOfWeek)) {
            if ($this->usingDailyLayouts) {
                throw new Exception('Day of week must be provided when using daily layouts');
            }
            $periods = $this->_periods;
        } else {
            if (! $this->usingDailyLayouts) {
                throw new Exception('Day of week should not be provided when not using daily layouts');
            }
            $periods = $this->_periods[$dayOfWeek];
        }

        $this->sortItems($periods);

        return $periods;
    }

    /**
     * Appends a period to the schedule layout
     *
     * @param  Time  $startTime  starting time of the schedule in specified timezone
     * @param  Time  $endTime  ending time of the schedule in specified timezone
     * @param  string  $label  optional label for the period
     * @param  DayOfWeek|int|null  $dayOfWeek
     */
    public function appendPeriod(Time $startTime, Time $endTime, $label = null, $dayOfWeek = null)
    {
        $this->appendGenericPeriod($startTime, $endTime, PeriodTypeEnum::RESERVABLE, $label, $dayOfWeek);
    }

    /**
     * Appends a period that is not reservable to the schedule layout
     *
     * @param  Time  $startTime  starting time of the schedule in specified timezone
     * @param  Time  $endTime  ending time of the schedule in specified timezone
     * @param  string  $label  optional label for the period
     * @param  DayOfWeek|int|null  $dayOfWeek
     * @return void
     */
    public function appendBlockedPeriod(Time $startTime, Time $endTime, $label = null, $dayOfWeek = null)
    {
        $this->appendGenericPeriod($startTime, $endTime, PeriodTypeEnum::NON_RESERVABLE, $label, $dayOfWeek);
    }

    protected function appendGenericPeriod(
        Time $startTime,
        Time $endTime,
        $periodType,
        $label = null,
        $dayOfWeek = null
    ) {
        if ($this->startTimeCanBeAdded($startTime, $dayOfWeek)) {
            $this->layoutTimezone = $startTime->timezone();
            $layoutPeriod = new LayoutPeriod($startTime, $endTime, $periodType, $label);
            if (! is_null($dayOfWeek)) {
                $this->usingDailyLayouts = true;
                $this->_periods[$dayOfWeek][] = $layoutPeriod;
            } else {
                $this->_periods[] = $layoutPeriod;
            }
        }
    }

    /**
     * @return bool
     */
    protected function spansMidnight(Date $start, Date $end)
    {
        return ! $start->dateEquals($end) && ! $end->isMidnight();
    }

    /**
     * @param  Date  $layoutDate  date for which to get the layout
     * @param  bool  $hideBlockedPeriods  whether to hide non-reservable periods
     * @return array|SchedulePeriod[] array of sorted SchedulePeriods and NonSchedulePeriods
     */
    public function getLayout(Date $layoutDate, $hideBlockedPeriods = false)
    {
        $targetTimezone = $this->targetTimezone;
        $layoutDate = $layoutDate->toTimezone($targetTimezone);

        if ($this->usingDailyLayouts) {
            return $this->getLayoutDaily($layoutDate, $hideBlockedPeriods);
        }
        /*
        $cachedValues = $this->GetCachedValuesForDate($layoutDate);
        if (!empty($cachedValues)) {
            return $cachedValues;
        }
        */

        $list = new PeriodList;

        $periods = $this->getPeriods($layoutDate);

        if (count($periods) <= 0) {
            throw new Exception(sprintf('No periods defined for date %s', $layoutDate));
        }

        $layoutTimezone = $periods[0]->timezone();
        $workingDate = Date::create(
            $layoutDate->year(),
            $layoutDate->month(),
            $layoutDate->day(),
            0,
            0,
            0,
            $layoutTimezone
        );

        $midnight = $layoutDate->getDate(); // get only date hour, minute, second to be zero

        /* @var $period LayoutPeriod */
        foreach ($periods as $period) {
            if ($hideBlockedPeriods && ! $period->isReservable()) {
                continue;
            }
            /** @var Time */
            $start = $period->start;
            $end = $period->end;
            $periodType = $period->periodTypeClass();
            $label = $period->label;
            $labelEnd = null;

            // convert to target timezone
            $periodStart = $workingDate->setTime($start)->toTimezone($targetTimezone);
            $periodEnd = $workingDate->setTime($end, true)->toTimezone($targetTimezone);

            if ($periodEnd->lessThan($periodStart)) {
                $periodEnd = $periodEnd->addDays(1);
            }

            $startTime = $periodStart->getTime();
            $endTime = $periodEnd->getTime();

            if ($this->bothDatesAreOff($periodStart, $periodEnd, $layoutDate)) {
                $periodStart = $layoutDate->setTime($startTime);
                $periodEnd = $layoutDate->setTime($endTime, true);
            }

            if ($this->spansMidnight($periodStart, $periodEnd)) {
                if ($periodStart->lessThan($midnight)) {
                    // add compensating period at end
                    $start = $layoutDate->setTime($startTime);
                    $end = $periodEnd->addDays(1);
                    $list->add($this->buildPeriod($periodType, $start, $end, $label, $labelEnd));
                } else {
                    // add compensating period at start
                    $start = $periodStart->addDays(-1);
                    $end = $layoutDate->setTime($endTime, true);
                    $list->add($this->buildPeriod($periodType, $start, $end, $label, $labelEnd));
                }
            }

            if (! $periodStart->isMidnight() && $periodStart->lessThan($layoutDate) && $periodEnd->dateEquals($layoutDate) && $periodEnd->isMidnight()) {
                $periodStart = $periodStart->addDays(1);
                $periodEnd = $periodEnd->addDays(1);
            }

            $list->add($this->buildPeriod($periodType, $periodStart, $periodEnd, $label, $labelEnd));
        }

        $layout = $list->getItems();
        $this->sortItems($layout);
        //$this->addCached($layout, $workingDate);

        return $layout;
    }

    private function getLayoutDaily(Date $requestedDate, $hideBlockedPeriods = false)
    {
        if ($requestedDate->timezone() != $this->targetTimezone) {
            throw new Exception('Target timezone and requested timezone do not match');
        }
        /*
        $cachedValues = $this->getCachedValuesForDate($requestedDate);
        if (!empty($cachedValues)) {
            return $cachedValues;
        }
        */
        // check cache
        $baseDateInLayoutTz = Date::Create(
            $requestedDate->year(),
            $requestedDate->month(),
            $requestedDate->day(),
            0,
            0,
            0,
            $this->layoutTimezone
        );

        $list = new PeriodList;
        $this->addDailyPeriods($requestedDate->weekday(), $baseDateInLayoutTz, $requestedDate, $list, $hideBlockedPeriods);

        if ($this->layoutTimezone != $this->targetTimezone) {
            $requestedDateInTargetTz = $requestedDate->toTimezone($this->layoutTimezone);

            $adjustment = 0;
            if ($requestedDateInTargetTz->format('YmdH') < $requestedDate->format('YmdH')) {
                $adjustment = -1;
            } else {
                if ($requestedDateInTargetTz->format('YmdH') > $requestedDate->format('YmdH')) {
                    $adjustment = 1;
                }
            }

            if ($adjustment != 0) {
                $adjustedDate = $requestedDate->addDays($adjustment);
                $baseDateInLayoutTz = $baseDateInLayoutTz->addDays($adjustment);
                $this->addDailyPeriods($adjustedDate->weekday(), $baseDateInLayoutTz, $requestedDate, $list);
            }
        }
        $layout = $list->getItems();
        $this->sortItems($layout);

        //$this->addCached($layout, $requestedDate);
        return $layout;
    }

    /**
     * @param  int  $day
     * @param  Date  $baseDateInLayoutTz
     * @param  Date  $requestedDate
     * @param  PeriodList  $list
     * @param  bool  $hideBlockedPeriods
     */
    private function addDailyPeriods($day, $baseDateInLayoutTz, $requestedDate, $list, $hideBlockedPeriods = false)
    {
        $periods = $this->_periods[$day];
        /** @var $period LayoutPeriod */
        foreach ($periods as $period) {
            if ($hideBlockedPeriods && ! $period->isReservable()) {
                continue;
            }
            $begin = $baseDateInLayoutTz->setTime($period->start)->toTimezone($this->targetTimezone);
            $end = $baseDateInLayoutTz->setTime($period->end, true)->toTimezone($this->targetTimezone);
            // only add this period if it occurs on the requested date
            if ($begin->dateEquals($requestedDate) || ($end->dateEquals($requestedDate) && ! $end->isMidnight())) {
                $built = $this->buildPeriod($period->periodTypeClass(), $begin, $end, $period->label);
                $list->add($built);
            }
        }
    }

    /**
     * @param  array|SchedulePeriod[]  $layout
     * @param  Date  $date
     */
    private function addCached($layout, $date)
    {
        $this->cached = true;
        $this->cachedPeriods[$date->format('Ymd')] = $layout;
    }

    /**
     * @param  Date  $date
     * @return array|SchedulePeriod[]
     */
    private function getCachedValuesForDate($date)
    {
        $key = $date->format('Ymd');
        if (array_key_exists($date->format('Ymd'), $this->cachedPeriods)) {
            return $this->cachedPeriods[$key];
        }

        return null;
    }

    private function bothDatesAreOff(Date $start, Date $end, Date $layoutDate)
    {
        return ! $start->dateEquals($layoutDate) && ! $end->dateEquals($layoutDate);
    }

    private function buildPeriod($periodType, Date $start, Date $end, $label, $labelEnd = null)
    {
        $class = __NAMESPACE__.'\\'.$periodType;

        return new $class($start, $end, $label, $labelEnd);
    }

    public function timezone()
    {
        return $this->targetTimezone;
    }

    /**
     * @return SchedulePeriod period which occurs at this datetime. Includes start time, excludes end time
     */
    public function getPeriod(Date $date)
    {
        $timezone = $this->layoutTimezone;
        $tempDate = $date->toTimezone($timezone);
        $periods = $this->getPeriods($tempDate);

        /** @var $period LayoutPeriod */
        foreach ($periods as $period) {
            $start = Date::create(
                $tempDate->year(),
                $tempDate->month(),
                $tempDate->day(),
                $period->start->hour(),
                $period->start->minute(),
                0,
                $timezone
            );
            $end = Date::create(
                $tempDate->year(),
                $tempDate->month(),
                $tempDate->day(),
                $period->end->hour(),
                $period->end->minute(),
                0,
                $timezone
            );

            if ($end->isMidnight()) {
                // if end time is 2024-06-10 00:00 set to 2024-06-11 00:00
                $end = $end->addDays(1);
            }

            if ($start->compare($date) <= 0 && $end->compare($date) > 0) {
                return $this->buildPeriod($period->periodTypeClass(), $start, $end, $period->label);
            }
        }

        return null;
    }

    public function usesDailyLayouts()
    {
        return $this->usingDailyLayouts;
    }

    private function getPeriods(Date $layoutDate)
    {
        if ($this->usingDailyLayouts) {
            $dayOfWeek = $layoutDate->weekday();

            return $this->_periods[$dayOfWeek];
        } else {
            return $this->_periods;
        }
    }

    private function startTimeCanBeAdded(Time $startTime, $dayOfWeek = null)
    {
        $day = $dayOfWeek;
        if ($day == null) {
            $day = 0;
        }

        if (! array_key_exists($day, $this->startTimes)) {
            $this->startTimes[$day] = [];
        }

        if (array_key_exists($startTime->toString(), $this->startTimes[$day])) {
            return false;
        }

        $this->startTimes[$day][$startTime->toString()] = $startTime->toString();

        return true;
    }

    protected function sortItems(&$items)
    {
        usort($items, [$this, 'sortBeginTimes']);
    }

    /**
     * @static
     *
     * @param  SchedulePeriod|LayoutPeriod  $period1
     * @param  SchedulePeriod|LayoutPeriod  $period2
     * @return int
     */
    public static function sortBeginTimes($period1, $period2)
    {
        return $period1->compare($period2);
    }

    /**
     * @param  string  $timezone
     * @param  string  $reservableSlots
     * @param  string  $blockedSlots
     * @return ScheduleLayout
     */
    public static function parse($timezone, $reservableSlots, $blockedSlots)
    {
        $parser = new LayoutParser($timezone);
        $parser->addReservable($reservableSlots);
        $parser->addBlocked($blockedSlots);

        return $parser->getLayout();
    }

    /**
     * @param  string  $timezone
     * @param  string[]|array  $reservableSlots
     * @param  string[]|array  $blockedSlots
     * @return ScheduleLayout
     *
     * @throws Exception
     */
    public static function parseDaily($timezone, $reservableSlots, $blockedSlots)
    {
        if (count($reservableSlots) != DayOfWeek::NumberOfDays || count($blockedSlots) != DayOfWeek::NumberOfDays) {
            throw new Exception(sprintf(
                'LayoutParser parseDaily missing slots. $reservableSlots=%s, $blockedSlots=%s',
                count($reservableSlots),
                count($blockedSlots)
            ));
        }

        for ($day = 0; $day < DayOfWeek::NumberOfDays; $day++) {
            if (trim($reservableSlots[$day]) == '' && trim($blockedSlots[$day]) == '') {
                throw new Exception('Empty slots on '.$day);
            }
        }

        $parser = new LayoutParser($timezone);

        foreach (DayOfWeek::days() as $day) {
            $parser->addReservable($reservableSlots[$day], $day);
            $parser->addBlocked($blockedSlots[$day], $day);
        }

        return $parser->getLayout();
    }

    public function getSlotCount(Date $startDate, Date $endDate)
    {
        $slots = 0;
        $peakSlots = 0;
        $start = $startDate->toTimezone($this->layoutTimezone);
        $end = $endDate->toTimezone($this->layoutTimezone);
        $testDate = $start;

        $periods = $this->getPeriods($startDate);

        /** var LayoutPeriod $period */
        foreach ($periods as $period) {
            if (! $period->isReservable()) {
                continue;
            }

            if ($start->lessThanOrEqual($testDate->setTime($period->start)) && $end->greaterThanOrEqual($testDate->setTimet($period->end, true))) {

                $isPeak = $this->hasPeakTimesDefined() && $this->peakTimes->isWithinPeak($testDate->setTime($period->start));
                if ($isPeak) {
                    $peakSlots++;
                } else {
                    $slots++;
                }
            }
        }

        return new SlotCount($slots, $peakSlots);
    }

    public function fitsToHours()
    {
        return true;
    }
}

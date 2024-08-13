<?php

namespace Soap\Jongman\Core\Application\Schedule;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Interfaces\DailyLayoutInterface;
use Soap\Jongman\Core\Interfaces\ReservationListingInterface;
use Soap\Jongman\Core\Interfaces\ScheduleLayoutInterface;

class DailyLayout implements DailyLayoutInterface
{
    private $_reservationListing;

    private $_scheduleLayout;

    public function __construct(ReservationListingInterface $listing, ScheduleLayoutInterface $layout)
    {
        $this->_reservationListing = $listing;
        $this->_scheduleLayout = $layout;
    }

    public function timezone()
    {
        return $this->_scheduleLayout->timezone();
    }

    /**
     * @param  int  $resourceId
     * @return array|ReservationSlotInterface[]
     */
    public function getLayout(Date $date, $resourceId)
    {
        try {
            $hideBlockedPeridos = false;

            $items = $this->_reservationListing->onDateForResource($date, $resourceId);

            $list = new ScheduleReservationList($items, $this->_scheduleLayout, $date, $hideBlockedPeridos);

            $slots = $list->buildSlots();

            return $slots;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getSummary(Date $date, $resourceId)
    {
        $summary = new DailyReservationSummary;

        $items = $this->_reservationListing->onDateForResource($date, $resourceId);

        if (count($items) > 0) {
            foreach ($items as $item) {
                $summary->addReservation($item);
            }
        }

        return $summary;
    }

    /**
     * @todo Why is this method here?
     *
     * @return bool
     */
    public function isDateReservable(Date $date)
    {
        return $date->dateCompare(Date::now()) >= 0;
    }

    /**
     * @return string[]
     */
    public function getLabels(Date $displayDate)
    {
        $hideBlockedPeridos = false;

        $labels = [];
        $periods = $this->_scheduleLayout->getLayout($displayDate, $hideBlockedPeridos);

        if ($periods[0]->beginBefore($displayDate)) {
            $labels[] = $periods[0]->label($displayDate->getDate());
        } else {
            $labels[] = $peridos[0]->label();
        }

        for ($i = 1; $i < count($periods); $i++) {
            $labels[] = $periods[$i]->label();
        }

        return $labels;
    }

    public function getPeriods(Date $displayDate, $fitToHours = false)
    {
        $hideBlockedPeridos = false;

        $periods = $this->_scheduleLayout->getLayout($displayDate, $hideBlockedPeridos);

        if (! $fitToHours || ! $this->_scheduleLayout->fitsToHours()) {
            return $periods;
        }

        /** @var $periodsToReturn SpanablePeriod[] */
        $periodsToReturn = [];

        for ($i = 0; $i < count($periods); $i++) {
            $span = 1;
            /** @var $currentPeriod SchedulePeriod */
            $currentPeriod = $periods[$i];
            $periodStart = $currentPeriod->beginDate();
            $periodLength = $periodStart->getDifference($currentPeriod->endDate())->minutes();

            if (! $currentPeriod->isLabelled() && ($periodStart->minute() == 0 && $periodLength <= 30)) {
                $span = 0;
                $nextPeriodTime = $periodStart->addMinutes(60);
                $tempPeriod = $currentPeriod;

                while ($tempPeriod != null && $tempPeriod->beginDate()->lessThan($nextPeriodTime)) {
                    $span++;
                    $i++;
                    $tempPeriod = isset($periods[$i]) ? $periods[$i] : null;
                }

                if ($span > 0) {
                    $i--;
                }

            }
            $periodsToReturn[] = new SpanablePeriod($currentPeriod, $span);
            ray($periodsToReturn);
        }

        return $periodsToReturn;
    }
}

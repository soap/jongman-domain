<?php

namespace Soap\Jongman\Core\Application\Schedule;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\Time;
use Soap\Jongman\Core\Domain\ScheduleLayout;
use Soap\Jongman\Core\Interfaces\ScheduleReservationListInterface;

class ScheduleReservationList implements ScheduleReservationListInterface
{
    private $destinationTimezone;

    private $midNight;

    private $layoutDateStart;

    private $layoutDateEnd;

    private $layoutItems;

    private $itemsByStartTime = [];

    /**
     * @var array|SchedulePeriod[]
     */
    private $layoutByStartTime = [];

    /**
     * @var array|int[]
     */
    private $layoutIndexByEndTime = [];

    public function __construct(private $items, private ScheduleLayout $layout, private Date $layoutDate, private bool $hideBlockedPeridos = false)
    {
        $this->destinationTimezone = $this->layout->timezone();
        $this->midNight = new Time(0, 0, 0, $this->destinationTimezone);
        $this->layoutDateStart = $layoutDate->toTimezone($this->destinationTimezone)->getDate();
        $this->layoutDateEnd = $this->layoutDateStart->addDays(1);
        $this->layoutItems = $this->layout->getLayout($layoutDate, $hideBlockedPeridos);
    }

    /**
     * Builds the slots of resource reservations
     *
     * @return ReservationSlot[]
     */
    public function buildSlots(): array
    {
        $slots = [];
        for ($currentIndex = 0; $currentIndex < count($this->items); $currentIndex++) {
            // get current layout item
            $layoutItem = $this->layoutItems[$currentIndex];
            // find the reservation item that starts at the same time as the layout item
            $item = $this->getItemStartingAt($layoutItem->beginDate()); // DateTime

            if ($item != null) {
                if ($this->itemEndsOnFutureDate($item)) {
                    $endTime = $this->layoutDateEnd;
                } else {
                    $endTime = $item->endDate()->toTimezone($this->destinationTimezone);
                }

                $endingPeriodIndex = max($this->getLayoutIndexEndingAt($endTime), $currentIndex);
                $span = ($endingPeriodIndex - $currentIndex) + 1;

                $slots[] = $item->buildSlot(
                    $layoutItem,
                    $this->layoutItems[$endingPeriodIndex],
                    $this->layoutDateStart,
                    $span
                );

                $currentIndex = $endingPeriodIndex;
            } else {
                // no reservation item found for this layout item, create an empty slot
                $slots[] = new EmptyReservationSlot($layoutItem, $layoutItem, $this->layoutDateStart, $layoutItem->isReservable());
            }
        }

        return $slots;
    }

    private function IndexItems()
    {
        foreach ($this->items as $index => $item) {
            if ($item->hasBufferTime()) {
                $bufferItem = new BufferItem($item, BufferItem::LOCATION_BEFORE);
                if (! $this->collides($bufferItem, $index)) {
                    $this->indexItem($bufferItem);
                }
            }

            $this->indexItem($item);

            if ($item->hasBufferTime()) {
                $bufferItem = new BufferItem($item, BufferItem::LOCATION_AFTER);
                if (! $this->collides($bufferItem, $index)) {
                    $this->indexItem($bufferItem);
                }
            }
        }

        //Log::Debug('IndexItems() took %s seconds', $sw->GetTotalSeconds());
    }

    private function indexItem(ReservationListItem $item)
    {
        if (($item->startDate()->compare($this->lastLayoutTime) >= 0) ||
                ($item->endDate()->compare($this->firstLayoutTime) <= 0)) {
            // skip the item if it starts after this layout or ends before it
            return;
        }

        $start = $item->startDate()->timezone($this->destinationTimezone);

        $startsInPast = $this->itemStartsOnPastDate($item);
        if ($startsInPast) {
            $start = $this->firstLayoutTime;
        } elseif ($this->itemIsNotOnLayoutBoundary($item)) {
            $layoutItem = $this->findClosestLayoutIndexBeforeStartingTime($item);
            if (! empty($layoutItem)) {
                $start = $layoutItem->beginDate()->timezone($this->destinationTimezone);
            }
        }

        $this->itemsByStartTime[$start->timestamp] = $item;
    }

    private function itemStartsOnPastDate(ReservationListItem $item)
    {
        //Log::Debug("PAST");
        return $item->startDate()->compare($this->layoutDateStart) <= 0;
    }

    private function itemEndsOnFutureDate(ReservationListItem $item)
    {
        //Log::Debug("%s %s %s", $reservation->GetReferenceNumber(), $reservation->GetEndDate()->GetDate(), $this->_layoutDateEnd->GetDate());
        return $item->endDate()->compare($this->layoutDateEnd) >= 0;
    }

    private function indexLayout()
    {
        if (! $this->ayoutIndexCache->contains($this->layoutDateStart)) {
            $this->layoutIndexCache->add(
                $this->layoutDateStart,
                $this->layoutItems,
                $this->layoutDateStart,
                $this->layoutDateEnd
            );
        }
        $cachedIndex = $this->layoutIndexCache->get($this->layoutDateStart);
        $this->firstLayoutTime = $cachedIndex->getFirstLayoutTime();
        $this->lastLayoutTime = $cachedIndex->getLastLayoutTime();
        $this->layoutByStartTime = $cachedIndex->layoutByStartTime();
        $this->layoutIndexByEndTime = $cachedIndex->layoutIndexByEndTime();

    }

    /**
     * @return int index of $_layoutItems which has the corresponding $endingTime
     */
    private function getLayoutIndexEndingAt(Date $endingTime)
    {
        $timeKey = $endingTime->timestamp;

        if (array_key_exists($timeKey, $this->layoutIndexByEndTime)) {
            return $this->layoutIndexByEndTime[$timeKey];
        }

        return $this->findClosestLayoutIndexBeforeEndingTime($endingTime);
    }

    private function getItemStartingAt(Date $beginDateTime)
    {
        $timeKey = $beginTime->timestamp;
        if (array_key_exists($timeKey, $this->itemsByStartTime)) {
            return $this->itemsByStartTime[$timeKey];
        }

        return null;
    }

    /**
     * @return int index of $_layoutItems which has the closest ending time to $endingTime without going past it
     */
    private function findClosestLayoutIndexBeforeEndingTime(Date $endingTime)
    {
        for ($i = count($this->layoutItems) - 1; $i >= 0; $i--) {
            $currentItem = $this->layoutItems[$i];

            if ($currentItem->beginDate()->lessThan($endingTime)) {
                return $i;
            }
        }

        return 0;
    }

    /**
     * @return SchedulePeriod which has the closest starting time to $endingTime without going prior to it
     */
    private function findClosestLayoutIndexBeforeStartingTime(ReservationListItem $item)
    {
        for ($i = count($this->layoutItems) - 1; $i >= 0; $i--) {
            $currentItem = $this->layoutItems[$i];

            if ($currentItem->beginDate()->lessThan($item->StartDate())) {
                return $currentItem;
            }
        }

        if ($item->startDate()->lessThan($this->layoutItems[0]->BeginDate())) {
            return $this->layoutItems[0];
        }

        Log::Error('Could not find a fitting starting slot for reservation. Item %s', var_export($item, true));

        return null;
    }

    /**
     * @return bool
     */
    private function itemIsNotOnLayoutBoundary(ReservationListItem $item)
    {
        $timeKey = $item->startDate()->timestamp;

        return ! (array_key_exists($timeKey, $this->layoutByStartTime));
    }

    private function collides(ReservationListItem $item, $itemIndex)
    {
        $previousItem = $itemIndex > 0 ? $this->_items[--$itemIndex] : null;
        $nextItem = $itemIndex < count($this->items) - 1 ? $this->items[++$itemIndex] : null;

        $itemDateRange = new DateRange($item->startDate(), $item->endDate());
        if ($previousItem != null) {
            if ($itemDateRange->overlaps(new DateRange($previousItem->startDate(), $previousItem->endDate()))) {
                return true;
            }
        }

        if ($nextItem != null) {
            if ($itemDateRange->overlaps(new DateRange($nextItem->startDate(), $nextItem->endDate()))) {
                return true;
            }
        }

        return false;
    }
}

<?php

namespace Soap\Jongman\Core\Application\Schedule;

use Soap\Jongman\Core\Common\Date;

class ReservationListing implements ImmutableReservationListingInterface
{
    /**
     * @param  string  $targetTimezone
     * @param  DateRange|null  $acceptableDateRange
     */
    public function __construct($targetTimezone, $acceptableDateRange = null)
    {
        $this->timezone = $targetTimezone;
        $this->min = Date::min();
        $this->max = Date::max();
        if ($acceptableDateRange != null) {
            $this->min = $acceptableDateRange->getBegin();
            $this->max = $acceptableDateRange->getEnd()->addDays(1);
        }
    }

    /**
     * @var string
     */
    protected $timezone;

    /**
     * @var Date
     */
    protected $min;

    /**
     * @var Date
     */
    protected $max;

    /**
     * @var array|ReservationItemView[]
     */
    protected $_reservations = [];

    /**
     * @var array|ReservationItemView[]
     */
    protected $_reservationByResource = [];

    /**
     * @var array|ReservationItemView[]
     */
    protected $_reservationsByDate = [];

    /**
     * @var array|ReservationItemView[]
     */
    protected $_reservationsByDateAndResource = [];

    public function add($reservation)
    {
        $this->addItem(new ReservationListItem($reservation));
    }

    public function addBlackout($blackout)
    {
        $this->addItem(new BlackoutListItem($blackout));
    }

    protected function addItem(ReservationListItem $item)
    {
        $currentDate = $item->BufferedStartDate()->ToTimezone($this->timezone);
        $lastDate = $item->BufferedEndDate()->ToTimezone($this->timezone);

        if ($currentDate->greaterThan($lastDate)) {
            //Log::Error("Reservation dates corrupted. ReferenceNumber=%s, Start=%s, End=%s", $item->ReferenceNumber(), $item->StartDate(), $item->EndDate());
            return;
        }

        if ($currentDate->isSameDay($lastDate)) {
            $this->addOnDate($item, $currentDate);
        } else {
            while ($currentDate->lessThan($lastDate) && ! $currentDate->isSameDate($lastDate) && $currentDate->lessThan($this->max)) {
                $this->addOnDate($item, $currentDate);
                $currentDate = $currentDate->addDays(1);
            }
            if (! $lastDate->isMidnight()) {
                $this->addOnDate($item, $lastDate);
            }
        }

        $this->_reservations[] = $item;
        $this->_reservationByResource[$item->resourceId()][] = $item;
    }

    protected function addOnDate(ReservationListItem $item, Date $date)
    {
        if ($item->bufferedStartDate()->greaterThan($this->max) || $item->bufferedEndDate()->lessThan($this->min)) {
            return;
        }

        //		Log::Debug('Adding id %s on %s', $item->Id(), $date);
        $this->_reservationsByDate[$date->format('Ymd')][] = $item;
        $this->_reservationsByDateAndResource[$date->format('Ymd').'|'.$item->resourceId()][] = $item;
    }

    public function count()
    {
        return count($this->_reservations);
    }

    public function reservations()
    {
        return $this->_reservations;
    }

    /**
     * @param  array|ReservationListItem[]  $reservations
     * @param  DateRange|null  $acceptableDateRange
     * @return ReservationListing
     */
    private function create($reservations, $acceptableDateRange = null)
    {
        $reservationListing = new ReservationListing($this->timezone, $acceptableDateRange);

        if ($reservations != null) {
            foreach ($reservations as $reservation) {
                $reservationListing->addItem($reservation);
            }
        }

        return $reservationListing;
    }

    /**
     * @param  Date  $date
     * @return ReservationListing
     */
    public function onDate($date)
    {
        //		Log::Debug('Found %s reservations on %s', count($this->_reservationsByDate[$date->Format('Ymd')]), $date);

        $key = $date->format('Ymd');
        $reservations = [];
        if (array_key_exists($key, $this->_reservationsByDate)) {
            $reservations = $this->_reservationsByDate[$key];
        }

        return $this->create($reservations, new DateRange($this->min, $this->max));
    }

    public function forResource($resourceId)
    {
        if (array_key_exists($resourceId, $this->_reservationByResource)) {
            return $this->create($this->_reservationByResource[$resourceId], new DateRange($this->min, $this->max));
        }

        return new ReservationListing($this->timezone, new DateRange($this->min, $this->max));
    }

    public function onDateForResource(Date $date, $resourceId)
    {
        $key = $date->format('Ymd').'|'.$resourceId;

        if (! array_key_exists($key, $this->_reservationsByDateAndResource)) {
            return [];
        }

        return $this->_reservationsByDateAndResource[$key];
    }
}

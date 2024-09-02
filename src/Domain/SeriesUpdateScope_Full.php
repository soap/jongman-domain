<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Domain\Values\ReservationStartTimeConstraint;
use Soap\Jongman\Core\Interfaces\RepeatOptionsInterface;

class SeriesUpdateScope_Full extends SeriesUpdateScopeBase
{
    private $hasSameConfiguration = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function getScope()
    {
        return SeriesUpdateScope::FullSeries;
    }

    /**
     * @param  ExistingReservationSeries  $series
     * @return array
     */
    public function instances($series)
    {
        $bookedBy = $series->bookedBy();
        if (! is_null($bookedBy) && $bookedBy->isAdmin) {
            return $series->_instances();
        }

        return $this->allInstancesGreaterThan($series, $this->earliestDateToKeep($series));
    }

    /**
     * @param  ExistingReservationSeries  $series
     * @return mixed
     */
    public function earliestDateToKeep($series)
    {
        $startTimeConstraint = JongmanFactory::getConfig()->get('reservation.start_time_constraint');

        if (ReservationStartTimeConstraint::isCurrent($startTimeConstraint)) {
            return $series->currentInstance()->startDate();
        }

        if (ReservationStartTimeConstraint::isNone($startTimeConstraint)) {
            return Date::min();
        }

        return Date::now();
    }

    /**
     * @param  ReservationSeries  $series
     * @param  RepeatOptionsInterface  $targetRepeatOptions
     * @return bool
     */
    public function canChangeRepeatTo($series, $targetRepeatOptions)
    {
        $this->hasSameConfiguration = $targetRepeatOptions->hasSameConfigurationAs($series->repeatOptions());

        return parent::canChangeRepeatTo($series, $targetRepeatOptions);
    }

    public function requiresNewSeries()
    {
        return false;
    }

    public function shouldInstanceBeRemoved($series, $instance)
    {
        if ($series->currentInstance()->referenceNumber() == $instance->referenceNumber()) {
            return false;
        }

        if ($this->hasSameConfiguration) {
            $newEndDate = $series->repeatOptions()->terminationDate();

            // remove all instances past the new end date
            return $instance->startDate()->greaterThan($newEndDate);
        }

        // remove all current instances, which now have an incompatible configuration
        return $instance->startDate()->greaterThan($this->earliestDateToKeep($series));
    }
}

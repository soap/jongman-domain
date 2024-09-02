<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Domain\ExistingReservationSeries;
use Soap\Jongman\Core\Domain\Reservation;

interface SeriesUpdateScopeInterface
{
    /**
     * @param  ExistingReservationSeries  $series
     * @return Reservation[]
     */
    public function instances($series);

    /**
     * @return bool
     */
    public function requiresNewSeries();

    /**
     * @return string
     */
    public function getScope();

    /**
     * @param  ExistingReservationSeries  $series
     * @return RepeatOptionsInterface
     */
    public function getRepeatOptions($series);

    /**
     * @param  ExistingReservationSeries  $series
     * @param  RepeatOptionsInterface  $repeatOptions
     * @return bool
     */
    public function canChangeRepeatTo($series, $repeatOptions);

    /**
     * @param  ExistingReservationSeries  $series
     * @param  Reservation  $instance
     * @return bool
     */
    public function shouldInstanceBeRemoved($series, $instance);
}

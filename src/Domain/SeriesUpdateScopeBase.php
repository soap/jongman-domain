<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Interfaces\RepeatOptionsInterface;
use Soap\Jongman\Core\Interfaces\SeriesUpdateScopeInterface;

abstract class SeriesUpdateScopeBase implements SeriesUpdateScopeInterface
{
    /**
     * @var SeriesDistinctionInterface
     */
    protected $series;

    protected function __construct() {}

    /**
     * @param  ExistingReservationSeries  $series
     * @param  Date  $compareDate
     * @return array
     */
    protected function allInstancesGreaterThan($series, $compareDate)
    {
        $instances = [];
        foreach ($series->_instances() as $instance) {
            if ($compareDate == null || $instance->startDate()->compare($compareDate) >= 0) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    abstract protected function earliestDateToKeep($series);

    public function getRepeatOptions($series)
    {
        return $series->repeatOptions();
    }

    /**
     * @param  ReservationSeries  $series
     * @param  RepeatOptionsInterface  $targetRepeatOptions
     * @return bool
     */
    public function canChangeRepeatTo($series, $targetRepeatOptions)
    {
        return ! $targetRepeatOptions->equals($series->repeatOptions());
    }

    public function shouldInstanceBeRemoved($series, $instance)
    {
        return $instance->startDate()->greaterThan($this->earliestDateToKeep($series));
    }
}

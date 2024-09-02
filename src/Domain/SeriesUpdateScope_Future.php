<?php

namespace Soap\Jongman\Core\Domain;

class SeriesUpdateScope_Future extends SeriesUpdateScopeBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getScope()
    {
        return SeriesUpdateScope::FutureInstances;
    }

    public function Instances($series)
    {
        return $this->allInstancesGreaterThan($series, $this->earliestDateToKeep($series));
    }

    public function earliestDateToKeep($series)
    {
        return $series->currentInstance()->startDate();
    }

    public function requiresNewSeries()
    {
        return true;
    }
}

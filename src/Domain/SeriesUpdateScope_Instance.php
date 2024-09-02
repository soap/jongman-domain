<?php

namespace Soap\Jongman\Core\Domain;

class SeriesUpdateScope_Instance extends SeriesUpdateScopeBase
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getScope()
    {
        return SeriesUpdateScope::ThisInstance;
    }

    public function instances($series)
    {
        return [$series->currentInstance()];
    }

    public function requiresNewSeries()
    {
        return true;
    }

    public function earliestDateToKeep($series)
    {
        return $series->currentInstance()->startDate();
    }

    public function getRepeatOptions($series)
    {
        return new RepeatNone;
    }

    public function canChangeRepeatTo($series, $targetRepeatOptions)
    {
        return $targetRepeatOptions->equals(new RepeatNone);
    }

    public function shouldInstanceBeRemoved($series, $instance)
    {
        return false;
    }
}

<?php

namespace Soap\Jongman\Core\Domain;

class SeriesUpdateScope
{
    private function __construct() {}

    public const ThisInstance = 'this';

    public const FullSeries = 'full';

    public const FutureInstances = 'future';

    public static function createStrategy($seriesUpdateScope)
    {
        switch ($seriesUpdateScope) {
            case SeriesUpdateScope::ThisInstance:
                return new SeriesUpdateScope_Instance;
                break;
            case SeriesUpdateScope::FullSeries:
                return new SeriesUpdateScope_Full;
                break;
            case SeriesUpdateScope::FutureInstances:
                return new SeriesUpdateScope_Future;
                break;
            default:
                throw new Exception('Unknown seriesUpdateScope requested');
        }
    }

    /**
     * @param  string  $updateScope
     * @return bool
     */
    public static function isValid($updateScope)
    {
        return $updateScope == SeriesUpdateScope::FullSeries ||
                $updateScope == SeriesUpdateScope::ThisInstance ||
                $updateScope == SeriesUpdateScope::FutureInstances;
    }
}

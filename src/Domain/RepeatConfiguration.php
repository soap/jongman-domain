<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\NullDate;

class RepeatConfiguration
{
    /**
     * @var string
     */
    public $Type;

    /**
     * @var string
     */
    public $Interval;

    /**
     * @var Date
     */
    public $TerminationDate;

    /**
     * @var array
     */
    public $Weekdays;

    /**
     * @var string
     */
    public $MonthlyType;

    /**
     * @param  string  $repeatType
     * @param  string  $configurationString
     * @return RepeatConfiguration
     */
    public static function create($repeatType, $configurationString)
    {
        $allparts = explode('|', $configurationString);
        $configParts = [];

        if (! empty($allparts[0])) {
            foreach ($allparts as $part) {
                $keyValue = explode('=', $part);

                if (! empty($keyValue[0])) {
                    $configParts[$keyValue[0]] = $keyValue[1];
                }
            }
        }

        $config = new RepeatConfiguration;
        $config->type = empty($repeatType) ? RepeatType::None : $repeatType;

        $config->interval = self::get($configParts, 'interval');
        $config->setTerminationDate(self::get($configParts, 'termination'));
        $config->setWeekdays(self::get($configParts, 'days'));
        $config->MonthlyType = self::get($configParts, 'type');

        return $config;
    }

    protected function __construct() {}

    private static function get($array, $key)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        return null;
    }

    private function setTerminationDate($terminationDateString)
    {
        if (! empty($terminationDateString)) {
            $this->TerminationDate = Date::fromDatabase($terminationDateString);
        } else {
            $this->TerminationDate = NullDate::instance();
        }
    }

    private function setWeekdays($weekdays)
    {
        if ($weekdays != null && $weekdays != '') {
            $this->Weekdays = explode(',', $weekdays);
        }
    }
}

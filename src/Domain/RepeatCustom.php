<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Common\NullDate;
use Soap\Jongman\Core\Interfaces\RepeatOptionsInterface;

class RepeatCustom implements RepeatOptionsInterface
{
    /**
     * @var Date[]
     */
    private $repeatDates;

    /**
     * @param  Date[]  $repeatDates
     */
    public function __construct($repeatDates)
    {
        $this->repeatDates = $repeatDates;
    }

    public function getDates(DateRange $startingDates)
    {
        $duration = $startingDates->Duration();
        $ranges = [];
        foreach ($this->repeatDates as $date) {
            $repeatStart = $date->setTime($startingDates->getBegin()->getTime());
            $repeatEnd = $repeatStart->applyDifference($duration);
            $ranges[] = new DateRange($repeatStart, $repeatEnd);
        }

        return $ranges;
    }

    public function configurationString()
    {
        return '';
    }

    public function repeatType()
    {
        return RepeatType::Custom;
    }

    public function terminationDate()
    {
        return new NullDate;
    }

    public function equals(RepeatOptionsInterface $repeatOptions)
    {
        return get_class($this) == get_class($repeatOptions) && $this->datesEqual($repeatOptions);
    }

    public function hasSameConfigurationAs(RepeatOptionsInterface $repeatOptions)
    {
        return $this->equals($repeatOptions);
    }

    private function datesEqual(RepeatCustom $other)
    {
        if (count($this->repeatDates) != count($other->repeatDates)) {
            return false;
        }

        for ($i = 0; $i < count($this->repeatDates); $i++) {
            if (! $this->repeatDates[$i]->equals($other->repeatDates[$i])) {
                return false;
            }
        }

        return true;
    }
}

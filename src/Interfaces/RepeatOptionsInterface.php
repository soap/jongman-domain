<?php

namespace Soap\Jongman\Core\Interfaces;

use Soap\Jongman\Core\Common\DateRange;

interface RepeatOptionsInterface
{
    /**
     * Gets array of DateRange objects
     *
     * @return array|DateRange[]
     */
    public function getDates(DateRange $startingDates);

    public function configurationString();

    public function repeatType();

    public function equals(RepeatOptionsInterface $repeatOptions);

    public function hasSameConfigurationAs(RepeatOptionsInterface $repeatOptions);

    public function terminationDate();
}

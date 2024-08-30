<?php

namespace Soap\Jongman\Core\Interfaces;

interface RepeatOptionsCompositeInterface
{
    /**
     * @return string
     */
    public function getRepeatType();

    /**
     * @return string|null
     */
    public function getRepeatInterval();

    /**
     * @return int[]|null
     */
    public function getRepeatWeekdays();

    /**
     * @return string|null
     */
    public function getRepeatMonthlyType();

    /**
     * @return string|null
     */
    public function getRepeatTerminationDate();

    /**
     * @return string[]
     */
    public function getRepeatCustomDates();
}

<?php

namespace Soap\Jongman\Core\Interfaces;

interface LayoutFactoryInterface
{
    /**
     * @return ScheduleLayoutInterface
     */
    public function createLayout();

    /**
     * @param  ScheduleRepositoryInterface  $repository
     * @param  int  $scheduleId
     * @return IScheduleLayout
     */
    //public function createCustomLayout(ScheduleRepositoryInterface $repository, $scheduleId);
}

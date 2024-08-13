<?php

namespace Soap\Jongman\Core\Factories;

use Soap\Jongman\Core\Domain\ScheduleLayout;
use Soap\Jongman\Core\Interfaces\LayoutFactoryInterface;

class ScheduleLayoutFactory implements LayoutFactoryInterface
{
    public function __construct(private $targetTimezone = null) {}

    public function createLayout()
    {
        return new ScheduleLayout($this->targetTimezone);
    }

    public function createCustomLayout() {}
}

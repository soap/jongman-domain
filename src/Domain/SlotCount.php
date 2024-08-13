<?php

namespace Soap\Jongman\Core\Domain;

class SlotCount
{
    public $offPeak = 0;

    public $peak = 0;

    public function __construct($offPeak, $peak)
    {
        $this->offPeak = $offPeak;
        $this->peak = $peak;
    }
}

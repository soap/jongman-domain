<?php

namespace Soap\Jongman\Core\Domain;

use Soap\Jongman\Core\Common\Time;

/**
 * Parses a layout string into a ScheduleLayout object
 */
class LayoutParser
{
    private $layout;

    private $timezone;

    public function __construct($timezone)
    {
        $this->layout = new ScheduleLayout($timezone);
        $this->timezone = $timezone;
    }

    /**
     * @param  string  $reservableSlots  multiple line string of slots
     * @param  int|null  $dayOfWeek
     */
    public function addReservable($reservableSlots, $dayOfWeek = null)
    {
        $cb = [$this, 'appendPeriod'];

        $this->parseSlots($reservableSlots, $dayOfWeek, $cb);
    }

    /**
     * @param  string  $blockedSlots  multiple line string of slots
     * @param  int|null  $dayOfWeek
     */
    public function addBlocked($blockedSlots, $dayOfWeek = null)
    {
        $cb = [$this, 'appendBlocked'];

        $this->parseSlots($blockedSlots, $dayOfWeek, $cb);
    }

    public function getLayout()
    {
        return $this->layout;
    }

    private function appendPeriod($start, $end, $label, $dayOfWeek = null)
    {
        $this->layout->appendPeriod(
            Time::parse($start, $this->timezone),
            Time::parse($end, $this->timezone),
            $label,
            $dayOfWeek
        );
    }

    private function appendBlocked($start, $end, $label, $dayOfWeek = null)
    {
        $this->layout->appendBlockedPeriod(
            Time::parse($start, $this->timezone),
            Time::parse($end, $this->timezone),
            $label,
            $dayOfWeek
        );
    }

    /**
     * @param  string  $allSlots  multiple line string of slots
     * @param  int|null  $dayOfWeek
     * @param  callable  $callback
     */
    private function parseSlots($allSlots, $dayOfWeek, $callback)
    {
        $trimmedSlots = trim($allSlots);
        // \R is a Unicode newline sequence can be \r\n, \r, or \n
        $lines = preg_split("/\R/", $trimmedSlots, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $slotLine) {
            $label = null;
            $parts = preg_split(
                '/(\d?\d:\d\d\s*\-\s*\d?\d:\d\d)(.*)/',
                trim($slotLine),
                -1,
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            );
            $times = explode('-', $parts[0]);
            $start = trim($times[0]);
            $end = trim($times[1]);

            if (count($parts) > 1) {
                $label = trim($parts[1]);
            }

            call_user_func($callback, $start, $end, $label, $dayOfWeek);
        }
    }
}

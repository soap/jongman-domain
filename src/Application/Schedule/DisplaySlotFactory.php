<?php

namespace Soap\Jongman\Core\Application\Schedule;

use Soap\Jongman\Core\Domain\SchedulePeriod;

class DisplaySlotFactory
{
    public function getFunction(SchedulePeriod $slot, $accessAllowed = false, $functionSuffix = '')
    {
        if (! $accessAllowed) {
            return "displayRestricted$functionSuffix";
        } else {
            if ($slot->isPastDate() && ! $this->userHasAdminRights()) {
                return "displayPastTime$functionSuffix";
            } else {
                if ($slot->isReservable()) {
                    return "displayReservable$functionSuffix";
                } else {
                    return "displayUnreservable$functionSuffix";
                }
            }
        }

        return "displayUnreservable$functionSuffix";
    }

    private function userHasAdminRights()
    {
        return true; //ServiceLocator::GetServer()->GetUserSession()->IsAdmin;
    }

    private function isMyReservation(ReservationSlotInterface $slot)
    {
        //$mySession = ServiceLocator::GetServer()->GetUserSession();
        return false; //$slot->isOwnedBy($mySession);
    }

    private function isAdminFor(ReservationSlotInterface $slot)
    {
        //$mySession = ServiceLocator::GetServer()->GetUserSession();
        return false; //$mySession->IsAdmin || $mySession->IsAdminForGroup($slot->OwnerGroupIds());
    }

    private function amIParticipating(ReservationSlotInterface $slot)
    {
        //$mySession = ServiceLocator::GetServer()->GetUserSession();
        return $slot->isParticipating($mySession);
    }

    /**
     * @param  SchedulePeriod[]  $periods
     * @param  Date  $start
     * @param  Date  $end
     * @return string
     */
    public function getCondensedPeriodLabel($periods, $start, $end)
    {
        foreach ($periods as $period) {
            if ($period->isLabelled()) {
                if ($period->beginDate()->equals($start)) {
                    return $period->label().' - '.$period->labelEnd();
                }
            }
        }
        $format = 'H:i'; // Resources::GetInstance()->GetDateFormat('period_time');

        return $start->format($format).' - '.$end->format($format);
    }
}

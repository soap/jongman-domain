<?php

namespace Soap\Jongman\Core\Domain\Values;

class ReservationPastTimeConstraint
{
    public static function IsPast(Date $begin, Date $end): bool
    {
        $constraint = JongmanFactory::getConfige()->get(
            'jongman.reservation.start_time_constraint'
        );

        if (empty($constraint)) {
            $constraint = ReservationStartTimeConstraint::_DEFAULT;
        }

        if ($constraint == ReservationStartTimeConstraint::NONE) {
            return false;
        }

        if ($constraint == ReservationStartTimeConstraint::CURRENT) {
            return $end->LessThan(Date::Now());
        }

        return $begin->LessThan(Date::Now());
    }
}

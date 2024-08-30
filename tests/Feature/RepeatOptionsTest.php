<?php

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Domain\RepeatDaily;
use Soap\Jongman\Core\Domain\RepeatDayOfMonth;
use Soap\Jongman\Core\Domain\RepeatNone;
use Soap\Jongman\Core\Domain\RepeatWeekDayOfMonth;
use Soap\Jongman\Core\Domain\RepeatWeekly;
use Soap\Jongman\Core\Domain\RepeatYearly;
use Soap\Jongman\Core\Factories\RepeatOptionsFactory;


test('no repeat never has repeat dates', function () {
    $duration = DateRange::create('2020-01-01', '2020-01-02', 'UTC');
    
    $repeatOptions = new RepeatNone();

    $dates = $repeatOptions->getDates($duration);

    expect($dates)->toBe([]);
});

test('termination date is inclusive', function () {
    $reservationStart = Date::parse('2020-01-01 08:00', 'UTC');
    $reservationEnd = Date::parse('2020-01-01 10:30', 'UTC');
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 1;
    $terminiationDate = Date::parse('2020-01-05', 'UTC');

    $repeatOptions = new RepeatDaily($interval, $terminiationDate);
    $repeatedDates = $repeatOptions->getDates($duration);

    expect(count($repeatedDates))->toBe(4);
});

test('factory can create RepeatDailyOptions', function () {
    $factory = new RepeatOptionsFactory();

    $options = $factory->create('daily', 1, null, null, null, []);

    expect($options)->toBeInstanceOf(RepeatDaily::class);
});


test('factory can create RepeatWeeklyOptions', function () {
    $factory = new RepeatOptionsFactory();

    $options = $factory->create('weekly', 1, null, [], null, []);

    expect($options)->toBeInstanceOf(RepeatWeekly::class);
});

test('factory can create DayOfMonthRepeatOptions', function() {
    $factory = new RepeatOptionsFactory();
    $options = $factory->create('monthly', 1, null, null, 'dayOfMonth', []);

    expect($options)->toBeInstanceOf(RepeatDayOfMonth::class);
});

test('factory can create WeekDayOfMonthRepeatOptions', function() {
    $factory = new RepeatOptionsFactory();
    $options = $factory->create('monthly', 1, null, null, null, []);
    
    expect($options)->toBeInstanceOf(RepeatWeekDayOfMonth::class);
});

test('factory can create YearlyRepeatOptions', function(){
    $factory = new RepeatOptionsFactory();
    $options = $factory->create('yearly', 1, null, null, null, []);

    expect($options)->toBeInstanceOf(RepeatYearly::class);
});

test('factory can create NoRepeatOptions', function(){
    $factory = new RepeatOptionsFactory();
    $options = $factory->create('none', 1, null, null, null, []);

    expect($options)->toBeInstanceOf(RepeatNone::class);
});



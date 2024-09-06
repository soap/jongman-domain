<?php

use Soap\Jongman\Core\Common\Date;
use Soap\Jongman\Core\Common\DateRange;
use Soap\Jongman\Core\Common\NullDate;
use Soap\Jongman\Core\Domain\RepeatConfiguration;
use Soap\Jongman\Core\Domain\RepeatCustom;
use Soap\Jongman\Core\Domain\RepeatDaily;
use Soap\Jongman\Core\Domain\RepeatDayOfMonth;
use Soap\Jongman\Core\Domain\RepeatMonthlyType;
use Soap\Jongman\Core\Domain\RepeatNone;
use Soap\Jongman\Core\Domain\RepeatType;
use Soap\Jongman\Core\Domain\RepeatWeekDayOfMonth;
use Soap\Jongman\Core\Domain\RepeatWeekly;
use Soap\Jongman\Core\Domain\RepeatYearly;
use Soap\Jongman\Core\Factories\RepeatOptionsFactory;

test('no repeat never has repeat dates', function () {
    $duration = DateRange::create('2020-01-01', '2020-01-02', 'UTC');

    $repeatOptions = new RepeatNone;

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

test('repeat daily create recurrence every specified day until end', function () {
    $reservationStart = Date::parse('2010-02-12 08:30', 'UTC');
    $reservationEnd = Date::parse('2010-02-12 10:30', 'UTC');
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 2;
    $terminiationDate = Date::parse('2010-04-02', 'UTC');

    $repeatOptions = new RepeatDaily($interval, $terminiationDate);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 8 + 15 + 1;
    $firstDate = DateRange::create('2010-02-14 08:30', '2010-02-14 10:30', 'UTC');
    $lastDate = DateRange::create('2010-04-01 08:30', '2010-04-01 10:30', 'UTC');

    expect(count($repeatedDates))->toBe($totalDates);

    expect($firstDate->equals($repeatedDates[0]), $firstDate->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($lastDate->equals($repeatedDates[$totalDates - 1]), $lastDate->toString().' '.$repeatedDates[$totalDates - 1]->toString())->toBe(true);
});

test('repeat weekly creates recurrence on specified days every interval until end', function () {
    $timezone = 'UTC';
    $reservationStart = Date::parse('2010-02-11 08:30', $timezone);
    $reservationEnd = Date::parse('2010-02-11 10:30', $timezone);
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 2;
    $terminiationDate = Date::parse('2010-04-01', $timezone);
    $daysOfWeek = [1, 3, 5];

    $repeatOptions = new RepeatWeekly($interval, $terminiationDate, $daysOfWeek);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 10;
    $firstDate = DateRange::create('2010-02-12 08:30', '2010-02-12 10:30', $timezone);
    $secondDate = DateRange::create('2010-02-22 08:30', '2010-02-22 10:30', $timezone);
    $thirdDate = DateRange::create('2010-02-24 08:30', '2010-02-24 10:30', $timezone);
    $forthDate = DateRange::create('2010-02-26 08:30', '2010-02-26 10:30', $timezone);
    $lastDate = DateRange::create('2010-03-26 08:30', '2010-03-26 10:30', $timezone);

    expect(count($repeatedDates))->toBe($totalDates);

    expect($firstDate->equals($repeatedDates[0]), $firstDate->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($secondDate->equals($repeatedDates[1]), $secondDate->toString().' '.$repeatedDates[1]->toString())->toBe(true);
    expect($thirdDate->equals($repeatedDates[2]), $thirdDate->toString().' '.$repeatedDates[2]->toString())->toBe(true);
    expect($forthDate->equals($repeatedDates[3]), $forthDate->toString().' '.$repeatedDates[3]->toString())->toBe(true);
    expect($lastDate->equals($repeatedDates[$totalDates - 1]), $lastDate->toString().' '.$repeatedDates[$totalDates - 1]->toString())->toBe(true);
});

test('repeat Weekly creates recurrence on sSingle day every interval until end', function () {
    $timezone = 'Asia/Bangkok';
    $reservationStart = Date::parse('2010-02-11 08:30', $timezone);
    $reservationEnd = Date::parse('2010-02-11 10:30', $timezone);
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 1;
    $terminiationDate = Date::parse('2010-04-01', $timezone);
    $daysOfWeek = [3];

    $repeatOptions = new RepeatWeekly($interval, $terminiationDate, $daysOfWeek);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 7;
    $firstDate = DateRange::create('2010-02-17 08:30', '2010-02-17 10:30', $timezone);
    $forthDate = DateRange::create('2010-03-10 08:30', '2010-03-10 10:30', $timezone);
    $lastDate = DateRange::create('2010-03-31 08:30', '2010-03-31 10:30', $timezone);

    expect(count($repeatedDates))->toBe($totalDates);
    expect($firstDate->equals($repeatedDates[0]), $firstDate->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($forthDate->equals($repeatedDates[3]), $forthDate->toString().' '.$repeatedDates[3]->toString())->toBe(true);
    expect($lastDate->equals($repeatedDates[$totalDates - 1]), $lastDate->toString().' '.$repeatedDates[$totalDates - 1]->toString())->toBe(true);
});

test('monthly repeatDayOfMonth when day is in all months', function () {
    $timezone = 'Asia/Bangkok';
    $reservationStart = Date::parse('2010-02-11 08:30', $timezone);
    $reservationEnd = Date::parse('2010-02-11 10:30', $timezone);
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 1;
    $terminiationDate = Date::parse('2011-10-01', $timezone);

    $repeatOptions = new RepeatDayOfMonth($interval, $terminiationDate);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 19;
    $firstDate = DateRange::create('2010-03-11 08:30', '2010-03-11 10:30', $timezone);
    $secondDate = DateRange::create('2010-04-11 08:30', '2010-04-11 10:30', $timezone);
    $lastDate = DateRange::create('2011-09-11 08:30', '2011-09-11 10:30', $timezone);

    expect(count($repeatedDates))->toBe($totalDates);
    expect($firstDate->equals($repeatedDates[0]), $firstDate->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($secondDate->equals($repeatedDates[1]), $secondDate->toString().' '.$repeatedDates[1]->toString())->toBe(true);
    expect($lastDate->equals($repeatedDates[$totalDates - 1]), $lastDate->toString().' '.$repeatedDates[$totalDates - 1]->toString())->toBe(true);
});

test('monthly repeatDayOfMonth when day isnot in all months', function () {
    $timezone = 'Asia/Bangkok';
    $reservationStart = Date::parse('2018-01-31 08:30', $timezone);
    $reservationEnd = Date::parse('2018-01-31 10:30', $timezone);
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 1;
    $terminiationDate = Date::parse('2018-12-31', $timezone);

    $repeatOptions = new RepeatDayOfMonth($interval, $terminiationDate);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 6;
    $date1 = DateRange::create('2018-03-31 08:30', '2018-03-31 10:30', $timezone);
    $date2 = DateRange::create('2018-05-31 08:30', '2018-05-31 10:30', $timezone);
    $date3 = DateRange::create('2018-07-31 08:30', '2018-07-31 10:30', $timezone);
    $date4 = DateRange::create('2018-08-31 08:30', '2018-08-31 10:30', $timezone);
    $date5 = DateRange::create('2018-10-31 08:30', '2018-10-31 10:30', $timezone);
    $date6 = DateRange::create('2018-12-31 08:30', '2018-12-31 10:30', $timezone);

    expect(count($repeatedDates))->toBe($totalDates);
    expect($date1->equals($repeatedDates[0]), $date1->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($date2->equals($repeatedDates[1]), $date2->toString().' '.$repeatedDates[1]->toString())->toBe(true);
    expect($date3->equals($repeatedDates[2]), $date3->toString().' '.$repeatedDates[2]->toString())->toBe(true);
    expect($date4->equals($repeatedDates[3]), $date4->toString().' '.$repeatedDates[3]->toString())->toBe(true);
    expect($date5->equals($repeatedDates[4]), $date5->toString().' '.$repeatedDates[4]->toString())->toBe(true);
    expect($date6->equals($repeatedDates[5]), $date6->toString().' '.$repeatedDates[5]->toString())->toBe(true);
});

test('monthly RepeatDayOfWeek when week is in all months', function () {
    //http://www.timeanddate.com/calendar/
    $timezone = 'Asia/Bangkok';
    $reservationStart = Date::parse('2010-03-01 08:30', $timezone); // first monday
    $reservationEnd = Date::parse('2010-03-01 10:30', $timezone);
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 1;
    $terminiationDate = Date::parse('2011-10-01', $timezone);

    $repeatOptions = new RepeatWeekDayOfMonth($interval, $terminiationDate);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 18;
    $firstDate = DateRange::create('2010-04-05 08:30', '2010-04-05 10:30', $timezone);
    $secondDate = DateRange::create('2010-05-03 08:30', '2010-05-03 10:30', $timezone);
    $lastDate = DateRange::create('2011-09-05 08:30', '2011-09-05 10:30', $timezone);

    expect(count($repeatedDates))->toBe($totalDates);
    expect($firstDate->equals($repeatedDates[0]), $firstDate->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($secondDate->equals($repeatedDates[1]), $secondDate->toString().' '.$repeatedDates[1]->toString())->toBe(true);
    expect($lastDate->equals($repeatedDates[$totalDates - 1]), $lastDate->toString().' '.$repeatedDates[$totalDates - 1]->toString())->toBe(true);
});

test('monthly repeatDayOfWeek whenWeek isnot in all months', function () {
    //http://www.timeanddate.com/calendar/
    $timezone = 'Asia/Bangkok';
    $reservationStart = Date::parse('2010-03-31 08:30', $timezone); // fifth wednesday
    $reservationEnd = Date::parse('2010-03-31 10:30', $timezone);
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 1;
    $terminiationDate = Date::parse('2010-10-01', $timezone);

    $repeatOptions = new RepeatWeekDayOfMonth($interval, $terminiationDate);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 2;
    $firstDate = DateRange::create('2010-06-30 08:30', '2010-06-30 10:30', $timezone);
    $lastDate = DateRange::create('2010-09-29 08:30', '2010-09-29 10:30', $timezone);

    expect(count($repeatedDates))->toBe($totalDates);
    expect($firstDate->equals($repeatedDates[0]), $firstDate->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($lastDate->equals($repeatedDates[$totalDates - 1]), $lastDate->toString().' '.$repeatedDates[$totalDates - 1]->toString())->toBe(true);
});

test('yearly repeat works correctly', function () {
    $timezone = 'Asia/Bangkok';
    $reservationStart = Date::parse('2010-03-31 08:30', $timezone); // fifth wednesday
    $reservationEnd = Date::parse('2010-03-31 10:30', $timezone);
    $duration = new DateRange($reservationStart, $reservationEnd);

    $interval = 2;
    $terminiationDate = Date::parse('2016-03-30', $timezone);

    $repeatOptions = new RepeatYearly($interval, $terminiationDate);
    $repeatedDates = $repeatOptions->getDates($duration);

    $totalDates = 2;
    $firstDate = DateRange::create('2012-03-31 08:30', '2012-03-31 10:30', $timezone);
    $lastDate = DateRange::create('2014-03-31 08:30', '2014-03-31 10:30', $timezone);

    expect(count($repeatedDates))->toBe($totalDates);
    expect($firstDate->equals($repeatedDates[0]), $firstDate->toString().' '.$repeatedDates[0]->toString())->toBe(true);
    expect($lastDate->equals($repeatedDates[$totalDates - 1]), $lastDate->toString().' '.$repeatedDates[$totalDates - 1]->toString())->toBe(true);
});

test('factory can create RepeatDailyOptions', function () {
    $factory = new RepeatOptionsFactory;

    $options = $factory->create('daily', 1, null, null, null, []);

    expect($options)->toBeInstanceOf(RepeatDaily::class);
});

test('factory can create RepeatWeeklyOptions', function () {
    $factory = new RepeatOptionsFactory;

    $options = $factory->create('weekly', 1, null, [], null, []);

    expect($options)->toBeInstanceOf(RepeatWeekly::class);
});

test('factory can create DayOfMonthRepeatOptions', function () {
    $factory = new RepeatOptionsFactory;
    $options = $factory->create('monthly', 1, null, null, 'dayOfMonth', []);

    expect($options)->toBeInstanceOf(RepeatDayOfMonth::class);
});

test('factory can create WeekDayOfMonthRepeatOptions', function () {
    $factory = new RepeatOptionsFactory;
    $options = $factory->create('monthly', 1, null, null, null, []);

    expect($options)->toBeInstanceOf(RepeatWeekDayOfMonth::class);
});

test('factory can create YearlyRepeatOptions', function () {
    $factory = new RepeatOptionsFactory;
    $options = $factory->create('yearly', 1, null, null, null, []);

    expect($options)->toBeInstanceOf(RepeatYearly::class);
});

test('factory can create NoRepeatOptions', function () {
    $factory = new RepeatOptionsFactory;
    $options = $factory->create('none', 1, null, null, null, []);

    expect($options)->toBeInstanceOf(RepeatNone::class);
});

test('configuration string can be serialized', function () {

    $terminationDate = Date::parse('2010-12-12 01:06:07', 'UTC');
    $dateString = $terminationDate->toDatabase();
    $interval = 10;

    // none
    $config = RepeatConfiguration::create(RepeatType::None, '');
    expect(RepeatType::None)->toEqual($config->type);

    // daily
    $daily = new RepeatDaily($interval, $terminationDate);
    $config = RepeatConfiguration::create($daily->repeatType(), $daily->configurationString());

    expect(RepeatType::Daily)->toBe($config->type);
    expect($config->interval)->toEqual(10);
    expect($config->terminationDate)->toEqual($terminationDate);

    // weekly
    $weekdays = [1, 3, 4, 5];
    $weekly = new RepeatWeekly($interval, $terminationDate, $weekdays);
    $config = RepeatConfiguration::create($weekly->repeatType(), $weekly->configurationString());
    $this->assertEquals(RepeatType::Weekly, $config->type);
    $this->assertEquals($terminationDate, $config->terminationDate);
    $this->assertEquals($weekdays, $config->weekdays);

    // day of month
    $dayOfMonth = new RepeatDayOfMonth($interval, $terminationDate);
    $config = RepeatConfiguration::create($dayOfMonth->repeatType(), $dayOfMonth->configurationString());
    $this->assertEquals(RepeatType::Monthly, $config->type);
    $this->assertEquals($terminationDate, $config->terminationDate);
    $this->assertEquals(RepeatMonthlyType::DayOfMonth, $config->monthlyType);

    // weekday of month
    $weekOfMonth = new RepeatWeekDayOfMonth($interval, $terminationDate);
    $config = RepeatConfiguration::create($weekOfMonth->repeatType(), $weekOfMonth->configurationString());
    $this->assertEquals(repeatType::Monthly, $config->type);
    $this->assertEquals($terminationDate, $config->terminationDate);
    $this->assertEquals(RepeatMonthlyType::DayOfWeek, $config->monthlyType);

    // yearly
    $yearly = new RepeatYearly($interval, $terminationDate);
    $config = RepeatConfiguration::create($yearly->repeatType(), $yearly->configurationString());
    $this->assertEquals(RepeatType::Yearly, $config->type);
    $this->assertEquals(10, $config->interval);
    $this->assertEquals($terminationDate, $config->terminationDate);

    // custom
    $custom = new RepeatCustom([]);
    $config = RepeatConfiguration::create($custom->repeatType(), $custom->configurationString());
    expect($config->type)->toEqual(RepeatType::Custom);
    expect($config->interval)->toEqual('');
    expect($config->terminationDate)->toEqual(new NullDate);
});

test('repeat when repeating day before first day of month', function () {
    // 2012-08 starts on wednesday, the 1st
    $firstSunday = Date::parse('2012-08-05');
    $firstTuesday = Date::parse('2012-08-07');
    $secondTuesday = Date::parse('2012-08-14');
    $firstWednesday = Date::parse('2012-08-01');
    $secondWednesday = Date::parse('2012-08-08');
    $weekOfMonth = new RepeatWeekDayOfMonth(1, Date::parse('2013-01-01'));

    $repeatDatesForFirstSun = $weekOfMonth->getDates(new DateRange($firstSunday, $firstSunday));
    $repeatDatesForFirstTue = $weekOfMonth->getDates(new DateRange($firstTuesday, $firstTuesday));
    $repeatDatesForSecondTue = $weekOfMonth->getDates(new DateRange($secondTuesday, $secondTuesday));
    $repeatDatesForFirstWed = $weekOfMonth->getDates(new DateRange($firstWednesday, $firstWednesday));
    $repeatDatesForSecondWed = $weekOfMonth->getDates(new DateRange($secondWednesday, $secondWednesday));

    $firstRepeatedSun = $repeatDatesForFirstSun[0]->getBegin();
    $firstRepeatedTue = $repeatDatesForFirstTue[0]->getBegin();
    $secondRepeatedTue = $repeatDatesForSecondTue[0]->getBegin();
    $firstRepeatedWed = $repeatDatesForFirstWed[0]->getBegin();
    $secondRepeatedWed = $repeatDatesForSecondWed[0]->getBegin();

    $this->assertTrue(Date::parse('2012-09-02')->equals($firstRepeatedSun), $firstRepeatedSun->__toString());
    $this->assertTrue(Date::parse('2012-09-04')->equals($firstRepeatedTue), $firstRepeatedTue->__toString());
    $this->assertTrue(Date::parse('2012-09-05')->equals($firstRepeatedWed), $firstRepeatedWed->__toString());
    $this->assertTrue(Date::parse('2012-09-11')->equals($secondRepeatedTue), $secondRepeatedTue->__toString());
    $this->assertTrue(Date::parse('2012-09-12')->equals($secondRepeatedWed), $secondRepeatedWed->__toString());
});

test('repeating across European daylight savings', function () {
    $firstWednesday = DateRange::create('2013-02-06 09:00', '2013-02-06 10:00', 'Europe/London');
    $firstWednesdayRepeat = new RepeatWeekDayOfMonth(1, Date::parse('2013-10-01', 'Europe/London'));

    /** @var $dates DateRange[] */
    $dates = $firstWednesdayRepeat->getDates($firstWednesday);

    foreach ($dates as $date) {
        $date = $date->toTimezone('Europe/London');

        expect($date->getBegin()->hour())->toEqual(9);
        // $this->assertEquals(9, $date->getBegin()->hour(), $date->__toString());
        expect($date->getEnd()->hour())->toEqual(10);
        expect($date->getBegin()->weekday())->toEqual(3);
    }
});

test('repeating across European daylightsavings with other example', function () {
    $firstWednesday = DateRange::create('2013-03-06 13:00:00', '2013-03-06 14:00:00', 'Europe/London');
    $firstWednesdayRepeat = new RepeatWeekDayOfMonth(1, Date::parse('2013-12-24', 'Europe/London'));

    /** @var $dates DateRange[] */
    $dates = $firstWednesdayRepeat->getDates($firstWednesday);

    foreach ($dates as $date) {
        $date = $date->toTimezone('Europe/London');
        $this->assertEquals(13, $date->getBegin()->hour(), $date->__toString());

        expect($date->getEnd()->hour())->toEqual(14);
        expect($date->getBegin()->weekday())->toEqual(3);
    }
});

test('repeat first FFriday when the firs dDay of the month is a Friday', function () {
    $firstFriday = DateRange::create('2014-04-04 08:00', '2014-04-04 08:00', 'UTC');
    $repeat = new RepeatWeekDayOfMonth(1, Date::parse('2015-01-01', 'UTC'));

    /** @var $dates DateRange[] */
    $dates = $repeat->getDates($firstFriday);
    expect($dates[3]->getBegin()->day())->toBe('01');
});

test('Factory creates Custom RepeatOptions', function () {
    $factory = new RepeatOptionsFactory;
    $options = $factory->create('custom', null, null, null, null, []);
    expect($options)->toBeInstanceOf(RepeatCustom::class);
});

test('reepeat Custom works correctly', function () {
    $timezone = 'America/Chicago';
    $reservationDate = DateRange::create('2020-02-02 2:30', '2020-02-03 4:00', $timezone);
    $repeatDates = [new Date('2020-02-05', $timezone), new Date('2020-02-22', $timezone), new Date('2020-05-19', $timezone)];
    $repeat = new RepeatCustom($repeatDates);

    $dates = $repeat->getDates($reservationDate);

    expect(count($dates))->toBe(3);
    expect(DateRange::create('2020-02-05 2:30', '2020-02-06 4:00', $timezone))->toEqual($dates[0]);
    expect(DateRange::create('2020-02-22 2:30', '2020-02-23 4:00', $timezone))->toEqual($dates[1]);
    expect(DateRange::create('2020-05-19 2:30', '2020-05-20 4:00', $timezone))->toEqual($dates[2]);
});

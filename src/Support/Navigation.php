<?php
namespace App\Support;

use Cake\I18n\Time;
use Cake\I18n\Date;
use Cake\Network\Exception\NotFoundException;

/**
 * Class Navigation
 *
 * @package App\Support
 */
class Navigation
{

    /**
     * @param \Cake\I18n\Time $theDate
     * @param                $repeatFreq
     * @param                $skip
     *
     * @return \Cake\I18n\Time
     * @throws Exception
     */
    public function addPeriod(Time $theDate, $repeatFreq, $skip)
    {
        $date = clone $theDate;
        $add  = ($skip + 1);

        $functionMap = [
            '1D'      => 'addDays', 'daily' => 'addDays',
            '1W'      => 'addWeeks', 'weekly' => 'addWeeks', 'week' => 'addWeeks',
            '1M'      => 'addMonths', 'month' => 'addMonths', 'monthly' => 'addMonths', '3M' => 'addMonths',
            'quarter' => 'addMonths', 'quarterly' => 'addMonths', '6M' => 'addMonths', 'half-year' => 'addMonths',
            'year'    => 'addYears', 'yearly' => 'addYears',
        ];
        $modifierMap = [
            'quarter'   => 3,
            '3M'        => 3,
            'quarterly' => 3,
            '6M'        => 6,
            'half-year' => 6,
        ];

        if (!isset($functionMap[$repeatFreq])) {
			throw new NotFoundException('Cannot do addPeriod for $repeat_freq "' . $repeatFreq . '"');
        }
        if (isset($modifierMap[$repeatFreq])) {
            $add = $add * $modifierMap[$repeatFreq];
        }
        $function = $functionMap[$repeatFreq];
        $date->$function($add);

        return $date;
    }

    /**
     * @param \Cake\I18n\Time $theCurrentEnd
     * @param                $repeatFreq
     *
     * @return \Cake\I18n\Time
     * @throws Exception
     */
    public function endOfPeriod(Time $theCurrentEnd, $repeatFreq)
    {
        $currentEnd = clone $theCurrentEnd;

        $functionMap = [
            '1D'   => 'addDay', 'daily' => 'addDay',
            '1W'   => 'addWeek', 'week' => 'addWeek', 'weekly' => 'addWeek',
            '1M'   => 'addMonth', 'month' => 'addMonth', 'monthly' => 'addMonth',
            '3M'   => 'addMonths', 'quarter' => 'addMonths', 'quarterly' => 'addMonths', '6M' => 'addMonths', 'half-year' => 'addMonths',
            'year' => 'addYear', 'yearly' => 'addYear',
        ];
        $modifierMap = [
            'quarter'   => 3,
            '3M'        => 3,
            'quarterly' => 3,
            'half-year' => 6,
            '6M'        => 6,
        ];

        $subDay = ['week', 'weekly', '1W', 'month', 'monthly', '1M', '3M', 'quarter', 'quarterly', '6M', 'half-year', 'year', 'yearly'];

		if (!isset($functionMap[$repeatFreq])) {
			throw new NotFoundException('Cannot do endOfPeriod for $repeat_freq "' . $repeatFreq . '"');
		}
        $function = $functionMap[$repeatFreq];
        if (isset($modifierMap[$repeatFreq])) {
            $currentEnd->$function($modifierMap[$repeatFreq]);
        } else {
            $currentEnd->$function();
        }
        if (in_array($repeatFreq, $subDay)) {
            $currentEnd->subDay();
        }

        return $currentEnd;
    }

    /**
     *
     * @param \Cake\I18n\Time $theCurrentEnd
     * @param                 $repeatFreq
     * @param \Cake\I18n\Time $maxDate
     *
     * @return \Cake\I18n\Time
     */
    public function endOfX(Time $theCurrentEnd, $repeatFreq, Time $maxDate)
    {
        $functionMap = [
            'daily'     => 'endOfDay',
            'week'      => 'endOfWeek',
            'weekly'    => 'endOfWeek',
            'month'     => 'endOfMonth',
            'monthly'   => 'endOfMonth',
            'quarter'   => 'lastOfQuarter',
            'quarterly' => 'lastOfQuarter',
            'year'      => 'endOfYear',
            'yearly'    => 'endOfYear',
        ];

        $currentEnd = clone $theCurrentEnd;

        if (isset($functionMap[$repeatFreq])) {
            $function = $functionMap[$repeatFreq];
            $currentEnd->$function();

        }

        if ($currentEnd > $maxDate) {
            return clone $maxDate;
        }

        return $currentEnd;
    }

    /**
     * @param \Cake\I18n\Time $date
     * @param                $repeatFrequency
     *
     * @return string
     * @throws Exception
     */
    public function periodShow(Time $date, $repeatFrequency)
    {
        $formatMap = [
            'daily'   => '%e %B %Y',
            'week'    => 'Week %W, %Y',
            'weekly'  => 'Week %W, %Y',
            'quarter' => '%B %Y',
            'month'   => '%B %Y',
            'monthly' => '%B %Y',
            'year'    => '%Y',
            'yearly'  => '%Y',
		];

        if (isset($formatMap[$repeatFrequency])) {
            return $date->formatLocalized($formatMap[$repeatFrequency]);
        }
		throw new NotFoundException('No date formats for frequency "' . $repeatFrequency . '"!');
    }

    /**
     * @param \Cake\I18n\Time $theDate
     * @param                $repeatFreq
     *
     * @return \Cake\I18n\Time
     * @throws Exception
     */
    public function startOfPeriod(Time $theDate, $repeatFreq)
    {
        $date = clone $theDate;

        $functionMap = [
            '1D'        => 'startOfDay',
            'daily'     => 'startOfDay',
            '1W'        => 'startOfWeek',
            'week'      => 'startOfWeek',
            'weekly'    => 'startOfWeek',
            'month'     => 'startOfMonth',
            '1M'        => 'startOfMonth',
            'monthly'   => 'startOfMonth',
            '3M'        => 'firstOfQuarter',
            'quarter'   => 'firstOfQuarter',
            'quarterly' => 'firstOfQuarter',
            'year'      => 'startOfYear',
            'yearly'    => 'startOfYear',
        ];
        if (isset($functionMap[$repeatFreq])) {
            $function = $functionMap[$repeatFreq];
            $date->$function();

            return $date;
        }
        if ($repeatFreq == 'half-year' || $repeatFreq == '6M') {
            $month = $date->month;
            $date->startOfYear();
            if ($month >= 7) {
                $date->addMonths(6);
            }

            return $date;
        }
        throw new NotFoundException('Cannot do startOfPeriod for $repeat_freq "' . $repeatFreq . '"');
	}

    /**
     * @param \Cake\I18n\Time $theDate
     * @param                 $repeatFreq
     * @param int             $subtract
     *
     * @return \Cake\I18n\Time
     * @throws Exception
     */
    public function subtractPeriod(Time $theDate, $repeatFreq, $subtract = 1)
    {
        $date = clone $theDate;

        $functionMap = [
            'daily'   => 'subDays',
            'week'    => 'subWeeks',
            'weekly'  => 'subWeeks',
            'month'   => 'subMonths',
            'monthly' => 'subMonths',
            'year'    => 'subYears',
            'yearly'  => 'subYears',
        ];
        $modifierMap = [
            'quarter'   => 3,
            'quarterly' => 3,
            'half-year' => 6,
        ];
        if (isset($functionMap[$repeatFreq])) {
            $function = $functionMap[$repeatFreq];
            $date->$function($subtract);

            return $date;
        }
        if (isset($modifierMap[$repeatFreq])) {
            $subtract = $subtract * $modifierMap[$repeatFreq];
            $date->subMonths($subtract);

            return $date;
        }

        throw new NotFoundException('Cannot do subtractPeriod for $repeat_freq "' . $repeatFreq . '"');
    }

    /**
     * @param                 $range
     * @param \Cake\I18n\Time $start
     *
     * @return \Cake\I18n\Time
     * @throws Exception
     */
    public function updateEndDate($range, Time $start)
    {
        $functionMap = [
            '1D' => 'endOfDay',
            '1W' => 'endOfWeek',
            '1M' => 'endOfMonth',
            '3M' => 'lastOfQuarter',
            '1Y' => 'endOfYear',
        ];
        $end         = clone $start;

        if (isset($functionMap[$range])) {
            $function = $functionMap[$range];
            $end->$function();

            return $end;
        }
        if ($range == '6M') {
            if ($start->month >= 7) {
                $end->endOfYear();
            } else {
                $end->startOfYear()->addMonths(6);
            }

            return $end;
        }
        throw new NotFoundException('updateEndDate cannot handle $range "' . $range . '"');
    }

    /**
     * @param                 $range
     * @param \Cake\I18n\Time $start
     *
     * @return \Cake\I18n\Time
     * @throws Exception
     */
    public function updateStartDate($range, Time $start)
    {
        $functionMap = [
            '1D' => 'startOfDay',
            '1W' => 'startOfWeek',
            '1M' => 'startOfMonth',
            '3M' => 'firstOfQuarter',
            '1Y' => 'startOfYear',
        ];
        if (isset($functionMap[$range])) {
            $function = $functionMap[$range];
            $start->$function();

            return $start;
        }
        if ($range == '6M') {
            if ($start->month >= 7) {
                $start->startOfYear()->addMonths(6);
            } else {
                $start->startOfYear();
            }

            return $start;
        }
        throw new NotFoundException('updateStartDate cannot handle $range "' . $range . '"');
    }
}

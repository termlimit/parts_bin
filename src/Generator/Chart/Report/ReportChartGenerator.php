<?php
namespace App\Generator\Chart\Report;

use Illuminate\Support\Collection;

/**
 * Interface ReportChartGenerator
 *
 * @package App\Generator\Chart\Report
 */
interface ReportChartGenerator
{

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function yearInOut(Collection $entries);

    /**
     * @param string $income
     * @param string $expense
     * @param int    $count
     *
     * @return array
     */
    public function yearInOutSummarized($income, $expense, $count);

}

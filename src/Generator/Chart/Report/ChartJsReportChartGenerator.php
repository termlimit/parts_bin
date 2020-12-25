<?php
namespace App\Generator\Chart\Report;

use Config;
use Illuminate\Support\Collection;

/**
 * Class ChartJsReportChartGenerator
 *
 * @package App\Generator\Chart\Report
 */
class ChartJsReportChartGenerator implements ReportChartGenerator
{

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function yearInOut(Collection $entries)
    {
        // language:
        $language = $this->Preferences->get('language', 'en')->data;
        $format   = Config::get('app.month.' . $language);

        $data = [
            'count'    => 2,
            'labels'   => [],
            'datasets' => [
                [
                    'label' => trans('app.income'),
                    'data'  => []
                ],
                [
                    'label' => trans('app.expenses'),
                    'data'  => []
                ]
            ],
        ];

        foreach ($entries as $entry) {
            $data['labels'][]              = $entry[0]->formatLocalized($format);
            $data['datasets'][0]['data'][] = round($entry[1], 2);
            $data['datasets'][1]['data'][] = round($entry[2], 2);
        }

        return $data;
    }

    /**
     * @param string $income
     * @param string $expense
     * @param int    $count
     *
     * @return array
     */
    public function yearInOutSummarized($income, $expense, $count)
    {

        $data                          = [
            'count'    => 2,
            'labels'   => [trans('app.sum_of_year'), trans('app.average_of_year')],
            'datasets' => [
                [
                    'label' => trans('app.income'),
                    'data'  => []
                ],
                [
                    'label' => trans('app.expenses'),
                    'data'  => []
                ]
            ],
        ];
        $data['datasets'][0]['data'][] = round($income, 2);
        $data['datasets'][1]['data'][] = round($expense, 2);
        $data['datasets'][0]['data'][] = round(($income / $count), 2);
        $data['datasets'][1]['data'][] = round(($expense / $count), 2);

        return $data;
    }
}

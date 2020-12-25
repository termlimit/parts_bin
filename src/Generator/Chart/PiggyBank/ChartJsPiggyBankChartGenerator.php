<?php
namespace App\Generator\Chart\PiggyBank;

use Carbon\Carbon;
use Config;
use Illuminate\Support\Collection;

/**
 * Class ChartJsPiggyBankChartGenerator
 *
 * @package App\Generator\Chart\PiggyBank
 */
class ChartJsPiggyBankChartGenerator implements PiggyBankChartGenerator
{

    /**
     * @param Collection $set
     *
     * @return array
     */
    public function history(Collection $set)
    {

        // language:
        $language = $this->Preferences->get('language', 'en')->data;
        $format   = Config::get('app.monthAndDay.' . $language);

        $data = [
            'count'    => 1,
            'labels'   => [],
            'datasets' => [
                [
                    'label' => 'Diff',
                    'data'  => []
                ]
            ],
        ];
        $sum  = '0';
        bcscale(2);
        foreach ($set as $entry) {
            $date                          = new Carbon($entry->date);
            $sum                           = bcadd($sum, $entry->sum);
            $data['labels'][]              = $date->formatLocalized($format);
            $data['datasets'][0]['data'][] = round($sum, 2);
        }

        return $data;
    }
}

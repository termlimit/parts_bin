<?php
namespace App\Generator\Chart\Budget;

use Config;
use Illuminate\Support\Collection;

/**
 * Class ChartJsBudgetChartGenerator
 *
 * @package App\Generator\Chart\Budget
 */
class ChartJsBudgetChartGenerator implements BudgetChartGenerator
{

    /**
     * @param Collection $entries
     * @param string     $dateFormat
     *
     * @return array
     */
    public function budget(Collection $entries, $dateFormat = 'month')
    {
        // language:
        $language = $this->Preferences->get('language', 'en')->data;
        $format   = Config::get('app.' . $dateFormat . '.' . $language);

        $data = [
            'labels'   => [],
            'datasets' => [
                [
                    'label' => 'Amount',
                    'data'  => [],
                ]
            ],
        ];

        /** @var array $entry */
        foreach ($entries as $entry) {
            $data['labels'][]              = $entry[0]->formatLocalized($format);
            $data['datasets'][0]['data'][] = $entry[1];

        }

        $data['count'] = count($data['datasets']);

        return $data;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param Collection $entries
     *
     * @return array
     */
    public function budgetLimit(Collection $entries)
    {
        return $this->budget($entries, 'monthAndDay');
    }

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function frontpage(Collection $entries)
    {
        $data = [
            'count'    => 0,
            'labels'   => [],
            'datasets' => [],
        ];
        // dataset: left
        // dataset: spent
        // dataset: overspent
        $left      = [];
        $spent     = [];
        $overspent = [];
        foreach ($entries as $entry) {
            if ($entry[1] != 0 || $entry[2] != 0 || $entry[3] != 0) {
                $data['labels'][] = $entry[0];
                $left[]           = round($entry[1], 2);
                $spent[]          = round($entry[2], 2);
                $overspent[]      = round($entry[3], 2);
            }
        }

        $data['datasets'][] = [
            'label' => trans('app.left'),
            'data'  => $left,
        ];
        $data['datasets'][] = [
            'label' => trans('app.spent'),
            'data'  => $spent,
        ];
        $data['datasets'][] = [
            'label' => trans('app.overspent'),
            'data'  => $overspent,
        ];

        $data['count'] = count($data['datasets']);

        return $data;
    }

    /**
     * @param Collection $budgets
     * @param Collection $entries
     *
     * @return array
     */
    public function year(Collection $budgets, Collection $entries)
    {
        // language:
		$language = $this->Preferences->get('language', 'en')->data;
        $format   = Config::get('app.month.' . $language);

        $data = [
            'labels'   => [],
            'datasets' => [],
        ];

        foreach ($budgets as $budget) {
            $data['labels'][] = $budget->name;
        }
        /** @var array $entry */
        foreach ($entries as $entry) {
            $array = [
                'label' => $entry[0]->formatLocalized($format),
                'data'  => [],
            ];
            array_shift($entry);
            $array['data']      = $entry;
            $data['datasets'][] = $array;

        }
        $data['count'] = count($data['datasets']);

        return $data;
    }
}

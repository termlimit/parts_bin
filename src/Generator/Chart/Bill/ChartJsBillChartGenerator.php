<?php
namespace App\Generator\Chart\Bill;

use App\Model\Table\Bill;
use App\Model\Table\TransactionJournal;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Class ChartJsBillChartGenerator
 *
 * @package App\Generator\Chart\Bill
 */
class ChartJsBillChartGenerator
{

    /**
     * @param string $paid
     * @param string $unpaid
     *
     * @return array
     */
    public function frontpage(string $paid, string $unpaid)
    {
        $data = [
            [
                'value'     => round($unpaid, 2),
                'color'     => 'rgba(53, 124, 165,0.7)',
                'highlight' => 'rgba(53, 124, 165,0.9)',
                'label'     => trans('app.unpaid'),
            ],
            [
                'value'     => round(bcmul($paid, '-1'), 2), // paid is negative, must be positive.
                'color'     => 'rgba(0, 141, 76, 0.7)',
                'highlight' => 'rgba(0, 141, 76, 0.9)',
                'label'     => trans('app.paid'),
            ],
        ];
        return $data;
    }
    /**
     * @param Bill       $bill
     * @param Collection $entries
     *
     * @return array
     */
    public function single(Bill $bill, Collection $entries)
    {
        $format       = (string)trans('config.month');
        $data         = [
            'count'    => 3,
            'labels'   => [],
            'datasets' => [],
        ];
        $minAmount    = [];
        $maxAmount    = [];
        $actualAmount = [];
        /** @var TransactionJournal $entry */
        foreach ($entries as $entry) {
            $data['labels'][] = $entry->date->formatLocalized($format);
            $minAmount[]      = round($bill->amount_min, 2);
            $maxAmount[]      = round($bill->amount_max, 2);
            /*
             * journalAmount has been collected in BillRepository::getJournals
             */
            $actualAmount[] = round(TransactionJournal::amountPositive($entry), 2);
        }
        $data['datasets'][] = [
            'label' => trans('app.minAmount'),
            'data'  => $minAmount,
        ];
        $data['datasets'][] = [
            'label' => trans('app.billEntry'),
            'data'  => $actualAmount,
        ];
        $data['datasets'][] = [
            'label' => trans('app.maxAmount'),
            'data'  => $maxAmount,
        ];
        $data['count'] = count($data['datasets']);
        return $data;
    }
}

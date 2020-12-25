<?php
namespace App\Generator\Chart\PiggyBank;

use Illuminate\Support\Collection;

/**
 * Interface PiggyBankChartGenerator
 *
 * @package App\Generator\Chart\PiggyBank
 */
interface PiggyBankChartGenerator
{
    /**
     * @param Collection $set
     *
     * @return array
     */
    public function history(Collection $set);
}

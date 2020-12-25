<?php
namespace App\Generator\Chart\Category;

use Illuminate\Support\Collection;

/**
 * Interface CategoryChartGeneratorInterface
 *
 * @package App\Generator\Chart\Category
 */
interface CategoryChartGeneratorInterface
{

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function all(Collection $entries);

    /**
     * @param Collection $categories
     * @param Collection $entries
     *
     * @return array
     */
    public function earnedInPeriod(Collection $categories, Collection $entries);

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function frontpage(Collection $entries);

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function multiYear(Collection $entries);

    /**
     * @param Collection $entries
     *
     * @return array
     */
    public function period(Collection $entries);

    /**
     * @param Collection $categories
     * @param Collection $entries
     *
     * @return array
     */
    public function spentInPeriod(Collection $categories, Collection $entries);
}

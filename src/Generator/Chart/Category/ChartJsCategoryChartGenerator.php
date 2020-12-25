<?php
namespace App\Generator\Chart\Category;

use App\Model\Table\CategoriesTable;
use Cake\Collection\Collection;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Class ChartJsCategoryChartGenerator
 *
 * @package App\Generator\Chart\Category
 */
class ChartJsCategoryChartGenerator
{

	/**
	 * @param Collection $entries
	 * @param string     $dateFormat
	 *
	 * @return array
	 */
	public function all($entries, $dateFormat = 'month')
	{
		// language:
		$language = $this->Preferences->get('language', 'en');
		$format	  = Configure::read('lf.' . $dateFormat . '.' . $language);
		$type	  = $entries[0]['type'] == 1 ? 'earned' : 'spent';

		$data = [
			'count'    => 3,
			'labels'   => [],
			'datasets' => [
				[
					'label' => $dateFormat == 'monthAndDay' ? 'Day total '.$type : 'Spent',
					'data'  => []
				],
				[
					'label' => $dateFormat == 'monthAndDay' ? 'Running total '.$type : 'Earned',
					'data'	=> []
				],
				[
					'label' => 'Budgeted',
					'data'  => []
				]
			],
		];
		foreach ($entries as $entry => $value) {
			$data['labels'][] = $value['date']->format($format);
			$amount           = round($value['spent'], 2);
			$earned			  = round($value['earned'], 2);
			$budget			  = round($value['budget'], 2);
			$data['datasets'][0]['data'][] 	= ($amount < 0) ? $amount * -1 : $amount;
			$data['datasets'][1]['data'][]  = $earned;
			$data['datasets'][2]['data'][]  = $budget;
		}

		return $data;
	}

	/**
	 * @param Collection $entries
	 *
	 * @return array
	 */
	public function frontpage(Collection $entries)
	{
		$data = [
			'count'    => 1,
			'labels'   => [],
			'datasets' => [
				[
					'label' => 'Difference',
					'data'  => []
				]
			],
		];
		foreach ($entries as $entry) {
			if ($entry['difference'] != 0) {
				$data['labels'][]              = $entry['title'];
				$data['datasets'][0]['data'][] = round($entry['difference'], 2);
			}
		}

		return $data;
	}

	/**
	 * @codeCoverageIgnore
	 *
	 * @param Collection $entries
	 *
	 * @return array
	 */
	public function month($entries)
	{
		return $this->all($entries, 'monthAndDay');
	}

	/**
	 * @param Collection $categories
	 * @param Collection $entries
	 *
	 * @return array
	 */
	public function spentInYear(Collection $categories, Collection $entries)
	{

		// language:
		$language = $this->Preferences->get('language', 'en');
		$format   = Configure::read('lf.month.' . $language);

		$data = [
			'count'    => 0,
			'labels'   => [],
			'datasets' => [],
		];

		foreach ($categories as $category) {
			$data['labels'][] = $category->name;
		}

		foreach ($entries as $entry) {
			$date = $entry[0]->formatLocalized($format);
			array_shift($entry);
			$data['count']++;
			$data['datasets'][] = ['label' => $date, 'data' => $entry];
		}

		return $data;
	}

	/**
	 * @param Collection $categories
	 * @param Collection $entries
	 *
	 * @return array
	 */
	public function earnedInYear(Collection $categories, Collection $entries)
	{

		// language:
		$language = $this->Preferences->get('language', 'en');
		$format   = Configure::read('lf.month.' . $language);

		$data = [
			'count'    => 0,
			'labels'   => [],
			'datasets' => [],
		];

		foreach ($categories as $category) {
			$data['labels'][] = $category->name;
		}

		foreach ($entries as $entry) {
			$date = $entry[0]->formatLocalized($format);
			array_shift($entry);
			$data['count']++;
			$data['datasets'][] = ['label' => $date, 'data' => $entry];
		}

		return $data;
	}
}

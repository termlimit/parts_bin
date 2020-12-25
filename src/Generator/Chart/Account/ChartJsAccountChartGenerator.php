<?php
namespace App\Generator\Chart\Account;

use App\Model\Table\AccountsTable;
use App\Repositories\Account\AccountRepository;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Class ChartJsAccountChartGenerator
 *
 * @package App\Generator\Chart\Account
 */
class ChartJsAccountChartGenerator
{

	/**
	 * @codeCoverageIgnore
	 *
	 * @param Collection $accounts
	 * @param Carbon     $start
	 * @param Carbon     $end
	 *
	 * @return array
	 */
	public function all(Collection $accounts, Carbon $start, Carbon $end)
	{
		return $this->frontpage($accounts, $start, $end);
	}

	/**
	 * @param Collection $accounts
	 * @param Carbon     $start
	 * @param Carbon     $end
	 *
	 * @return array
	 */
	public function expenseAccounts(Collection $accounts, Carbon $start, Carbon $end)
	{
		$data = [
			'count'    => 1,
			'labels'   => [], 'datasets' => [[
							   'label' => trans('app.spent'),
							   'data'  => []]]];

		bcscale(2);
		$start->subDay();
		$ids           = $this->getIdsFromCollection($accounts);
		$startBalances = Steam::balancesById($ids, $start);
		$endBalances   = Steam::balancesById($ids, $end);

		$accounts->each(
			function (Account $account) use ($startBalances, $endBalances) {
				$id                  = $account->id;
				$startBalance        = $this->isInArray($startBalances, $id);
				$endBalance          = $this->isInArray($endBalances, $id);
				$diff                = bcsub($endBalance, $startBalance);
				$account->difference = round($diff, 2);
			}
		);

		$accounts = $accounts->sortByDesc(
			function (Account $account) {
				return $account->difference;
			}
		);

		foreach ($accounts as $account) {
			if ($account->difference > 0) {
				$data['labels'][]              = $account->name;
				$data['datasets'][0]['data'][] = $account->difference;
			}
		}

		return $data;
	}

	/**
	 * @param $array
	 * @param $entryId
	 *
	 * @return string
	 */
	protected function isInArray($array, $entryId)
	{
		if (isset($array[$entryId])) {
			return $array[$entryId];
		}

		return '0';
	}

	/**
	 * @param Collection $accounts
	 * @param Carbon     $start
	 * @param Carbon     $end
	 *
	 * @return array
	 */
	#public function frontpage(Collection $accounts, Carbon $start, Carbon $end)
	public function frontpage()
	{
		// language:
		#$format   = Config::get('app.monthAndDay.' . $language);
		$language = 'en';
		$format   = 'M d, Y';
		$data     = [
			'count'    => 0,
			'labels'   => [],
			'datasets' => [],
		];
		#$current  = clone $start;
		#while ($current <= $end) {
			#$data['labels'][] = $current->formatLocalized($format);
			$data['labels'][] = 'Month day, Year';
			$data['labels'][] = 'Month day, Year';
			$data['labels'][] = 'Month day, Year';
			$data['labels'][] = 'Month day, Year';
			#$current->addDay();
		#}

		/* 
		foreach ($accounts as $account) {
			$set     = [
				'label'                => $account->name,
				'fillColor'            => 'rgba(220,220,220,0.2)',
				'strokeColor'          => 'rgba(220,220,220,1)',
				'pointColor'           => 'rgba(220,220,220,1)',
				'pointStrokeColor'     => '#fff',
				'pointHighlightFill'   => '#fff',
				'pointHighlightStroke' => 'rgba(220,220,220,1)',
				'data'                 => [],
			];
			$current = clone $start;
			while ($current <= $end) {
				$set['data'][] = Steam::balance($account, $current);
				$current->addDay();
			}
			$data['datasets'][] = $set;
		}
		$data['count'] = count($data['datasets']);
		*/
		$set     = [
			'label'                => 'Account title',
			'fillColor'            => 'rgba(220,220,220,0.2)',
			'strokeColor'          => 'rgba(220,220,220,1)',
			'pointColor'           => 'rgba(220,220,220,1)',
			'pointStrokeColor'     => '#fff',
			'pointHighlightFill'   => '#fff',
			'pointHighlightStroke' => 'rgba(220,220,220,1)',
			'data'                 => [],
		];
		$set['data'][] = '1.00';
		$set['data'][] = '2.00';
		$set['data'][] = '3.00';
		$set['data'][] = '4.00';

		$data['datasets'][] = $set;
		$data['count'] = count($data['datasets']);

		return $data;
	}

	/**
	 * @param Account $account
	 * @param Time    $start
	 * @param Time    $end
	 *
	 * @return array
	 */
	public function single(AccountsTable $Accounts, $account, Time $start, Time $end)
	{
		$difference = $end->diff($start);
		if ($difference->y > 0) {
			$add = 'addYear';
		} elseif ($difference->y == 0 && $difference->m > 1) {
			$add = 'addMonth';
		} else {
			$add = 'addDay';
		}

		// language:
		$language = 'en';
		$format   = 'M d, Y';

		$data = [
			'count'    => 1,
			'labels'   => [],
			'datasets' => [
				[
					'label' => $account->title,
					'data'  => []
				]
			],
		];

		$current = clone $start;
		$account->balance = $account->balance + $Accounts->getBalanceAdjust($account, $start);

		while ($end >= $current) {
			$data['labels'][]              = $current->format($format);
			$data['datasets'][0]['data'][] = $account->balance + $Accounts->getBalanceAdjust($account, $start, $current);

			$next = clone $current;
			$current->$add();
			if ($current > $end && $next < $end) $current = clone $end;
		}

		return $data;
	}

	/*
	 * @param Time   $start
     * @param Time   $end
	 * @param String $range
     *
     * @return array
	 */
	public function netWorth(Time $start, Time $end)
	{
		$repository  = new AccountRepository();
		$accounts	 = $repository->getAccounts(['asset', 'liability'], [0,1]);

		$language = 'en';
		$format   = 'M Y';

		$data = [
			'count'    => 1,
			'labels'   => [],
			'datasets' => [
				[
					'label' => 'Net worth',
					'data'  => []
				]
			],
		];

		$current = clone $start;
		while ($end >= $current) {
			$total = 0.00;
			$data['labels'][]              = $current->format($format);
			foreach ($accounts as $account) {
				$total += $repository->calculateCommonBalance($account, $current);
			}
			$data['datasets'][0]['data'][] = $total;
			$next = clone $current;
			$current->addMonth();

			if ($current > $end && $next < $end) $current = clone $end;
		}

		return $data;
	}

	/**
	 * @param Collection $collection
	 *
	 * @return array
	 */
	protected function getIdsFromCollection(Collection $collection)
	{
		$ids = [];
		foreach ($collection as $entry) {
			$ids[] = $entry->id;
		}

		return array_unique($ids);
	}
}

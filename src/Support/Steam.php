<?php
namespace App\Support;

use App\Model\Table\AccountsTable;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

/**
 * Class Steam
 *
 * @package App\Support
 */
class Steam 
{

	/**
	 * @param array $accounts
	 *
	 * @return array
	 */
	public function getLastActivities(array $accounts)
	{
		$list = [];
		$set = Auth::user()->transactions()
			->whereIn('account_id', $accounts)
			->groupBy('account_id')
			->get(['transactions.account_id', DB::Raw('MAX(`transaction_journals`.`date`) as `max_date`')]);
		foreach ($set as $entry) {
			$list[intval($entry->account_id)] = new Carbon($entry->max_date);
		}
		return $list;
	}

	/**
	 *
	 * @param \App\Models\Account		 $account
	 * @param \Carbon\Carbon             $date
	 * @param bool                       $ignoreVirtualBalance
	 *
	 * @return float
	 */
	public static function balance(AccountsTable $Accounts, $account_id, Time $start, Time $end, $ignoreVirtualBalance = false)
	{
		// abuse chart properties:
		#$cache = new CacheProperties;
		#$cache->addProperty($account->id);
		#$cache->addProperty('balance');
		#$cache->addProperty($date);
		#$cache->addProperty($ignoreVirtualBalance);
		#if ($cache->has()) {
		#	return $cache->get(); // @codeCoverageIgnore
		#}
		//bcscale(2);
		$balance = $Accounts->getBalanceAdjust($account_id, $start, $end);
		#if (!$ignoreVirtualBalance) {
		#	$balance = bcadd($balance, $account->virtual_balance);
		#}
		#$cache->store(round($balance, 2));
		return round($balance, 2);
	}

	/**
	 *
	 * @param array          $ids
	 * @param \Carbon\Carbon $date
	 *
	 * @return float
	 */
	public function balancesById(array $ids, Time $date)
	{
		// abuse chart properties:
		//$cache = new CacheProperties;
		//$cache->addProperty($ids);
		//$cache->addProperty('balances');
		//$cache->addProperty($date);
		//if ($cache->has()) {
		//	return $cache->get(); // @codeCoverageIgnore
		//}
		bcscale(2);
		//$balances = Transaction::
		//leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id')
		//	->where('transaction_journals.date', '<=', $date->format('Y-m-d'))
		//	->groupBy('transactions.account_id')
		//	->whereIn('transactions.account_id', $ids)
		//	->get(['transactions.account_id', DB::Raw('sum(`transactions`.`amount`) as aggregate')]);
		$result = [];
		//foreach ($balances as $entry) {
		foreach ($ids as $id) {
			$result[$id] = TableRegistry::get('Accounts')->getBalanceAdjust($id, $date);
		}
		//$cache->store($result);
		return $result;
	}

    // parse PHP size:
    /**
     * @param $string
     *
     * @return int
     */
    public function phpBytes($string)
    {
        $string = strtolower($string);
        if (!(strpos($string, 'k') === false)) {
            // has a K in it, remove the K and multiply by 1024.
            $bytes = bcmul(rtrim($string, 'k'), 1024);
            return intval($bytes);
        }
        if (!(strpos($string, 'm') === false)) {
            // has a M in it, remove the M and multiply by 1048576.
            $bytes = bcmul(rtrim($string, 'm'), 1048576);
            return intval($bytes);
        }
        return $string;
    }
}

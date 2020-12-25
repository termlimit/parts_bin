<?php
namespace App\Repositories\Journal;

use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Class JournalTasker
 *
 * @package App\Repositories\Journal
 */
class JournalTasker
{

	/** @var  int */
	protected $user_id;

	/**
	 * Create a new JournalTasker instance.
	 */
	public function __construct($user_id)
	{
		$this->user_id = $user_id;
	}

    /**
     * Get a complete net worth snapshot for a logged in user
     *
     * @param Time $end
     * @param Time $start
     *
     * @return float
     */
	public function getNetWorthDeviation(Time $start = null, Time $end)
	{
		$user_id = $this->user_id;
		if ($start === null) $start = (clone $end)->subYear();

		$debit = TableRegistry::get('Transactions')
			->find();
		$debit
			->select(['sum' => $debit->func()->sum('Transactions.debit')])
			->matching('Accounts', function ($q) use ($user_id) {
				return $q->where([
					'Accounts.user_id' => $user_id
				]);
			})
			->matching('Accounts.AccountTypes', function ($q) {
				return $q->where([
					'AccountTypes.role IN' => ['asset', 'liability'],
					'AccountTypes.type IN' => [0, 1]
				]);
			})
			->matching('TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end
				]);
			});
		$debit = $debit->first()['sum'];

		$credit = TableRegistry::get('Transactions')
			->find();
		$credit
			->select(['sum' => $credit->func()->sum('Transactions.credit')])
			->matching('Accounts', function ($q) use ($user_id) {
				return $q->where([
					'Accounts.user_id' => $user_id
				]);
			})
			->matching('Accounts.AccountTypes', function ($q) {
				return $q->where([
					'AccountTypes.role IN' => ['asset', 'liability'],
					'AccountTypes.type IN' => [0, 1]
				]);
			})
			->matching('TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end
				]);
			});
		$credit = $credit->first()['sum'];

		// Use SQL to perform the heavy lifting
		$query = TableRegistry::get('Transactions')
			->find()
			->select([
				'total' => $debit - $credit,
			])
			->matching('Accounts.AccountTypes', function ($q) use ($user_id) {
				return $q->where([
					'Accounts.user_id' => $user_id
				]);
			})
			->matching('TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end
				]);
			})
			->group(['Accounts.user_id'])
			->first();

		return $query->total;
	}
}

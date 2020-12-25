<?php
namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

class SearchHelper extends Helper
{

	/**
	 * @param Time	$start
	 * @param Time	$end
	 *
	 * @return array
	 */
	public function getAccountsList(Time $start, Time $end)
	{
		$user_id = Configure::read('GlobalAuth.id');

		return TableRegistry::get('Accounts')
			->find()
			->hydrate(false)
			->select(['Accounts.id', 'Accounts.title'])
			->distinct(['Accounts.title'])
			->where(['Accounts.user_id' => (int)$user_id])
			->matching('Transactions.TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end
				]);
			})
			->order(['Accounts.title' 	 => 'ASC'])
			->toArray();
	}

	/**
	 * @param Time	$start
	 * @param Time	$end
	 *
	 * @return array
	 */
	public function getBillsList(Time $start = null, Time $end = null)
	{
		$user_id = Configure::read('GlobalAuth.id');

		return TableRegistry::get('Bills')
			->find()
			->hydrate(false)
			->select(['Bills.id', 'Bills.title'])
			->distinct(['Bills.title'])
			->where(['Bills.user_id' => (int)$user_id])
			->order(['Bills.title' 	 => 'ASC'])
			->toArray();
	}

	/**
	 * @param Time	$start
	 * @param Time	$end
	 *
	 * @return array
	 */
	public function getCategoriesList(Time $start = null, Time $end = null)
	{
		$user_id = Configure::read('GlobalAuth.id');

		return TableRegistry::get('Categories')
			->find()
			->hydrate(false)
			->select(['Categories.id', 'Categories.title'])
			->distinct(['Categories.title'])
			->where(['Categories.user_id' => (int)$user_id])
			->order(['Categories.title'   => 'ASC'])
			->toArray();
	}

	/**
	 * Alias for getAccountsList()
	 *
	 * @param Time	$start
	 * @param Time	$end
	 *
	 * @return array
	 */
	public function getTransactionsList(Time $start = null, Time $end = null)
	{
		return $this->getAccountsList($start, $end);
	}
}

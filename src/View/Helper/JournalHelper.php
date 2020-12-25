<?php
namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

class JournalHelper extends Helper
{

	/**
	 * @return Journal type conversion
	 */
	public function typeIcon($type)
	{
		//$cache = new CacheProperties();
		//$cache->addProperty($journal->id);
		//$cache->addProperty('typeIcon');
		//if ($cache->has()) {
		//	return $cache->get(); // @codeCoverageIgnore
		//}

		//$type = $journal->transactionType->type;

		switch ($type) {
			case 'Withdrawal':
				$txt = '<i class="fa fa-long-arrow-left fa-fw" title="' . $type . '"></i>';
				break;
			case 'Deposit':
				$txt = '<i class="fa fa-long-arrow-right fa-fw" title="' . $type . '"></i>';
				break;
			case 'Refund':
				$txt = '<i class="fa fa-long-arrow-right fa-fw" title="' . $type . '"></i>';
				break;
			case 'Transfer':
				$txt = '<i class="fa fa-fw fa-exchange" title="' . $type . '"></i>';
				break;
			case 'Opening balance':
				$txt = '<i class="fa-fw fa fa-ban" title="' . $type . '"></i>';
				break;
			default:
				$txt = '';
				break;
		}
		//$cache->store($txt);

		return $txt;
	}

	/**
	 * This will set the amount as a positive or negative depending on the type of transaction.
	 * positive or negative is based on the 'to' account.
	 *
	 * @param string
	 * @param float
	 * @return string
	 */
	public function correctAmountByType($type, $amount)
	{
		switch ($type) {
			case 'Withdrawal':
				$txt = $amount * -1;
				break;
			case 'Refund':
				$txt = $amount * 1;
				break;
			case 'Deposit':
				$txt = $amount * 1;
				break;
			case 'Transfer':
				$txt = $amount * 1;
				break;
			case 'Opening balance':
				$txt = $amount * 1;
				break;
			default:
				$txt = '';
				break;
		}

		return $txt;
	}

	public function correctAmountByAccountType($journal, $account)
	{
		$txt = $journal->transactions[0]['account_id'] = $account->id ? $journal->transactions[0]['amount'] : $journal->transactions[1]['amount'];
		switch ($account->account_type['role']) {
			case 'asset':
				$txt = $txt * -1;
				break;
			case 'expense':
				$txt = $txt;
				break;
			case 'revenue':
				$txt = $txt * -1;
				break;
		}

		return $txt;
	}

	public function getAccountList(Time $start, Time $end)
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
}

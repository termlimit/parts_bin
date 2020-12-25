<?php
namespace App\Repositories\Shared;

use App\Model\Table\CategoriesTable;
use App\Model\Entity\Category;
use App\Model\Table\TransactionJournalsTable;
use App\Model\Table\CategoryTransactionJournal;
use App\Repositories\Shared\ComponentRepository;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Class ComponentRepository
 *
 * @package App\Repositories\Shared
 */
class ComponentRepository
{

	/**
	 * @param        $object
	 * @param Carbon $start
	 * @param Carbon $end
	 *
	 * @param bool   $shared
	 *
	 * @return string
	 */
	protected function commonBalanceInPeriod($object, Time $start, Time $end, $shared = false)
	{
		// get all the ids
		$ids = $this->getCategoryChildIdArray($object, null);

		$shared = true;

		if ($shared === true) { // shared is true: always ignore transfers between accounts!
			$query = TableRegistry::get('CategoryTransactionJournal')
				->find()
				->where(['CategoryTransactionJournal.category_id IN ' => $ids]);

			$query
				->matching('TransactionJournals', function ($q) use ($start, $end) {
					return $q->where([
						'TransactionJournals.user_id'	 => Configure::read('GlobalAuth.id'),
						'TransactionJournals.entered >=' => $start,
						'TransactionJournals.entered <=' => $end
					]);
				});
			// Case statements for different transaction types.
			$depositCase = $query->newExpr()->addCase($query->newExpr()->add(['TransactionJournals.transaction_type_id IN' => [2, 5]]), ['CategoryTransactionJournal.amount' => 'literal'], 'decimal');
			$withdrawalCase = $query->newExpr()->addCase($query->newExpr()->add(['TransactionJournals.transaction_type_id' => 1]), ['CategoryTransactionJournal.amount * -1' => 'literal'], 'decimal');

			$result = $query
				->select([
					'deposits' => $query->func()->sum($depositCase),
					'withdrawals' => $query->func()->sum($withdrawalCase)
				])
				->toArray();

		} else {
 			$query = TableRegistry::get('CategoryTransactionJournal')
				->find()
				->matching('TransactionJournals', function ($q) use ($start, $end) {
					return $q->where([
						'TransactionJournals.user_id'	 => Configure::read('GlobalAuth.id'),
						'TransactionJournals.entered >=' => $start,
						'TransactionJournals.entered <=' => $end
					]);
				})
				->matching('TransactionJournals.TransactionTypes', function ($q) {
					return $q->where([
						'TransactionTypes.type IN' => ['Withdrawal', 'Deposit', 'Opening balance']
					]);
				})
				->matching('TransactionJournals.Transactions.Accounts.AccountMeta', function ($q) {
					return $q->where([
						'AccountMeta.title' => 'accountRole',
						'AccountMeta.data !=' => 'sharedAsset'
					]);
				})
				->where([
						'CategoryTransactionJournal.category_id IN' => $ids
				]);
			$sum = $query
				->select(['sum' => $query->func()->sum('CategoryTransactionJournal.amount')])
				->toArray();
		}

		return [
			'spent'		=> sprintf('%0.2f', $result[0]['withdrawals']),
			'earned'	=> sprintf('%0.2f', $result[0]['deposits'])
		];
	}

	/**
     * sumTransactionsByDateRange method
     *
	 * @param \Cake\I18n\Time	$start
	 * @param \Cake\I18n\Time	$end
     * @return object
     */
	public function sumTransactionsByDateRange(Time $start = null, Time $end = null)
	{
		// No date, return 0.00 amount
		if ($start === null || $end === null) {
			return '0.00';
		}

		// Get the sum of transactions for a given date range
		$query = TableRegistry::get('CategoryTransactionJournal')
			->find();

		// If start and end are not null, get a period of time
		$query
			->matching('TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end
				]);
			});

		$result = $query
			->select(['sum' => $query->func()->sum('amount')])
			->toArray();

		return sprintf('%0.2f', $result[0]['sum']);
	}
}

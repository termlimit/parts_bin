<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Account;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * Category Transaction Journal Model
 *
 * @property \Cake\ORM\Association\belongsTo $Categories
 * @property \Cake\ORM\Association\belongsTo $TransactionJournals
 */
class CategoryTransactionJournalTable extends Table
{

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config)
	{
		parent::initialize($config);
		$this->belongsTo('Categories');
		$this->belongsTo('TransactionJournals');
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator)
	{
		$validator
			->add('id', 'valid', ['rule' => 'numeric'])
			->allowEmpty('id', 'create');

		$validator
			->add('category_id', 'valid', ['rule' => 'numeric'])
			->notEmpty('category_id');

		$validator
			->add('transaction_journal_id', 'valid', ['rule' => 'numeric'])
			->notEmpty('transaction_journal_id');

		$validator
			->add('amount', 'valid', ['rule' => ['decimal', 2]])
			->notEmpty('amount');

		$validator
			->allowEmpty('description', 'true')
			->add('description', 'valid', ['rule' => 'ascii']);

		return $validator;
	}

	/**
	 * detach Remove category - transaction journal association
	 *
	 * @param int $id The transaction_journal_id to remove
	 * @return boolean false on error
	 */
	public function detach($id)
	{
		$associations = $this->findByJournalId($id);
		if ($associations->count() == 0) return true;

		foreach ($associations as $association) {
			if (!$result = $this->delete($association))
				return false;
		}
		return true;
	}

	/**
	 * findByCategoryId method
	 *
	 * @param array	$ids 	Category ids
	 * @param Time	$start	Start range of entries
	 * @param Time	$end	End range of entries
	 * @param int	$page	The current page number
	 * @return object
	 */
	public function findByCategoryId(array $ids, Time $start, Time $end, $page = null)
	{
		$journalIds = [];
		// Get all transaction_journal ids for this account (1 side) between $start and $end
		$transactionJournalIds = $this
			->find()
			->where(['CategoryTransactionJournal.category_id IN' => $ids])
			->matching('TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end,
					'TransactionJournals.user_id'	 => Configure::read('GlobalAuth.id'),
				]);
			})
			->extract('transaction_journal_id')
			->toArray();
		if (count($transactionJournalIds) > 0) {
			foreach ($transactionJournalIds as $journal_id) {
				$journalIds[] = $journal_id;
			}
			return TableRegistry::get('TransactionJournals')->getTransactionsByJournalIds($journalIds);
		}
		return null;
	}

	/**
	 * findByJournalId method
	 *
	 * @param int	$id 	The journal id
	 * @return object
	 */
	public function findByJournalId($id)
	{
		return $this
			->find()
			->where(['CategoryTransactionJournal.transaction_journal_id' => $id]);
	}

	/**
	 * spentByCategoryNode method
	 *
	 * @access public
	 * @param  int[] $ids 	Categories to search
	 * @param  Time	 $start	Start date
	 * @param  Time	 $end	End date
	 * @return float
	 */
	public function spentByCategoryNode($ids, Time $start = null, Time $end = null)
	{
		if (!is_array($ids)) {
			$ids = [$ids];
		}

		// This should not occur, however it ensures accurate response
		if ($start === null || $end === null) return '0.00';

		$query = $this
			->find()
			->where(['CategoryTransactionJournal.category_id IN' => $ids], ['type' => 'int[]'])
			->matching('TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end,
					'TransactionJournals.user_id'	 => Configure::read('GlobalAuth.id'),

				]);
			});

		// Case statements for different transaction types.
		$depositCase = $query->newExpr()->addCase($query->newExpr()->add(['TransactionJournals.transaction_type_id IN' => [2, 5]]), ['CategoryTransactionJournal.amount' => 'literal'], 'decimal');
		$withdrawalCase = $query->newExpr()->addCase($query->newExpr()->add(['TransactionJournals.transaction_type_id IN' => [1, 3]]), ['CategoryTransactionJournal.amount * -1' => 'literal'], 'decimal');

		$result = $query
			->select([
				'deposits' => $query->func()->sum($depositCase),
				'withdrawals' => $query->func()->sum($withdrawalCase)
			])
			->toArray();

		$withdrawals = isset($result[0]['withdrawals']) ? sprintf('%0.2f', $result[0]['withdrawals']) : '0.00';
		$deposits = isset($result[0]['deposits']) ? sprintf('%0.2f', $result[0]['deposits']) : '0.00';

		return [$withdrawals, $deposits];
	}

	public function spentOnDay($category, Time $month)
	{
		// Get the sum of transactions against a category
		$query = $this
			->find()
			->where(['CategoryTransactionJournal.category_id' => $category->id]);

		$query
			->matching('TransactionJournals', function ($q) use ($month) {
				return $q->where([
					'TransactionJournals.entered >=' => $month,
					'TransactionJournals.entered <=' => $month,
					'TransactionJournals.user_id'	 => Configure::read('GlobalAuth.id'),
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
		return [
			'spent'		=> sprintf('%0.2f', $result[0]['withdrawals']),
			'earned'	=> sprintf('%0.2f', $result[0]['deposits'])
		];
	}

	/**
	 * Store new category transaction journal entry
	 *
	 * @param array $data The user data to store.
	 * @return int/boolean false on error
	 */
	public function store(array $data)
	{
		// one error is an abort
		foreach ($data['category_transaction_journal'] as $key => $value) {
			if ($value['category_id'] != '') {
				$categoryTransactionJournal = $this->newEntity([
					'category_id' => $value['category_id'],
					'transaction_journal_id' => $data['transaction_journal_id'],
					'amount' => $value['amount'] == 0 ? null : $value['amount'],
					'description' => $value['description']
				]);
				if ($categoryTransactionJournal->errors()) {
					Log::write('error', 'File: ' . __FILE__ . '|||Line: ' . __LINE__ . '|||Data: ' . serialize($categoryTransactionJournal));
					return $categoryTransactionJournal;
				}
				if (!$this->save($categoryTransactionJournal)) {
					Log::write('error', 'File: ' . __FILE__ . '|||Line: ' . __LINE__ . '|||Data: ' . serialize($categoryTransactionJournal));
					return $categoryTransactionJournal;
				}
			}
		}

		// no errors, return empty entity
		return $this->newEntity();
	}

	/**
	 * sumTransactionsByCategoryId method
	 *
	 * @param int				$id Category id
	 * @param \Cake\I18n\Time	$start
	 * @param \Cake\I18n\Time	$end
	 * @return object
	 */
	public function sumTransactionsByCategoryId($id, Time $start = null, Time $end = null)
	{
		$query = $this
			->find()
			->where(['CategoryTransactionJournal.category_id' => $id]);

		// If start and end are not null, get a period of time
		if ($start !== null && $end !== null) {
			$query
				->matching('TransactionJournals', function ($q) use ($start, $end) {
					return $q->where([
						'TransactionJournals.entered >=' => $start,
						'TransactionJournals.entered <=' => $end,
						'TransactionJournals.user_id'	 => Configure::read('GlobalAuth.id'),
					]);
				});
		} else {
			$query
				->matching('TransactionJournals', function ($q) use ($start) {
					return $q->where([
						'TransactionJournals.entered <' => $start,
						'TransactionJournals.user_id'	=> Configure::read('GlobalAuth.id'),
					]);
				});
		}
		// Case statements for different transaction types.
		$depositCase = $query->newExpr()->addCase($query->newExpr()->add(['TransactionJournals.transaction_type_id IN' => [2, 5]]), ['CategoryTransactionJournal.amount' => 'literal'], 'decimal');
		$withdrawalCase = $query->newExpr()->addCase($query->newExpr()->add(['TransactionJournals.transaction_type_id' => 1]), ['CategoryTransactionJournal.amount * -1' => 'literal'], 'decimal');

		$result = $query
			->select([
				'deposits' => $query->func()->sum($depositCase),
				'withdrawals' => $query->func()->sum($withdrawalCase)
			])
			->toArray();

		return sprintf('%0.2f', ($result[0]['withdrawals']+$result[0]['deposits']));
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules)
	{
		$rules->add($rules->existsIn('transaction_journal_id', 'transactionjournals'));
		$rules->add($rules->existsIn('category_id', 'categories'));
		return $rules;
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['amount'] = $data['amount'] == 0 ? null : sprintf('%0.2f', $data['amount']);
		#$data['amount'] = sprintf('%0.2f', $data['amount']);
	}
}

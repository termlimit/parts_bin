<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Account;
use App\Model\Entity\Transaction;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\RulesChecker;
use Cake\ORM\Query;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * Transaction Model
 *
 * @property \Cake\ORM\Association\belongsTo $TransactionJournals
 * @property \Cake\ORM\Association\belongsTo $Accounts
 */
class TransactionsTable extends Table
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
		$this->displayField('amount');

        $this->addBehavior('Timestamp');

		$this->belongsTo('TransactionJournals');
		$this->belongsTo('Accounts');
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
            ->add('account_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('account_id');

        $validator
            ->add('transaction_journal_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('transaction_journal_id');

		$validator
			->allowEmpty('amount')
			->add('amount', [
				'decimal' => [
					'rule' => ['decimal', 2],
					'last' => true,
					'message' => 'Amount must be a number'
				],
			]);

		$validator
			->notEmpty('credit')
			->add('credit', [
				'decimal' => [
					'rule' => ['decimal', 2],
					'last' => true,
					'message' => 'Credit must be a number'
				],
			]);

		$validator
			->notEmpty('debit')
			->add('debit', [
				'decimal' => [
					'rule' => ['decimal', 2],
					'last' => true,
					'message' => 'Debit must be a number'
				],
			]);

		$validator
            ->add('active', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('active');

        $validator
            ->add('deleted_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('deleted_date');

		return $validator;
    }

	/**
     * getJournals method
     *
     * @param array $types 	 The type of journal to get.
	 * @param int	$user_id The user in session
	 * @param int	$offset  The offset to start at
	 * @param int	$page	 The current page number
     * @return object
     */
	public function getJournals(array $types, $user_id, $start, $end, $offset = null, $page = null)
	{
		$results = TableRegistry::get('TransactionJournals')
			->find()
			->autoFields(true)
			->contain([
				'Transactions.Accounts.AccountTypes',
				'TransactionCurrencies',
				'TransactionTypes',
				'Bills',
				//'CategoryTransactionJournal.Categories.Budgets.BudgetLimits'
				'CategoryTransactionJournal.Categories'
			])
			->where(['TransactionJournals.user_id' => $user_id])
			->andWhere([
				'TransactionJournals.entered >=' => $start,
				'TransactionJournals.entered <=' => $end
			])
			->order(['TransactionJournals.entered' => 'DESC']);

		return $results;
	}

	/**
     * findByAccountId method
     *
	 * @param int	$account_id The user in session
	 * @param Time	$start		Start range of entries
	 * @param Time	$end		End range of entries
	 * @param int	$page	    The current page number
     * @return object
     */
	public function findByAccountId($account_id, Time $start, Time $end, $page = null)
	{
		$journalIds = [];
		// Get all transaction_journal ids for this account (1 side) between $start and $end
		$transactionJournalIds = $this
			->find()
			->where(['Transactions.account_id' => $account_id])
			//->limit(50)
			//->page(1)
			->order([
				'Transactions.id' => 'DESC'
			])
			->matching('TransactionJournals', function ($q) use ($start, $end) {
				return $q->where([
					'TransactionJournals.entered >=' => $start,
					'TransactionJournals.entered <=' => $end
				]);
			})
			->extract('transaction_journal_id');
		foreach($transactionJournalIds as $journal_id) {
			$journalIds[] = $journal_id;
		}

		return TableRegistry::get('TransactionJournals')->getTransactionsByJournalIds($journalIds);
	}

	/**
     * sumTransactionsByAccountId method
     *
	 * @param int				$account_id The user in session
	 * @param \Cake\I18n\Time	$start
	 * @param \Cake\I18n\Time	$end
	 * @param int				$page The current page number
     * @return object
     */
	public function sumTransactionsByAccountId($account_id, Time $start = null, Time $end = null, $page = null)
	{
		// Get the sum of transactions against an account
		$query = $this
			->find()
			->where(['Transactions.account_id' => $account_id]);

		// If start and end are not null, get a period of time
		if ($start !== null && $end !== null) {
			$query
				->matching('TransactionJournals', function ($q) use ($start, $end) {
					return $q->where([
						'TransactionJournals.entered >=' => $start,
						'TransactionJournals.entered <=' => $end
					]);
				});
		} else {
			$query
				->matching('TransactionJournals', function ($q) use ($start) {
					return $q->where(['TransactionJournals.entered <=' => $start]);
				});
		}

		return $query
			->select(['debits' => $query->func()->sum('debit'), 'credits' => $query->func()->sum('credit')]);
	}

    /**
     * getJournal method
     *
     * @param int   $id 	 The journal to get.
     * @return object
     */
	public function getJournal($id)
	{
		return TableRegistry::get('TransactionJournals')
			->find()
			->autoFields(true)
			->contain([
				'Transactions.Accounts.AccountTypes',
				'TransactionCurrencies',
				'TransactionTypes',
				'Bills',
				'CategoryTransactionJournal.Categories'
			])
			->where([
				'TransactionJournals.id' => $id,
				'TransactionJournals.user_id' => Configure::read('GlobalAuth.id')
			])
			->first();
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
        return $rules;
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['user_id'] = Configure::read('GlobalAuth.id');
		$data['amount'] = sprintf('%0.2f', $data['amount']);
		$data['credit'] = sprintf('%0.2f', $data['credit']);
		$data['debit'] = sprintf('%0.2f', $data['debit']);
	}
}

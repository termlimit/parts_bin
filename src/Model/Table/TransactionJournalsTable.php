<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Account;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * Transaction Journals Model
 *
 * @property \Cake\ORM\Association\belongsTo $TransactionCurrencies
 * @property \Cake\ORM\Association\belongsTo $TransactionTypes
 * @property \Cake\ORM\Association\belongsTo $Bills
 * @property \Cake\ORM\Association\belongsTo $Users
 * @property \Cake\ORM\Association\hasMany	 $CategoryTransactionJournal
 * @property \Cake\ORM\Association\hasMany	 $PiggyBankEvents
 * @property \Cake\ORM\Association\hasMany	 $Transactions
 */
class TransactionJournalsTable extends Table
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
		$this->displayField('entered');

        $this->addBehavior('Timestamp');

		$this->belongsTo('TransactionCurrencies');
		$this->belongsTo('TransactionTypes');
		$this->belongsTo('Bills');
		$this->belongsTo('Users');
		$this->hasMany('CategoryTransactionJournal');
		//$this->hasMany('PiggyBankEvents');
		$this->hasMany('Transactions');
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
            ->add('user_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('user_id');

        $validator
            ->add('transaction_type_id', 'valid', ['rule' => 'numeric', 'message' => 'Please select withdrawal, refund, deposit, or transfer.'])
            ->notEmpty('transaction_type_id');

        $validator
            ->add('transaction_currency_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('transaction_currency_id');

        $validator
            ->add('bill_id', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('bill_id');

        $validator
			->add('title', [
				'ascii' => [
					'required' => false,
					'rule' => ['ascii'],
					'message' => 'Title has to be regular keyboard characters.',
					'last' => true,
				],
			])
            ->allowEmpty('title');

        $validator
            ->allowEmpty('description', 'true')
			->add('description', [
				'ascii' => [
					'rule' => ['ascii'],
					'last' => true,
					'message' => 'Description has to be regular keyboard characters.'
				],
			]);

        $validator
            ->add('completed', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('completed');

        $validator
            ->add('entered', 'valid', ['rule' => 'date'])
			->notEmpty('entered', 'Please enter a transaction date');

        $validator
            ->add('posted', 'valid', ['rule' => 'date'])
            ->allowEmpty('posted');

        $validator
            ->add('active', 'valid', ['rule' => 'numeric'])
            ->allowEmpty('active');

        $validator
            ->add('deleted_date', 'valid', ['rule' => 'datetime'])
            ->allowEmpty('deleted_date');

		return $validator;
    }

	public function getTransactionsByJournalIds($ids)
	{
		if (count($ids) == 0) return null;
		return $this
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
			->where(['TransactionJournals.id IN' => $ids])
			->order([
				'TransactionJournals.entered' => 'DESC',
				'TransactionJournals.id' => 'DESC',
			]);
	}

	public function getTransactionByJournalId($id)
	{
		return $this
			->find()
			->autoFields(true)
			->contain([
				'Transactions.Accounts',
				'TransactionCurrencies',
				'TransactionTypes',
				'Bills',
				//'CategoryTransactionJournal.Categories.Budgets.BudgetLimits'
				'CategoryTransactionJournal.Categories'
			])
			->where(['TransactionJournals.id' => $id])
			->first();
	}

	public function findFirstAccountTransaction($id) {
		$query = $this
			->find()
			->order([
				'TransactionJournals.entered' => 'ASC'
			])
			->matching('Transactions', function ($q) use ($id) {
				return $q->where([
					'Transactions.account_id' => $id,
				]);
			})
			->first();

		return $query !== null ? $query->entered : null;
	}

	/**
	 * findLastAccountTransaction
	 *
     * @param Entity object		$account
	 * @return Time  object		$entered
	 */
	public function findLastAccountTransaction($account) {
		$query = $this
			->find()
			->order([
				'TransactionJournals.entered' => 'DESC'
			])
			->matching('Transactions', function ($q) use ($account) {
				return $q->where([
					'Transactions.account_id' => $account->id,
				]);
			});
			#->first();

		if ($query->count() > 0) {
			return $query->first()->entered;
		}
		return null;
	}

	/**
	 * findLastCategoryTransaction
	 *
     * @param Entity object		$category
	 * @return Time  object		$entered
	 */
	public function findLastCategoryTransaction($category) {
		$query = $this
			->find()
			->order([
				'TransactionJournals.entered' => 'DESC'
			])
			->matching('CategoryTransactionJournal', function ($q) use ($category) {
				return $q->where([
					'CategoryTransactionJournal.category_id' => $category->id,
				]);
			});
			#->first();

		if ($query->count() > 0) {
			return $query->first()->entered;
		}
		return null;
	}

	/**
	 * findFirstCategoryTransaction
	 *
     * @param Entity int		$id
	 * @return Time  object		$entered
	 */
	public function findFirstCategoryTransaction($id) {
		$query = $this
			->find()
			->order([
				'TransactionJournals.entered' => 'ASC'
			])
			->matching('CategoryTransactionJournal', function ($q) use ($id) {
				return $q->where([
					'CategoryTransactionJournal.category_id' => $id,
				]);
			})
			->first();

		return isset($query->entered) ? $query->entered : null;
	}

    /**
     * Store new transaction journal entry
     *
     * @param array $data The user data to store.
     * @return int/boolean false on error
     */
	public function store(array $data)
	{
		$transactionJournal = $this->newEntity([
			'transaction_type_id' => $data['transaction_type']['id'],
			'transaction_currency_id' => $data['transaction_currency']['id'],
			'bill_id' => $data['bill_id'],
			'title' => isset($data['title']) ? $data['title'] : '',
			'description' => isset($data['description']) ? $data['title'] : '',
			'completed' => $data['completed'],
			'entered' => $data['entered'],
			'posted' => $data['posted'],
			'active' => 1,
		]);
		if (!$transactionJournal->errors()) {
			if ($this->save($transactionJournal)) {
				return $transactionJournal;
			}
		}
		Log::write('error', 'File: ' . __FILE__ . '|||Line: ' . __LINE__ . '|||Data: ' . json_encode($transactionJournal->errors()));
		return $transactionJournal;
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
		$rules->add($rules->existsIn('transaction_currency_id', 'transactioncurrencies'), [
			'errorField' => 'transaction_currency_id',
			'message' => 'Please select a currency from the drop down box.'
		]);
		$rules->add($rules->existsIn('transaction_type_id', 'transactiontypes'), [
			'errorField' => 'transaction_type_id',
			'message' => 'Please select withdrawal, refund, deposit, or transfer.'
		]);
        return $rules;
	}

	/**
	 * isOwnedBy current user in session owns the transaction_journal_id
	 *
	 * @param int	$transaction_journal_id
	 * @param int	$user_id
	 * @return boolean true on owner
	 */
	public function isOwnedBy($transaction_journal_id, $user_id)
	{
		return $this->exists(['id' => $transaction_journal_id, 'user_id' => $user_id]);
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['user_id'] = Configure::read('GlobalAuth.id');
	}
}

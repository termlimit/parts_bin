<?php
namespace App\Model\Table;

use ArrayObject;
use App\Log\Engine\DatabaseLog;
use App\Model\Entity\Account;
use App\Model\Entity\TransactionType;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\I18n\Date;
use Cake\Log\Log;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Cake\Utility\Inflector;
use Cake\ORM\Rule\ExistsIn;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * Accounts Model
 *
 * @property \Cake\ORM\Association\hasOne $AccountMeta
 */
class AccountsTable extends Table
{
	use SoftDeleteTrait;
	protected $softDeleteField = 'deleted_date';

	public $account_ids = [];

    /**
		* Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
		$this->displayField('title');

        $this->addBehavior('Timestamp');
		$this->addBehavior('Tree', [
			#'scope' => [$this->alias().'.deleted_date IS NULL']
			'scope' => ['deleted_date IS NULL']
		]);
		//$this->addBehavior('Sluggable');

		$this->hasMany('AccountMeta');
		$this->belongsTo('Users');
		$this->belongsTo('AccountTypes');
		$this->hasMany('Transactions');
		$this->hasMany('Reconciles');
		//$this->hasMany('PiggyBanks');
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
			->requirePresence('account_type_id')
			->add('account_type_id', 'valid', ['rule' => 'numeric'])
			->notEmpty('account_type_id');

		$validator
			->add('title', [
				'ascii' => [
					'required' => false,
					'rule' => ['ascii'],
					'message' => 'Title has to be regular keyboard characters.',
					'last' => true,
				],
			])
			->notEmpty('title', 'An account title is required');

        $validator
			->allowEmpty('description', true)
			->add('description', [
				'ascii' => [
					'required' => false,
					'rule' => ['ascii'],
					'message' => 'Description has to be regular keyboard characters.',
					'last' => true,
				],
			]);

		$validator
			->allowEmpty('balance', 'true')
			->add('balance', [
				'decimal' => [
					'rule' => ['decimal', 2],
					'last' => true,
					'message' => 'Balance must be a number'
				],
			]);

		$validator
			->add('currency', [
				'alphaNumeric' => [
					'rule' => ['alphaNumeric'],
					'last' => true,
					'message' => 'Currency can only be 3 uppercase letters, please select from dropdown'
				],
				'maxLength' => [
					'rule' => ['maxLength', 3],
					'message' => 'Currency can only be 3 characters'
				],
			]);

		$validator
			->allowEmpty('interest', 'true')
			->add('interest', 'valid', ['rule' => ['decimal', 2]]);

        $validator
            ->allowEmpty('billing', 'true')
            ->add('billing', 'valid', ['rule' => 'date']);

		$validator
            ->allowEmpty('expiration', 'true')
			->add('expiration', 'valid', ['rule' => 'date']);

        $validator
            ->allowEmpty('active')
            ->add('active', 'valid', ['rule' => 'numeric']);

        $validator
            ->allowEmpty('deleted_date', 'true')
			->add('deleted_date', 'valid', ['rule' => 'datetime']);

		return $validator;
    }

    /**
     * @param int[]		$id
	 * @param int		$transaction_type_id
     * @return boolean
     */
	public function accountIdsUnique($ids, $transaction_type_id = null)
	{
		// Transfers require 2 asset accounts
		if ((int)$transaction_type_id === 3) {
			if ((count($ids) > 1) && (count($ids) === count(array_unique($ids)))) {
				return true;
			}
		}
		// All other transactions only require one
		if ((count($ids) >= 1) && (count($ids) === count(array_unique($ids)))) {
			return true;
		}
		return false;
	}

    /**
     * @param int 			$user_id
     * @return array
     */
	public function accountList($user_id)
	{
		return $this
			->find('list', [
				'keyField' 		 => 'id',
				'valueField' 	 => 'title'
			])
			->where([
				'active' 		 => 1,
				'user_id'		 => $user_id,
				'deleted_date IS NULL'
			])
			->order(['title' 	 => 'ASC'])
			->toArray();
	}

	/**
	 * check for accounts
     * @return array
     */
	public function check()
	{
		return $this->find()->where(['user_id' => Configure::read('GlobalAuth.id')]);
	}

	/**
	 * findFirstAccountTransaction
	 *
     * @param Entity object		$account
	 * @return Time  object		$entered
	 */
	public function findFirstAccountTransaction($id) {
		return TableRegistry::get('TransactionJournals')->findFirstAccountTransaction($id);
	}

	/**
	 * findLastAccountTransaction
	 *
     * @param Entity object		$account
	 * @return Time  object		$entered
	 */
	public function findLastAccountTransaction(Account $account) {
		$last = TableRegistry::get('TransactionJournals')->findLastAccountTransaction($account);
		return $last === null ? 'Never' : $last;
	}

    /**
	 * @param array			$types
     *
     * @return array
     */
	public function getAccounts($types)
	{
		$result = $this
			->find()
			->autoFields(true)
			->where([
				'Accounts.user_id' => Configure::read('GlobalAuth.id'),
				'OR' 		=> [['expiration IS NULL'], ['expiration >' => new Date('now')]],
			])
			->order(['Accounts.account_type_id' => 'ASC', 'Accounts.title' => 'ASC'])
			->contain([
				'AccountMeta' => function ($query) {
					return $query
						->where(['AccountMeta.title' => 'accountRole']);
				}
			]);
		if (count($types) > 0) {
			$result->matching(
				'AccountTypes', function ($q) use ($types) {
					return $q->where(['AccountTypes.title IN' => $types], ['title' => 'string[]']);
				});
		} else {
			$result->contain([
				'AccountTypes' => function ($query) use ($types) {
					return $query
						->where(['AccountTypes.id = Accounts.account_type_id']);
				}
			]);
		}

		return $result;
	}

    /**
	 * @param array			$types
     *
     * @return array
     */
	public function getClosedAccounts()
	{
		return $this
			->find('all', ['withDeleted'])
			->autoFields(true)
			->where([
				'Accounts.user_id' => Configure::read('GlobalAuth.id'),
				'OR' 		=> [['Accounts.deleted_date IS NOT NULL'], ['expiration <' => new Date('now')], ['active' => 0]],
			])
			->order(['Accounts.title' => 'ASC'])
			->contain([
				'AccountMeta' => function ($query) {
					return $query
						->where(['AccountMeta.title' => 'accountRole']);
				}
			])
			->contain([
				'AccountTypes' => function ($query) {
					return $query
						->where(['AccountTypes.id = Accounts.account_type_id']);
				}
			]);
	}

    /**
	 * getBalanceAdjust
	 * This will sum the current date range of transactions for a given account
     * @param Entity object		$account
     * @param Query object
     *
     * @return array
     */
	public function getBalanceAdjust(Account $account, Time $start = null, Time $end = null)
	{
		// Return 0 on null values
		if ($start === null && $end === null) {
			return '0.00';
		}
		// If $end is null, determine the account range.
		if ($end === null) {
			$end = $start;
			// Close #75, incorrect expense account total. Check for first transaction date, may
			// occur prior to entered date for auto-generated expense accounts.
			$start = TableRegistry::get('TransactionJournals')->findFirstAccountTransaction($account->id);
			if ($start !== null && $start instanceof Date && $account->account_type_id == 14) {
				$start = new Time($start);
			} else {
				$start = new Time($account->entered);
			}
		}

		$transactionTotal = TableRegistry::get('Transactions')->sumTransactionsByAccountId($account->id, $start, $end)->toArray();
		return $transactionTotal[0]['debits'] - $transactionTotal[0]['credits'];
	}

    /**
	 * getBalanceAdjustLoan
	 * This will sum the current date range of transactions for a given loan account
     * @param Entity object		$account
     * @param Query object
     *
     * @return array
     */
	public function getBalanceAdjustLoan(Account $account, Time $start = null, Time $end = null)
	{
		// Return 0 on null values
		if ($start === null && $end === null) {
			return '0.00';
		}
		// If $end is null, determine the account range.
		if ($end === null) {
			$end = $start;
			// Close #75, incorrect expense account total. Check for first transaction date, may
			// occur prior to entered date for auto-generated expense accounts..
			$start = TableRegistry::get('TransactionJournals')->findFirstAccountTransaction($account->id);
			if ($start !== null && $start instanceof Date) {
				$start = new Time($start);
			} else {
				$start = new Time($account->entered);
			}
		}
		$category = TableRegistry::get('Categories')->findByTitle($account->title);
		if (is_array($category) && isset($category[0])) {
			return TableRegistry::get('CategoryTransactionJournal')->sumTransactionsByCategoryId($category[0]->id, $start, $end) * -1;
		}
		return '0.00';
	}

    /**
     * @param int 			$id
	 * @param Time			$start
	 * @param Time			$end
     * @param Query object
     *
     * @return array
     */
	public function getJournals($id, Time $start, Time $end)
	{
		return TableRegistry::get('Transactions')->findByAccountId($id, $start, $end);
	}

	/**
	 * idByTitleAndUserId current user in session owns the account title and it is an
	 * active account
	 *
	 * @param int	  $title
	 * @param int	  $user_id
	 * @param boolean $identify
	 * @return mixed  $account_id on exists/create false on error
	 */
	public function idByTitleAndUserId($title, $user_id, $identify = true) {
		return $this->find('all')
			->where([
				'Accounts.title' => $title,
				'Accounts.user_id' => $user_id,
				'Accounts.active' => 1,
			])
			->contain('AccountTypes')
			->first();
	}

    /**
     * @param TransactionType $type
     * @param array           $data
     *
     * @return array
     */
    public function storeAccounts(Entity $type, array $data)
    {
        $fromAccount = null;
        $toAccount   = null;
		#Log::write('debug', 'type->type: ' . $type->type);
		#Log::write('debug', serialize($data));
        switch ($type->type) {
            case 'Withdrawal':
                list($fromAccount, $toAccount) = $this->storeWithdrawalAccounts($data);
                break;

            case 'Refund':
                list($fromAccount, $toAccount) = $this->storeWithdrawalAccounts($data);
                break;
				
            case 'Deposit':
                list($fromAccount, $toAccount) = $this->storeDepositAccounts($data);
                break;

            case 'Transfer':
				$fromAccount = $this->idByTitleAndUserId($data['accounts'][1]['_titles'], $data['user_id']);
                $toAccount 	 = $this->idByTitleAndUserId($data['accounts'][0]['_titles'], $data['user_id']);
                break;
        }

        if (is_null($toAccount)) {
            Log::write('error', '"to"-account is null, so we cannot continue!');
            Log::write('error', serialize($data));
            //abort(500, '"to"-account is null, so we cannot continue!');
			return false;
        }

        if (is_null($fromAccount)) {
            Log::write('error', '"from"-account is null, so we cannot continue!');
            Log::write('error', serialize($data));
            //abort(500, '"from"-account is null, so we cannot continue!');
			return false;
        }

        return [$fromAccount, $toAccount];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function storeDepositAccounts(array $data)
    {
		$toAccount = $this->idByTitleAndUserId($data['accounts'][0]['_titles'], $data['user_id']);
		#Log::write('debug', '"to"-account id: '.$toAccount->id.'!' . __LINE__ .'-'.__FILE__);

		if (strlen($data['accounts'][1]['_titles']) > 0) {
			// Account doesn't exist, create new
			if (!$fromAccount = $this->idByTitleAndUserId($data['accounts'][1]['_titles'], $data['user_id'])) {
				$fromAccount = $this->newEntity([
					'account_type_id' 		 => 15,
					'title' 				 => $data['accounts'][1]['_titles'],
					'description'			 => '',
					'currency'				 => 'USD',
					'entered'				 => Time::now()->format('Y-m-d'),
					'user_id'				 => $data['user_id'],
					'balance'				 => '0.00',
					'active'				 => 1
				]);
				if ($fromAccount->errors()) {
					Log::write('debug', '"from"-account newEntity failed: '.json_encode($fromAccount->errors()));
				}
				if (!$this->save($fromAccount)) {
					Log::write('debug', '"from"-account save failed: '.json_encode($fromAccount->errors()));
				}
			}
			if ($fromAccount === false) $fromAccount = null;
        }

        return [$fromAccount, $toAccount];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function storeWithdrawalAccounts(array $data)
    {
        $fromAccount = $this->idByTitleAndUserId($data['accounts'][1]['_titles'], $data['user_id']);
		//Log::write('debug', '"from"-account id: '.$fromAccount->id.'!');

        if (strlen($data['accounts'][0]['_titles']) > 0) {
			// Account doesn't exist, create new
			if (!$toAccount = $this->idByTitleAndUserId($data['accounts'][0]['_titles'], $data['user_id'])) {
				$toAccount = $this->newEntity([
						'account_type_id' 		 => 14,
						'title' 				 => $data['accounts'][0]['_titles'],
						'description'			 => '',
						'currency'				 => 'USD',
						'entered'				 => Time::now()->format('Y-m-d'),
						'user_id'				 => $data['user_id'],
						'balance'				 => '0.00',
						'active'				 => 1
				]);
				if ($toAccount->errors()) {
					Log::write('debug', '"to"-account newEntity failed: '.json_encode($toAccount->errors()));
					//return $toAccount;
				}
				if (!$this->save($toAccount)) {
					Log::write('debug', '"to"-account save failed: '.json_encode($toAccount->errors()));
					//return $toAccount;
				}
			}
			if ($toAccount === false) $toAccount = null;
        }

        return [$fromAccount, $toAccount];
    }

	/**
	 * isOwnedBy current user in session owns the account_id
	 *
	 * @param int	$account_id
	 * @param int	$user_id
	 * @return boolean true on owner
	 */
	public function isOwnedBy($account_id, $user_id)
	{
		return $this->exists(['id' => $account_id, 'user_id' => $user_id]);
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
		$rules->add($rules->existsIn('account_type_id', 'accounttypes'), [
			'errorField' => 'account_type_id',
			'message' => 'Please select an account type from the drop down box.'
		]);
		$rules->add($rules->isUnique(['title', 'user_id', 'account_type_id']), [
			'errorField' => 'title',
			'message' => 'Your account titles have to be unique for every type of account.'
		]);
		return $rules;
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		if (isset($data['id'])) {
			$data['account_type_id'] = (int)$data['account_type']['id'];
			unset($data['id']);
			unset($data['account_type']['id']);
		} else {
			$data['account_type_id'] = (int)$data['accountRole'];
		}
		$data['currency'] = substr(preg_replace('/[^A-Z]/', '', strtoupper($data['currency'])), 0, 3);
		$data['balance'] = sprintf('%0.2f', $data['balance']);
		$data['user_id'] = Configure::read('GlobalAuth.id');
		$data['entered'] = $data['entered'] == '' ? Time::now()->format('Y-m-d') : $data['entered'];
		$data['currency'] = $data['currency'] == '' ? $this->Preferences->get('currency', 'USD') : $data['currency'];
		$data['interest'] = '0.00';
		$data['billing'] = Time::now()->format('Y-m-d');

		if ($data['type'] == 'asset' && $data['no_change'] !== true) {
			$data['account_meta'][0]['title'] 	  = 'accountRole';
			$data['account_meta'][0]['data'] 	  = Configure::read('lf.accountRoleById.' . $data['account_type_id']);
			if ($data['account_type_id'] == '1') {
				$data['account_meta'][1]['title'] = 'ccMonthlyPaymentDate';
				$data['account_meta'][1]['data']  = Time::now()->format('Y-m-d');
				$data['account_meta'][2]['title'] = 'ccType';
				$data['account_meta'][2]['data']  = 'monthlyFull';
			}
		}
	}
}

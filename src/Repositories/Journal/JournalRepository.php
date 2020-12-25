<?php
namespace App\Repositories\Journal;

use App\Log\Engine\DatabaseLog;
use App\Model\Entity\TransactionJournal;
use App\Model\Entity\TransactionType;
use App\Model\Table\Account;
use App\Model\Table\AccountTypesTable;
use App\Model\Table\BudgetsTable;
use App\Model\Table\CategoriesTable;
use App\Model\Table\CategoryTransactionJournalTable;
use App\Model\Table\TransactionsTable;
use App\Model\Table\TransactionJournalsTable;
use App\Model\Table\TransactionTypesTable;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Class JournalRepository
 *
 * @package App\Repositories\Journal
 */
class JournalRepository
{

	/**
	 * Check totals for the transaction match
	 *
	 * @param array
	 * @return bool true on no errors
	 */
	public function checkTotalsMatch(array $data)
	{
		$total_debits = round(array_sum(array_column($data['transactions'], 'debit')), 2);
		$total_credits = round(array_sum(array_column($data['transactions'], 'credit')), 2);
		// check debits == credits
		if ($total_debits != $total_credits) {
			return false;
		}
		return true;
	}

	/**
	 * Check if the transaction is split, require existing accounts
	 *
	 * @param array
	 * @return bool true on no errors
	 */
	public function checkAccountsExist(array $data)
	{
		// Loop through accounts, if no account throw error?
		foreach ($data['transactions'] as $transaction) {
			// Throws null when none exist, one null is return false
			if (null === TableRegistry::get('Accounts')->idByTitleAndUserId($transaction['account']['title'], Configure::read('GlobalAuth.id'))) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param TransactionJournal $journal
	 *
	 * @return bool
	 */
	public function delete(TransactionJournal $journal)
	{
		TableRegistry::get('TransactionJournals')->delete($journal);

		return true;
	}

	/**
	 * Get users first transaction journal
	 *
	 * @return TransactionJournal
	 */
	public function first()
	{
		return TableRegistry::get('TransactionJournals')
			->find()
			->where([
				'TransactionJournals.user_id' => Configure::read('GlobalAuth.id')
			])
			->order([
				'TransactionJournals.entered' => 'ASC'
			])
			->first();
	}

    /**
     * @param TransactionJournal $journal
     * @param Transaction        $transaction
     *
     * @return integer
     */
    public function getAmountBefore(TransactionJournal $journal, Transaction $transaction)
    {
        $set = $transaction->account->transactions()->leftJoin(
            'transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id'
        )
                                    ->where('transaction_journals.date', '<=', $journal->date->format('Y-m-d'))
                                    ->where('transaction_journals.order', '>=', $journal->order)
                                    ->where('transaction_journals.id', '!=', $journal->id)
                                    ->get(['transactions.*']);
        $sum = 0;
        foreach ($set as $entry) {
            $sum += $entry->amount;
        }

        return $sum;

    }

    /**
     * @param TransactionType $dbType
     *
     * @return Collection
     */
    public function getJournalsOfType(TransactionType $dbType)
    {
        return Auth::user()->transactionjournals()->where('transaction_type_id', $dbType->id)->orderBy('id', 'DESC')->take(50)->get();
    }

	public function getJournalsOfTypes(array $types, $offset = 0, $page = 0, $search = null)
	{
		$results = TableRegistry::get('TransactionJournals')
			->find()
			->autoFields(true)
			->contain([
				'Transactions.Accounts.AccountTypes',
				'TransactionCurrencies',
				'Bills',
				//'CategoryTransactionJournal.Categories.Budgets.BudgetLimits'
				'CategoryTransactionJournal.Categories'
			])
			->where(['TransactionJournals.user_id' => Configure::read('GlobalAuth.id')])
			->andWhere([
				'TransactionJournals.entered >=' => Configure::read('calendar.start'),
				'TransactionJournals.entered <=' => Configure::read('calendar.end')
			])
			->order(['TransactionJournals.entered' => 'DESC']);
		if (count($types) > 0 && !in_array('All', $types)) {
			$results->matching(
				'TransactionTypes', function ($q) use ($types) {
					return $q->where(['TransactionTypes.type IN' => $types], ['type' => 'string[]']);
				});
		} else {
			$results->contain(['TransactionTypes']);
		}

		if ($search !== null) {
			$results->matching(
				'Transactions.Accounts.AccountTypes', function ($q) use ($search) {
					return $q->where(['Accounts.title' => $search]);
				});
		} else {
			$results->contain(['Transactions.Accounts.AccountTypes']);
		}

		return $results;
	}

	/**
	 * @param array $data
	 *
	 * @return TransactionJournal
	 */
	public function store(array $data)
	{
		// find transaction type.
		$transactionType = TableRegistry::get('TransactionTypes')->get($data['transaction_type']['id']);
		// Refund requires asset account
		if (in_array($transactionType->type, ['Refund', 'Deposit'])) {
			if (!TableRegistry::get('Accounts')->idByTitleAndUserId($data['transactions'][0]['account']['title'], Configure::read('GlobalAuth.id'))) {
				return $result = ['errors' => 'Refund/deposit transactions require an existing account, create: "'.h($data['transactions'][0]['account']['title']).'" before completing transaction'];
			}
		}
		if (!$this->checkTotalsMatch($data)) {
			return $result = ['errors' => 'Funds to and from must be equal before saving this transaction'];
		}

		$conn = ConnectionManager::get('default');
		$conn->begin();

		// store journal
		$transactionJournal = TableRegistry::get('TransactionJournals')->store($data);
		if ($transactionJournal->errors()) {
			// return the errors
			$conn->rollback();
			return $result = ['errors' => $transactionJournal->errors()];
		}

		// place transaction_journal id in data array
		$data['transaction_journal_id'] = $transactionJournal->id;

		$categoryTransactionJournal = TableRegistry::get('CategoryTransactionJournal')->store($data);
		if ($categoryTransactionJournal->errors()) {
			// return the errors
			$conn->rollback();
			return $result = ['errors' => $categoryTransactionJournal->errors()];
		}

		// split transaction (more than 2 sides)? Check if accounts exist
		if (count($data['transactions']) > 2) {
			if (!$this->checkAccountsExist($data)) {
				return $result = ['errors' => 'Transactions with more than three accounts require all accounts exist. Please create the accounts and try again.'];
			}
		} else {
			// accounts are next if only 2 transactions 0 = from, 1 = to.
			list($fromAccount, $toAccount) = $this->storeAccounts($transactionType, $data);
			if ($fromAccount->errors()) {
				$conn->rollback();
				return $result = ['errors' => $fromAccount->errors()];
			}
			if ($toAccount->errors()) {
				$conn->rollback();
				return $result = ['errors' => $toAccount->errors()];
			}
		}
		// reverse array for proper storage
		$data['transactions'] = array_reverse($data['transactions'], true);

		// Loop through and save transactions, accounts exist
		foreach ($data['transactions'] as $journal) {
			$journal['type'] = $transactionType->type;
			$journal['amount'] = 0.00;
			$journal['account'] = TableRegistry::get('Accounts')->idByTitleAndUserId($journal['account']['title'], Configure::read('GlobalAuth.id'));
			$journal['transaction_journal_id'] = $data['transaction_journal_id'];
			$entry = $this->storeTransaction($journal);
			if ($entry->errors()) {
				$conn->rollback();
				return $result = ['errors' => $entry->errors()];
			}
		}
		$conn->commit();

		return true;
    }

	/**
	 * @param array $data
	 *
	 * @return Transaction
	 */
	private function storeTransaction(array $data)
	{
		/** @var Transaction $transaction */
		$Transactions = TableRegistry::get('Transactions');

		// assets and expenses on a withdrawal are debits (funds applied to) positive value
		// House (asset), Closing costs (expense)
		// liabilities and income on a withdrawal are credits (funds from) negative value
		// Mortgage (liability), USAA Checking (asset, but credit not debit)
		$Transaction = $Transactions->newEntity([
			'account_id' 			 => $data['account']->id,
			'description'			 => $data['description'],
			'transaction_journal_id' => $data['transaction_journal_id'],
			'amount'				 => $data['amount'],
			'credit'				 => $data['credit'],
			'debit'					 => $data['debit'],
			'active'				 => 1
		]);
 		if (!$Transactions->save($Transaction)) {
			Log::write('error', 'File: ' . __FILE__ . '|||Line: ' . __LINE__ . '|||Data: ' . json_encode($Transaction->errors()));
		}
		return $Transaction;
	}

	/**
	 * @param TransactionJournal $journal
	 * @param array              $data
	 *
	 * @return TransactionJournal
	 */
	//public function update(TransactionJournal $journal, array $data)
	public function update(TransactionJournal $journal, array $data)
	{
		// find transaction type.
		$transactionType = TableRegistry::get('TransactionTypes')->get($data['transaction_type']['id']);

		// Refund requires asset account
		if (in_array($transactionType->type, ['Refund', 'Deposit'])) {
			if (!TableRegistry::get('Accounts')->idByTitleAndUserId($data['transactions'][0]['account']['title'], $data['user_id'])) {
				return $result = ['errors' => 'Refund and deposit transactions require an existing "to"-account, create the account: "'.h($data['transactions'][0]['account']['title']).'" before entering this transaction'];
			}
		}
		if (!$this->checkTotalsMatch($data)) {
			return $result = ['errors' => 'Funds to and from must be equal before saving this transaction'];
		}

		ConnectionManager::get('default')->begin();

		$data['transaction_journal_id']   = (int)$journal->id;

		// update actual journal.
		$journal->transaction_currency_id = $data['transaction_currency']['id'];
		$journal->transaction_type_id	  = $data['transaction_type']['id'];
		$journal->description             = $data['transactions'][0]['description'];
		$journal->entered                 = new Time($data['entered']);
		$journal->posted                  = new Time($data['posted']);
		$journal->completed				  = $data['completed'];

		// unlink all categories, recreate them:
		TableRegistry::get('CategoryTransactionJournal')->detach($journal->id);
		$categoryTransactionJournal = TableRegistry::get('CategoryTransactionJournal')->store($data);
		if ($categoryTransactionJournal->errors()) {
			// return the errors
			ConnectionManager::get('default')->rollback();
			return $result = ['errors' => $categoryTransactionJournal->errors()];
		}

		// split transaction (more than 2 sides)? Check if accounts exist
		if (count($data['transactions']) > 2) {
			if (!$this->checkAccountsExist($data)) {
				ConnectionManager::get('default')->rollback();
				return $result = ['errors' => 'Transactions with more than three accounts require all accounts exist. Please create the accounts and try again.'];
			}
		} else {
			// accounts are next if only 2 transactions 0 = from, 1 = to.
			list($fromAccount, $toAccount) = $this->storeAccounts($transactionType, $data);
			if ($fromAccount->errors()) {
				ConnectionManager::get('default')->rollback();
				return $result = ['errors' => $fromAccount->errors()];
			}
			if ($toAccount->errors()) {
				ConnectionManager::get('default')->rollback();
				return $result = ['errors' => $toAccount->errors()];
			}
		}
		// reverse array for proper storage
		$data['transactions'] = array_reverse($data['transactions'], true);

		$transactions = TableRegistry::get('Transactions')->findByTransactionJournalId($journal->id);
		// update the from and to transaction.
		/** @var Transaction $transaction */
		foreach ($transactions as $transaction) {
			TableRegistry::get('Transactions')->delete($transaction);
		}

		// Loop through and save transactions, accounts exist
		foreach ($data['transactions'] as $entry) {
			$entry['type'] = $transactionType->type;
			$entry['amount'] = 0.00;
			$entry['account'] = TableRegistry::get('Accounts')->idByTitleAndUserId($entry['account']['title'], Configure::read('GlobalAuth.id'));
			$entry['transaction_journal_id'] = $data['transaction_journal_id'];
			$result = $this->storeTransaction($entry);
			if ($result->errors()) {
				ConnectionManager::get('default')->rollback();
				return $result = ['errors' => $result->errors()];
			}
		}

		if (!TableRegistry::get('TransactionJournals')->save($journal)) {
			Log::write('error', 'File: ' . __FILE__ . '|||Line: ' . __LINE__ . '|||Data: ' . json_encode(
					   'Transaction data: '.$this->request->data().'\n'.'Transaction errors: '.$journal->errors()));
			return $result = ['errors' => $journal->errors()];
		}
		ConnectionManager::get('default')->commit();

		return true;
	}

	/**
	 * @param TransactionType $type
	 * @param array           $data
	 *
	 * @return array
	 */
	protected function storeAccounts(TransactionType $type, array $data)
	{
		$fromAccount = null;
		$toAccount   = null;
		switch ($type->type) {
			//// #### NEED ENTERED DATE FOR EXPRESS METHODS #### ////
            case 'Withdrawal':
				$toAccount = $this->storeExpressWithdrawalAccount($data);
				$fromAccount = $this->storeExpressDepositAccount($data);
				break;

            case 'Refund':
				$toAccount = $this->storeExpressWithdrawalAccount($data);
				$fromAccount = $this->storeExpressDepositAccount($data);
                break;
				
            case 'Deposit':
				$toAccount = $this->storeExpressWithdrawalAccount($data);
				$fromAccount = $this->storeExpressDepositAccount($data);
                break;

            case 'Transfer':
                $toAccount 	 = TableRegistry::get('Accounts')->idByTitleAndUserId($data['transactions'][1]['account']['title'], Configure::read('GlobalAuth.id'));
				$fromAccount = TableRegistry::get('Accounts')->idByTitleAndUserId($data['transactions'][0]['account']['title'], Configure::read('GlobalAuth.id'));
                break;
		}

		if ($toAccount->errors()) {
			// @codeCoverageIgnoreStart
			Log::write('error', '"to"-account: Transaction Type: (('.$type->type.'))(((('.$data['transactions'][1]['account']['title'].')))) is null, so we cannot continue!');
			// @codeCoverageIgnoreEnd
		}

		if ($fromAccount->errors()) {
			// @codeCoverageIgnoreStart
			Log::write('error', '"from"-account: (Transaction Type: (('.$type->type.'))((('.$data['transactions'][0]['account']['title'].')))) is null, so we cannot continue!');
			// @codeCoverageIgnoreEnd
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
		$Accounts = TableRegistry::get('Accounts');
        $fromAccount = $Accounts->idByTitleAndUserId($data['transactions'][0]['account']['title'], Configure::read('GlobalAuth.id'));

        if (strlen($data['transactions'][1]['account']['title']) > 0) {
			// Account doesn't exist, create new
			if (!$toAccount = $Accounts->idByTitleAndUserId($data['transactions'][1]['account']['title'], Configure::read('GlobalAuth.id'))) {
				if (!$toAccount = $Accounts->newEntity([
						'account_type_id' 		 => 14,
						'title' 				 => $data['transactions'][1]['account']['title'],
						'description'			 => '',
						'currency'				 => 'USD',
						'user_id'				 => Configure::read('GlobalAuth.id'),
						'balance'				 => '0.00',
						'active'				 => 1
					])
				) {
					Log::write('debug', '"to"-account newEntity failed: '.json_encode($toAccount->errors()));
					return false;
				}
				if (!$Accounts->save($toAccount)) {
					Log::write('debug', '"to"-account save failed: '.json_encode($toAccount->errors()));
					return false;
				}
			}
        }

        return [$fromAccount, $toAccount];
    }

	/**
	 * @param array $data
	 *
	 * @return Entity	$account
	 */
	protected function storeExpressWithdrawalAccount(array $data)
	{
		$Accounts = TableRegistry::get('Accounts');
		/** @var Account **/
		if ($account = $Accounts->idByTitleAndUserId($data['transactions'][1]['account']['title'], Configure::read('GlobalAuth.id'))) {
			return $account;
		}

		// Account doesn't exist, create new
		$entity = [
			'accountRole'	 	=> 14,
			'title' 			=> $data['transactions'][1]['account']['title'],
			'description'		=> '',
			'currency'			=> 'USD',
			'entered'			=> $data['entered'],
			'user_id'			=> Configure::read('GlobalAuth.id'),
			'balance'			=> '0.00',
			'active'			=> 1,
			'type'				=> 'expense'
		];

		if (!$account = $Accounts->newEntity($entity)) {
			Log::write('debug', '"' . __FILE__ . '.storeExpressWithdrawalAccount"-newEntity failed line: '. __LINE__ .': '.json_encode($account->errors()));
		}
		if (!$Accounts->save($account)) {
			Log::write('debug', '"' . __FILE__ . '.storeExpressWithdrawalAccount"-save failed line: '. __LINE__ .':'.json_encode($account->errors()));
		}

		return $account;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function storeExpressDepositAccount(array $data)
    {
		$Accounts = TableRegistry::get('Accounts');
		/** @var Account **/
		if ($account = $Accounts->idByTitleAndUserId($data['transactions'][0]['account']['title'], Configure::read('GlobalAuth.id'))) {
			return $account;
		}

		// Account doesn't exist, create new
		$entity = [
			'accountRole'	 	=> 15,
			'title' 			=> $data['transactions'][0]['account']['title'],
			'description'		=> '',
			'currency'			=> 'USD',
			'entered'			=> $data['entered'],
			'user_id'			=> Configure::read('GlobalAuth.id'),
			'balance'			=> '0.00',
			'active'			=> 1,
			'type'				=> 'revenue'
		];

		if (!$account = $Accounts->newEntity($entity)) {
			Log::write('debug', '"' . __FILE__ . '.storeExpressRevenueAccount"-newEntity failed line: '. __LINE__ .': '.json_encode($account->errors()));
		}
		if (!$Accounts->save($account)) {
			Log::write('debug', '"' . __FILE__ . '.storeExpressRevenueAccount"-save failed line: '. __LINE__ .':'.json_encode($account->errors()));
		}

		return $account;
    }

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function storeDepositAccounts(array $data)
	{
		$Accounts = TableRegistry::get('Accounts');
        $toAccount = $Accounts->idByTitleAndUserId($data['transactions'][1]['account']['title'], Configure::read('GlobalAuth.id'));

		if (strlen($data['transactions'][0]['account']['title']) > 0) {
			// Account doesn't exist, create new
			if (!$fromAccount = $Accounts->idByTitleAndUserId($data['transactions'][0]['account']['title'], Configure::read('GlobalAuth.id'))) {
				$fromAccount = $Accounts->newEntity([
					'account_type_id' 		 => 15,
					'title' 				 => $data['transactions'][0]['account']['title'],
					'description'			 => '',
					'currency'				 => 'USD',
					'user_id'				 => Configure::read('GlobalAuth.id'),
					'balance'				 => '0.00',
					'active'				 => 1
				]);
				if ($fromAccount->errors()) {
					Log::write('debug', '"from"-account newEntity failed: '.json_encode($fromAccount->errors()));
					return false;
				}
				if (!$this->save($fromAccount)) {
					Log::write('debug', '"from"-account save failed: '.json_encode($fromAccount->errors()));
					return false;
				}
			}
			if ($fromAccount === false) $fromAccount = null;
        }

        return [$fromAccount, $toAccount];
    }
}

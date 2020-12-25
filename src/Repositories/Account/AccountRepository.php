<?php
namespace App\Repositories\Account;

use App\Model\Entity\Account;
use App\Model\Table\AccountsTable;
use App\Model\Table\AccountMetaTable;
use App\Model\Table\AccountTypesTable;
use App\Model\Table\TransactionsTable;
use App\Model\Table\TransactionJournalsTable;
use App\Model\Table\TransactionTypesTable;
use App\Support\Steam;
use Cake\Collection\Iterator\SortIterator;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\I18n\Time;
use Cake\I18n\Date;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * Class AccountRepository
 *
 * @package App\Repositories\Account
 */
class AccountRepository
{

    /**
     * Moved here from account CRUD
     *
     * @param array $types
     *
     * @return int
     */
    public function count(array $types)
    {
        return TableRegistry::get('Accounts')
            ->find()
            ->where([
                'user_id' => Configure::read('GlobalAuth.id')
            ])
            ->matching(
                'AccountTypes',
                function ($q) use ($types) {
                    return $q->where(['AccountTypes.type IN' => $types], ['type' => 'string[]']);
                }
            )
            ->count();
    }

    /**
     * @param Account $account
     * @param Account $moveTo
     *
     * @return boolean
     */
    public function destroy(Account $account, Account $moveTo = null)
    {
        if (!is_null($moveTo)) {
            // update all transactions:
            #DB::table('transactions')->where('account_id', $account->id)->update(['account_id' => $moveTo->id]);
        }
        if (!is_null($account)) {
            $account->delete();
        }

        return true;
    }

    /**
     * @param $accountId
     *
     * @return Account
     */
    public function find(int $accountId)
    {
        $account = TableRegistry::get('Accounts')
            ->find()
            ->where([
                'id' => $accountId,
                'user_id' => Configure::read('GlobalAuth.id')
            ]);

        if (is_null($account)) {
            return new Account;
        }

        return $account;
    }

    /**
     * @param string $title
     * @param array  $types
     *
     * @return Account
     */
    public function findByTitle(string $title, array $types)
    {
        $account = TableRegistry::get('Accounts')
            ->find()
            ->contain('AccountMeta')
            ->where([
                'Accounts.user_id' => Configure::read('GlobalAuth.id'),
                'Accounts.title' => $title
            ]);

        if (count($types) > 0) {
            $account = $account->matching('AccountTypes', function ($q) use ($types) {
                return $q->where(
                    ['AccountTypes.type IN' => $types],
                    ['AccountTypes.type' => 'string[]']
                );
            });
        }

        if (is_null($account)) {
            return new Account;
        }
        return $account;
    }

    /**
     * @param array $accountIds
     *
     * @return Collection
     */
    public function getAccountsById(array $accountIds)
    {
        $result = TableRegistry::get('Accounts')
            ->find()
            ->where([
                'Accounts.user_id' => Configure::read('GlobalAuth.id'),
            ]);

        if (count($accountIds) > 0) {
            $result = $result->where(['Accounts.id IN' => $accountIds]);
        }

        $result = $result->sortBy(
            function (Account $account) {
                return strtolower($account->title);
            }
        );

        return $result;
    }

    /**
     * @param array $types
     *
     * @return Collection
     */
    public function getAccountsByType(array $types)
    {
        /** @var Collection $result */
        $query = $this->user->accounts();
        if (count($types) > 0) {
            $query->accountTypeIn($types);
        }

        $result = $query->get(['accounts.*']);
        $result = $result->sortBy(
            function (Account $account) {
                return strtolower($account->name);
            }
        );

        return $result;
    }

    /**
     * @param array $types
     *
     * @return Collection
     */
    public function getActiveAccountsByType(array $types)
    {
        /** @var Collection $result */
        $query = $this->user->accounts()->with(
            ['accountmeta' => function (HasMany $query) {
                $query->where('name', 'accountRole');
            }]
        );
        if (count($types) > 0) {
            $query->accountTypeIn($types);
        }
        $query->where('active', 1);
        $result = $query->get(['accounts.*']);
        $result = $result->sortBy(
            function (Account $account) {
                return strtolower($account->name);
            }
        );

        return $result;
    }

    /**
     * @param int 			$id
     * @return array
     */
    public function getAccountAndType($id)
    {
        return TableRegistry::get('Accounts')
            ->find()
            ->where(['Accounts.id' => (int)$id])
            ->contain([
                'AccountTypes'
            ])
            ->first();
    }

    /**
     * @param array $types
     *
     * @return Collection
     */
    public function getAccounts(array $roles, array $types = [1])
    {
        $result = TableRegistry::get('Accounts')
            ->find()
            ->contain('AccountMeta')
            ->where([
                'Accounts.user_id' => Configure::read('GlobalAuth.id'),
            ])
            ->order(['Accounts.title' => 'ASC'])
            ->matching('AccountTypes', function ($q) use ($roles, $types) {
                return $q->where(
                    [
                    'AccountTypes.role IN' => $roles,
                    'AccountTypes.type IN' => $types
                ],
                    ['AccountTypes.role' => 'string[]', 'AccountTypes.type' => 'integer[]']
                );
            });

        return $result;
    }

    /**
     * @param array $types
     *
     * @return Collection
     */
    public function getAccountsByAccountType(array $types)
    {
        $result = TableRegistry::get('AccountTypes')
            ->find()
            ->where(
                ['AccountTypes.title IN' => $types],
                ['AccountTypes.title' => 'string[]']
            )
            ->order(['AccountTypes.title' => 'ASC'])
            ->contain([
                'Accounts' => function ($query) {
                    return $query
                        ->where(['Accounts.user_id' => Configure::read('GlobalAuth.id')]);
                }
            ]);
        /* 		->matching('Accounts', function ($q) {
                        return $q->where(
                            ['Accounts.user_id' => Configure::read('GlobalAuth.id')]
                        );
                    }); */

        return $result;
    }

    /**
     * calculateCommonBalance
     * Grab balance based on account type 2,3,4 are all loans,
     * only matching categories come off of balance
     *
     * @param Entity	$account
     * @param Time		$start
     * @param Time		$end
     * @return decimal
     */
    public function calculateCommonBalance(Account $account, Time $start = null, Time $end = null)
    {
        // Return 0 on null values
        if ($start === null && $end === null) {
            return '0.00';
        }
        // If $end is null, determine the account range.
        if ($end === null) {
            $end = $start;
            $start = new Time($account->entered);
        }

        $periodBalance = $account->balance + $this->getBalanceAdjust($account, $start, $end);
        return $periodBalance;
    }

    /**
     * getBalanceAdjust
     * This will sum the current date range of transactions for a given account
     * @param Entity $account
     * @param Time	 $start
     * @param Time	 $end
     *
     * @return decimal
     */
    public function getBalanceAdjust(Account $account, Time $start = null, Time $end = null)
    {
        $transactionTotal = TableRegistry::get('Transactions')->sumTransactionsByAccountId($account->id, $start, $end)->toArray();
        if (in_array($account->account_type_id, [1,2,3,4,5,6,7,8,9,10,11,12,13])) {
            $total = $transactionTotal[0]['debits'] - $transactionTotal[0]['credits'];
        } else {
            $total = $transactionTotal[0]['debits'] - $transactionTotal[0]['credits'];
        }
        return $total;
    }

    /**
     * getBalanceAdjustLoan
     * This will sum the current date range of transactions for a given loan account
     * @param Entity $account
     * @param Time	 $start
     * @param Time	 $end
     *
     * 2,3,4 are all loans, only matching categories come off of balance
     * @return decimal
     */
    public function getBalanceAdjustLoan(Account $account, Time $start = null, Time $end = null)
    {
        $balance = 0;
        $category = TableRegistry::get('Categories')->findByTitle($account->title);
        if (is_array($category) && isset($category[0])) {
            $balance += TableRegistry::get('CategoryTransactionJournal')->sumTransactionsByCategoryId($category[0]->id, $start, $end) * -1;
        }
        // Add in transfers to balance. Issue #107
        $balance = sprintf('%0.2f', ($balance + TableRegistry::get('Transactions')->sumTransactionsByTransactionTypeId($account->id, 3, $start, $end)));

        return $balance;
    }

    /**
     * @param Account            $account
     *
     * @return Transaction
     */
    public function getFirstTransaction(Account $account)
    {
        return TableRegistry::get('TransactionJournals')->findFirstAccountTransaction($account->id);
    }

    /**
     * getLastAccountTransaction
     *
     * @param Entity object		$account
     */
    public function getLastAccountTransaction(Account $account)
    {
        $last = TableRegistry::get('TransactionJournals')->findLastAccountTransaction($account);
        return $last === null ? 'Never' : $last;
    }

    /**
     * @param Account $account
     * @param Time	  $start
     * @param Time	  $end
     *
     * @return Collection
     */
    public function getJournals(Account $account, Time $start, Time $end)
    {
        return TableRegistry::get('Transactions')->findByAccountId($id, $start, $end);
    }

    /**
     * Get the accounts of a user that have piggy banks connected to them.
     *
     * @return Collection
     */
    public function getPiggyBankAccounts()
    {
        $ids        = [];
        $start      = clone Session::get('start', new Carbon);
        $end        = clone Session::get('end', new Carbon);
        $accountIds = DB::table('piggy_banks')->distinct()->get(['piggy_banks.account_id']);
        $accounts   = new Collection;

        /** @var PiggyBank $id */
        foreach ($accountIds as $id) {
            $ids[] = intval($id->account_id);
        }

        $cache = new CacheProperties;
        $cache->addProperty($ids);
        $cache->addProperty('piggyAccounts');
        if ($cache->has()) {
            return $cache->get(); // @codeCoverageIgnore
        }

        $ids = array_unique($ids);
        if (count($ids) > 0) {
            $accounts = Auth::user()->accounts()->whereIn('id', $ids)->get();
        }
        bcscale(2);

        $accounts->each(
            function (Account $account) use ($start, $end) {
                $account->startBalance = Steam::balance($account, $start, true);
                $account->endBalance   = Steam::balance($account, $end, true);
                $account->piggyBalance = 0;
                /** @var PiggyBank $piggyBank */
                foreach ($account->piggyBanks as $piggyBank) {
                    $account->piggyBalance += $piggyBank->currentRelevantRep()->currentamount;
                }
                // sum of piggy bank amounts on this account:
                // diff between endBalance and piggyBalance.
                // then, percentage.
                $difference          = bcsub($account->endBalance, $account->piggyBalance);
                $account->difference = $difference;
                $account->percentage = $difference != 0 && $account->endBalance != 0 ? round((($difference / $account->endBalance) * 100)) : 100;
            }
        );

        $cache->store($accounts);

        return $accounts;
    }

    /**
     * Get all transfers TO this account in this range.
     *
     * @param Account $account
     * @param Carbon  $start
     * @param Carbon  $end
     *
     * @return Collection
     */
    public function getTransfersInRange(Account $account, Carbon $start, Carbon $end)
    {
        $set      = TransactionJournal::whereIn(
            'id',
            function (Builder $q) use ($account, $start, $end) {
                $q->select('transaction_journals.id')
              ->from('transactions')
              ->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id')
              ->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id')
              ->where('transactions.account_id', $account->id)
              ->where('transaction_journals.user_id', Auth::user()->id)
              ->where('transaction_journals.date', '>=', $start->format('Y-m-d'))
              ->where('transaction_journals.date', '<=', $end->format('Y-m-d'))
              ->where('transaction_types.type', 'Transfer');
            }
        )->get();
        $filtered = $set->filter(
            function (TransactionJournal $journal) use ($account) {
                if ($journal->destination_account->id == $account->id) {
                    return $journal;
                }

                return null;
            }
        );

        return $filtered;
    }

    /**
     * @param Account $account
     * @param Carbon  $date
     *
     * @return float
     */
    public function leftOnAccount(Account $account, Carbon $date)
    {
        $balance = Steam::balance($account, $date, true);
        /** @var PiggyBank $p */
        foreach ($account->piggybanks()->get() as $p) {
            $balance -= $p->currentRelevantRep()->currentamount;
        }

        return $balance;
    }

    /**
     * Returns the date of the very last transaction in this account.
     *
     * @param Account $account
     *
     * @return Date
     */
    public function newestJournalDate(Account $account)
    {
        $last = new Date;
        $date = $account->transactions()
                        ->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id')
                        ->orderBy('transaction_journals.date', 'DESC')
                        ->orderBy('transaction_journals.order', 'ASC')
                        ->orderBy('transaction_journals.id', 'DESC')
                        ->first(['transaction_journals.date']);
        if (!is_null($date)) {
            $last = new Date($date->date);
        }

        return $last;
    }

    /**
     * Returns the date of the very first transaction in this account.
     *
     * @param Account $account
     *
     * @return Date
     */
    public function oldestJournalDate(Account $account)
    {
        $first = new Date;
        $date  = $account->transactions()
                         ->leftJoin('transaction_journals', 'transaction_journals.id', '=', 'transactions.transaction_journal_id')
                         ->orderBy('transaction_journals.date', 'ASC')
                         ->orderBy('transaction_journals.order', 'DESC')
                         ->orderBy('transaction_journals.id', 'ASC')
                         ->first(['transaction_journals.date']);
        if (!is_null($date)) {
            $first = new Date($date->date);
        }

        return $first;
    }

    /**
     * @param array $data
     *
     * @return Account
     */
    public function store(array $data)
    {
        $account = $this->storeAccount($data);

        return $account;
    }

    /**
     * @param Account $account
     * @param array   $data
     *
     * @return Account
     */
    public function update(Account $account, array $data)
    {
        // update the account:
        $account->name            = $data['name'];
        $account->active          = $data['active'] == '1' ? true : false;
        $account->virtual_balance = $data['virtualBalance'];
        $account->iban            = $data['iban'];
        $account->save();

        $this->updateMetadata($account, $data);
        $openingBalance = $this->openingBalanceTransaction($account);

        // if has openingbalance?
        if ($data['openingBalance'] != 0) {
            // if opening balance, do an update:
            if ($openingBalance) {
                // update existing opening balance.
                $this->updateInitialBalance($account, $openingBalance, $data);
            } else {
                // create new opening balance.
                $type         = $data['openingBalance'] < 0 ? 'expense' : 'revenue';
                $opposingData = [
                    'user'           => $data['user'],
                    'accountType'    => $type,
                    'name'           => $data['name'] . ' initial balance',
                    'active'         => false,
                    'iban'           => '',
                    'virtualBalance' => 0,
                ];
                $opposing     = $this->storeAccount($opposingData);
                if (!is_null($opposing)) {
                    $this->storeInitialBalance($account, $opposing, $data);
                }
            }
        } else {
            if ($openingBalance) { // opening balance is zero, should we delete it?
                $openingBalance->delete(); // delete existing opening balance.
            }
        }

        return $account;
    }

    /**
     * @param array $data
     *
     * @return Account
     */
    protected function storeAccount(array $data)
    {
        $Accounts = TableRegistry::get('Accounts');
        $account = $Accounts->newEntity();

        $data['user_id']		 = Configure::read('GlobalAuth.id');
        $data['account_type_id'] = $data['account_type_id'];
        $data['title']			 = $data['title'];
        $data['entered']		 = Time::now()->format('Y-m-d');
        $data['balance']		 = $data['balance'];
        $data['currency']		 = 'USD';
        $data['interest']		 = '0.00';
        $data['billing']		 = null;
        $data['expiration']		 = null;
        $data['active']			 = 1;

        if (in_array($data['account_type_id'], [1, 5, 6, 7, 8, 9, 11, 12, 13])) {
            $data['account_meta'][0]['title'] = 'accountRole';
            $data['account_meta'][0]['data']  = Configure::read('lf.accountRoleById.' . $data['account_type_id']);
            if ($data['account_type_id'] == 1) {
                $data['account_meta'][1]['title'] = 'ccMonthlyPaymentDate';
                $data['account_meta'][1]['data']  = Time::now()->format('Y-m-d');
                $data['account_meta'][2]['title'] = 'ccType';
                $data['account_meta'][2]['data']  = 'monthlyFull';
            }
            $account = $Accounts->patchEntity(
                $account,
                $data,
                ['associated' => ['AccountMeta']]
            );
        } else {
            $account = $Accounts->patchEntity($account, $data);
        }

        if ($Accounts->save($account)) {
            if (in_array($data['account_type_id'], ['2','3','4'])) {
                $categoryRepository = new \App\Repositories\Category\CategoryRepository;
                $data['parent_id'] = '';
                // mortgage, loan, and heloc get automatic categories ('title' and 'title Interest')
                $categoryRepository->store($data);
                $interest['title'] = $data['title'] . ' Interest';
                $interest['parent_id'] = '';
                // mortgage gets escrow as well
                $categoryRepository->store($interest);
                // Mortgage gets an escrow account as well
                if ($data['account_type_id'] == 2) {
                    $escrow['title'] = $data['title'] . ' Escrow';
                    $escrow['parent_id'] = '';
                    $categoryRepository->store($escrow);
                }
            }
        }

        return $account;
    }

    /**
     * @param Account $account
     * @param array   $data
     */
    protected function storeMetadata(Account $account, array $data)
    {
        $validFields = ['accountRole', 'ccMonthlyPaymentDate', 'ccType'];
        foreach ($validFields as $field) {
            if (isset($data[$field])) {
                $metaData = new AccountMeta(
                    [
                        'account_id' => $account->id,
                        'name'       => $field,
                        'data'       => $data[$field]
                    ]
                );
                $metaData->save();
            }
        }
    }

    /**
     * @param Account $account
     * @param array   $data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function updateMetadata(Account $account, array $data)
    {
        $validFields = ['accountRole', 'ccMonthlyPaymentDate', 'ccType'];

        foreach ($validFields as $field) {
            $entry = $account->accountMeta()->where('name', $field)->first();

            // update if new data is present:
            if ($entry && isset($data[$field])) {
                $entry->data = $data[$field];
                $entry->save();
            }
            // no entry but data present?
            if (!$entry && isset($data[$field])) {
                $metaData = new AccountMeta(
                    [
                        'account_id' => $account->id,
                        'name'       => $field,
                        'data'       => $data[$field]
                    ]
                );
                $metaData->save();
            }
        }
    }
}

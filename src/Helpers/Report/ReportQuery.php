<?php
namespace App\Helpers\Report;

use App\Model\Table\Account;
use App\Model\Table\Budget;
use App\Repositories\Budget\BudgetRepository;
use App\Support\Navigation;
use App\Support\Steam;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use SoftDelete\ORM\Query;

/**
 * Class ReportQuery
 *
 * @package App\Helpers\Report
 */
class ReportQuery
{

    /**
     * See ReportQueryInterface::incomeInPeriodCorrected.
     *
     * This method's length is caused mainly by the query build stuff. Therefor:
     * 
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @param Time   $start
     * @param Time   $end
     * @param bool   $includeShared
     *
     * @return Collection
     *
     */
    public function expenseInPeriodCorrected(Time $start, Time $end, $includeShared = false)
    {
        $query = $this->queryJournalsWithTransactions($start, $end);
        if ($includeShared === false) {
            $query->where(
                function (Builder $query) {
                    $query->where(
                        function (Builder $q) { // only get withdrawals not from a shared account
                            $q->where('transaction_types.type', 'Withdrawal');
                            $q->where('acm_from.data', '!=', '"sharedAsset"');
                        }
                    );
                    $query->orWhere(
                        function (Builder $q) { // and transfers from a shared account.
                            $q->where('transaction_types.type', 'Transfer');
                            $q->where('acm_to.data', '=', '"sharedAsset"');
                        }
                    );
                }
            );
        } else {
            $query->where('transaction_types.type', 'Withdrawal'); // any withdrawal is fine.
        }
        $query->orderBy('transaction_journals.date');
        $data = $query->get( // get everything
            ['transaction_journals.*', 'transaction_types.type', 'ac_to.name as name', 'ac_to.id as account_id', 'ac_to.encrypted as account_encrypted']
        );

        $data->each(
            function (TransactionJournal $journal) {
                if (intval($journal->account_encrypted) == 1) {
                    $journal->name = Crypt::decrypt($journal->name);
                }
            }
        );
        $data = $data->filter(
            function (TransactionJournal $journal) {
                if ($journal->amount != 0) {
                    return $journal;
                }

                return null;
            }
        );

        return $data;
    }

	/**
	 * This method returns all the "out" transaction journals for the given account and given period. The amount
	 * is stored in "journalAmount".
	 *
	 * @param Query $accounts
	 * @param Time  $start
	 * @param Time  $end
	 *
	 * @return Collection
	 */
	public function expense(Query $accounts, Time $start, Time $end)
	{
		$ids = $accounts->extract('id')->toArray();
		$set = TableRegistry::get('TransactionJournals')
			->find();
		$set->hydrate(false)
			->select(['debits' => $set->func()->sum('t_to.debit')])
			->where([
				'TransactionJournals.entered >=' => $start,
				'TransactionJournals.entered <=' => $end,
				'TransactionJournals.transaction_type_id <>' => 3,
				't_to.account_id IN' => $ids
			], ['t_to.account_id' => 'integer[]'])
			->join([
				't_to' => [
					'table' => 'transactions',
					'type' => 'LEFT',
					'conditions' => [
						't_to.transaction_journal_id = TransactionJournals.id',
					]
				]
			])
			->leftJoin(
				['Accounts' => 'accounts'],
				['t_to.account_id' => 'accounts.id']
			);

		return $set;
	}

	/**
	 * This method returns all the "in" transaction journals for the given account and given period. The amount
	 * is stored in "journalAmount".
	 *
	 * @param Query $accounts
	 * @param Time  $start
	 * @param Time  $end
	 *
	 * @return Collection
	 */
	public function income(Query $accounts, Time $start, Time $end)
	{
		$ids = $accounts->extract('id')->toArray();
		$set = TableRegistry::get('TransactionJournals')
			->find();
		$set->hydrate(false)
			->select(['debits' => $set->func()->sum('t_to.debit')])
			->where([
				'TransactionJournals.entered >=' => $start,
				'TransactionJournals.entered <=' => $end,
				'TransactionJournals.transaction_type_id <>' => 3,
				't_to.account_id IN' => $ids,
			], ['t_to.account_id' => 'integer[]'])
			->join([
				't_to' => [
					'table' => 'transactions',
					'type' => 'LEFT',
					'conditions' => [
						't_to.transaction_journal_id = TransactionJournals.id',
					]
				],
			])
			->leftJoin(
				['Accounts' => 'accounts'],
				['t_to.account_id' => 'accounts.id']
			);

		return $set;
	}

	/**
	 * Get a user's root categories combined with expiration related to the start and end date.
	 *
	 * @param	Time			$start
	 *
	 * @return	Entity\Category	$rows
	 */
	public function getRootCategories(Time $start = null)
	{
		$user_id = (int)Configure::read('GlobalAuth.id');
		$rows = [];
		$counter = 0;
		$titles = TableRegistry::get('Categories')->titlesNotAccounts($user_id);

		$query = TableRegistry::get('Categories')
			->find()
			->where([
				'user_id' 	   => $user_id,
				'parent_id IS' => null,
				'active' 	   => 1,
				'OR' 		   => [['expiration IS NULL'], ['expiration >=' => $start]], 
			]);
		if (count($titles) > 0) {
			$query = $query->andWhere(['title NOT IN' => $titles], ['type' => 'string[]']);
		}
		$query = $query
			->order(['lft' => 'ASC']);

		return $query;
	}

	/**
	 * Recurse user categories to get all children
	 * @see		buildChild()
	 * @access	protected
	 *
	 * @param	int			$parent_id
	 * @param	Time		$start
	 * @param	Time		$end
	 * @param	string		$range
	 *
	 * @return	mixed		array with children, false on no-children
	 */
	public function buildChild($parent_id, $start, $end, $range = '1M')
	{
		$user_id = (int)Configure::read('GlobalAuth.id');
		/** @var \App\Repositories\Budget\BudgetRepository $repository */
		$repository = new BudgetRepository;
		// Load category model
		$CategoryTable = TableRegistry::get('Categories');
		$titles = $CategoryTable->titlesNotAccounts($user_id);
		$tempTree = [];
		$tempDetails = [];

		// Get the children of a given category
		$children = $CategoryTable
			->find()
			->where([
				'user_id' 	=> $user_id,
				'parent_id' => $parent_id,
				'active' 	=> 1,
				'OR' 		=> [['expiration IS NULL'], ['expiration >=' => $start]], 
			]);
		if (count($titles) > 0) {
			$children = $children->andWhere(['title NOT IN' => $titles], ['type' => 'string[]']);
		}
		$children = $children
			->order(['lft' => 'ASC']);

		// Loop through the children
		foreach($children as $child) {
			$begin = clone $start;
			// Why this check, should never occur!!!
			if($child->id != $child->parent_id) {
				// Process the nodes
				// 'child' key will hold all the children, if any
				$tempTree[$child->lft] = [
					'id' 		 => $child->id,
					'user_id' 	 => $child->user_id,
					'parent_id'  => $child->parent_id,
					'lft' 		 => $child->lft,
					'title' 	 => $child->title,
					'profit' 	 => $child->profit,
					'expiration' => $child->expiration,
				];
				// Build in the totals here based on the recursion, has to return an array with two levels.
				// array[details] => details, which is the total of all the children below it
				// array[child] => child, this is the current output as it
				$temp = $this->buildChild($child->id, $begin, $end);
				$tempTree[$child->lft]['child'] = $temp['child'];
				// Now check for ledger entries, no children present
				if ($tempTree[$child->lft]['child'] === false) {
					// Cycle through the months in a year
					$c = 1;
					while ($begin <= $end) {
						$currentEnd = Navigation::endOfPeriod($begin, $range);
						$money 		= TableRegistry::get('CategoryTransactionJournal')->spentByCategoryNode([$child->id], $begin, $currentEnd);
						$budget		= $repository->getBudgetLimitRepetition([$child->id], $begin);
						$tempTree[$child->lft]['detail'][$c]['id'] 		= $child->id;
						$tempTree[$child->lft]['detail'][$c]['amount']  = $money[0] + $money[1];
						$tempTree[$child->lft]['detail'][$c]['budget']  = $budget;
						$tempTree[$child->lft]['detail'][$c]['entered'] = $begin;
						if (isset($tempDetails[$c]['amount']) ) {
							$tempDetails[$c]['amount'] += $money[0] + $money[1];
						} else {
							$tempDetails[$c]['amount']  = $money[0] + $money[1];
						}
						if (isset($tempDetails[$c]['budget']) ) {
							$tempDetails[$c]['budget'] += $budget;
						} else {
							$tempDetails[$c]['budget']  = $budget;
						}
						$begin = Navigation::addPeriod($begin, $range, 0);
						$c++;
					}
				} else {
					$begin = clone $start;
					// Cycle through the months in a year
					$c = 1;
					while ($begin <= $end) {
						// if the details returned is an array, add it to the current level
						if (is_array($temp['details']) ) {
							// Check if the tempTree has an amount set for each month, add to it if it does
							if (isset($tempTree[$child->lft]['detail'][$c]['amount']) ) {
								$tempTree[$child->lft]['detail'][$c]['amount'] += $temp['details'][$c]['amount'];
							} else {
								$tempTree[$child->lft]['detail'][$c]['amount']  = $temp['details'][$c]['amount'];
							}
							// Check if the tempTree has a budget set for each month, add to it if it does
							if (isset($tempTree[$child->lft]['detail'][$c]['budget']) ) {
								$tempTree[$child->lft]['detail'][$c]['budget'] += $temp['details'][$c]['budget'];
							} else {
								$tempTree[$child->lft]['detail'][$c]['budget']  = $temp['details'][$c]['budget'];
							}
							// Check if the tempDetails has an amount set for each month, add to it if it does
							if (isset($tempDetails[$c]['amount']) ) {
								$tempDetails[$c]['amount'] += $temp['details'][$c]['amount'];
							} else {
								$tempDetails[$c]['amount']  = $temp['details'][$c]['amount'];
							}
							// Check if the tempDetails has a budget set for each month, add to it if it does
							if (isset($tempDetails[$c]['budget']) ) {
								$tempDetails[$c]['budget'] += $temp['details'][$c]['budget'];
							} else {
								$tempDetails[$c]['budget']  = $temp['details'][$c]['budget'];
							}
							$begin = Navigation::addPeriod($begin, $range, 0);
							$c++;
						}
					}
				}
			}
		}
		// Return the entire child tree
		if($tempTree == []) {
			$tempTree = false;
		}
		return $array = array('child' => $tempTree, 'details' => $tempDetails);
	}

	/**
	 * Get a users categories combined with expiration related to the start and end date.
	 *
	 * @param	Time			$start
	 *
	 * @return	Entity\Category	$rows
	 */
	public function getBudgetCategories(Time $start = null)
	{
		$user_id = (int)Configure::read('GlobalAuth');
		$rows = [];
		$counter = 0;
		$titles = TableRegistry::get('Categories')->titlesNotAccounts($user_id);

		$roots = $this
			->find()
			->where([
				'user_id' 	   => $user_id,
				'parent_id IS' => null,
				'active' 	   => 1,
				'OR' 		   => [['expiration IS NULL'], ['expiration >=' => $start]], 
			]);
		if (count($titles) > 0) {
			$roots = $roots->andWhere(['title NOT IN' => $titles], ['type' => 'string[]']);
		}
		$roots = $roots
			->order(['lft' => 'ASC']);

		foreach($categories as $category) {
			$none = false;
			$rows[$counter] = $category;
			$rows[$counter]['children'] = $this
				->find('children', ['for' => $category->id])
				->find('threaded')
				->where([
					'user_id' 		=> $user_id,
					'active' 		=> 1,
					'OR' 			=> [['expiration IS NULL'], ['expiration >=' => $start]],
					'title NOT IN' 	=> $titles,
				])
				->order(['lft' => 'ASC'])
				->toArray();
			$counter++;
		}
		return $rows;
	}

	/**
	 * Get a users accounts combined with various meta-data related to the start and end date.
	 *
	 * @param Time   $start
	 * @param Time   $end
	 * @param bool   $includeShared
	 *
	 * @return Collection
	 */
	public function getAllAccounts(Time $start, Time $end, $includeShared = false)
	{
		$query = TableRegistry::get('Users')
			->find('ownedBy')
			->contain(['Accounts'])
			->matching('AccountTypes', function ($q) use ($types) {
				return $q->where(
					['AccountTypes.role' => $types]
				);
			});
		if ($includeShared === false) {
            $query->leftJoin(
                'account_meta', function (JoinClause $join) {
                $join->on('account_meta.account_id', '=', 'accounts.id')->where('account_meta.name', '=', 'accountRole');
            }
            )
                  ->where(
                      function (Builder $query) {

                          $query->where('account_meta.data', '!=', '"sharedAsset"');
                          $query->orWhereNull('account_meta.data');

                      }
                  );
        }
        $set = $query->get(['accounts.*']);
        $set->each(
            function (Account $account) use ($start, $end) {
                /**
                 * The balance for today always incorporates transactions
                 * made on today. So to get todays "start" balance, we sub one
                 * day.
                 */
                $yesterday = clone $start;
                $yesterday->subDay();

                /** @noinspection PhpParamsInspection */
                $account->startBalance = Steam::balance($account, $yesterday);
                $account->endBalance   = Steam::balance($account, $end);
            }
        );

        return $set;
    }


    /**
     * This method works the same way as ReportQueryInterface::incomeInPeriod does, but instead of returning results
     * will simply list the transaction journals only. This should allow any follow up counting to be accurate with
     * regards to tags.
     *
     * This method returns all "income" journals in a certain period, which are both transfers from a shared account
     * and "ordinary" deposits. The query used is almost equal to ReportQueryInterface::journalsByRevenueAccount but it does
     * not group and returns different fields.
     *
     * @param Time   $start
     * @param Time   $end
     * @param bool   $includeShared
     *
     * @return Collection
     */
    public function incomeInPeriodCorrected(Time $start, Time $end, $includeShared = false)
    {
        $query = $this->queryJournalsWithTransactions($start, $end);
        if ($includeShared === false) {
            // only get deposits not to a shared account
            // and transfers to a shared account.
            $query->where(
                function (Builder $query) {
                    $query->where(
                        function (Builder $q) {
                            $q->where('transaction_types.type', 'Deposit');
                            $q->where('acm_to.data', '!=', '"sharedAsset"');
                        }
                    );
                    $query->orWhere(
                        function (Builder $q) {
                            $q->where('transaction_types.type', 'Transfer');
                            $q->where('acm_from.data', '=', '"sharedAsset"');
                        }
                    );
                }
            );
        } else {
            // any deposit is fine.
            $query->where('transaction_types.type', 'Deposit');
        }
        $query->orderBy('transaction_journals.date');

        // get everything
        $data = $query->get(
            ['transaction_journals.*', 'transaction_types.type', 'ac_from.name as name', 'ac_from.id as account_id', 'ac_from.encrypted as account_encrypted']
        );

        $data->each(
            function (TransactionJournal $journal) {
                if (intval($journal->account_encrypted) == 1) {
                    $journal->name = Crypt::decrypt($journal->name);
                }
            }
        );
        $data = $data->filter(
            function (TransactionJournal $journal) {
                if ($journal->amount != 0) {
                    return $journal;
                }

                return null;
            }
        );

        return $data;
    }

    /**
     * Covers tags
     *
     * @param Account $account
     * @param Budget  $budget
     * @param Time    $start
     * @param Time    $end
     *
     * @return float
     */
    public function spentInBudgetCorrected(Account $account, Budget $budget, Time $start, Time $end)
    {

        bcscale(2);

        return bcmul(
            Auth::user()->transactionjournals()
                ->leftJoin('transactions', 'transactions.transaction_journal_id', '=', 'transaction_journals.id')
                ->leftJoin('budget_transaction_journal', 'budget_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                ->transactionTypes(['Withdrawal'])
                ->where('transactions.account_id', $account->id)
                ->before($end)
                ->after($start)
                ->where('budget_transaction_journal.budget_id', $budget->id)
                ->get(['transaction_journals.*'])->sum('amount'), -1
        );
    }

    /**
     * @param Account $account
     * @param Time  $start
     * @param Time  $end
     *
     * @return string
     */
    public function spentNoBudget(Account $account, Time $start, Time $end)
    {
        return
            Auth::user()->transactionjournals()
                ->leftJoin('transactions', 'transactions.transaction_journal_id', '=', 'transaction_journals.id')
                ->leftJoin('budget_transaction_journal', 'budget_transaction_journal.transaction_journal_id', '=', 'transaction_journals.id')
                ->transactionTypes(['Withdrawal'])
                ->where('transactions.account_id', $account->id)
                ->before($end)
                ->after($start)
                ->whereNull('budget_transaction_journal.budget_id')->get(['transaction_journals.*'])->sum('amount');
    }

    /**
     * @param Time $start
     * @param Time $end
     *
     * @return Builder
     */
    protected function queryJournalsWithTransactions(Time $start, Time $end)
    {
        $query = TransactionJournal::
        leftJoin(
            'transactions as t_from', function (JoinClause $join) {
            $join->on('t_from.transaction_journal_id', '=', 'transaction_journals.id')->where('t_from.amount', '<', 0);
        }
        )
                                   ->leftJoin('accounts as ac_from', 't_from.account_id', '=', 'ac_from.id')
                                   ->leftJoin(
                                       'account_meta as acm_from', function (JoinClause $join) {
                                       $join->on('ac_from.id', '=', 'acm_from.account_id')->where('acm_from.name', '=', 'accountRole');
                                   }
                                   )
                                   ->leftJoin(
                                       'transactions as t_to', function (JoinClause $join) {
                                       $join->on('t_to.transaction_journal_id', '=', 'transaction_journals.id')->where('t_to.amount', '>', 0);
                                   }
                                   )
                                   ->leftJoin('accounts as ac_to', 't_to.account_id', '=', 'ac_to.id')
                                   ->leftJoin(
                                       'account_meta as acm_to', function (JoinClause $join) {
                                       $join->on('ac_to.id', '=', 'acm_to.account_id')->where('acm_to.name', '=', 'accountRole');
                                   }
                                   )
                                   ->leftJoin('transaction_types', 'transaction_types.id', '=', 'transaction_journals.transaction_type_id');
        $query->before($end)->after($start)->where('transaction_journals.user_id', Auth::user()->id);

        return $query;
    }
}

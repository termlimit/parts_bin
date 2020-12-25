<?php
namespace App\Helpers\Report;

use App\Helpers\Collection\Account as AccountCollection;
use App\Helpers\Collection\Balance;
use App\Helpers\Collection\BalanceEntry;
use App\Helpers\Collection\BalanceHeader;
use App\Helpers\Collection\BalanceLine;
use App\Helpers\Collection\Bill as BillCollection;
use App\Helpers\Collection\BillLine;
use App\Helpers\Collection\Budget as BudgetCollection;
use App\Helpers\Collection\BudgetLine;
use App\Helpers\Collection\Category as CategoryCollection;
use App\Helpers\Collection\Expense;
use App\Helpers\Collection\Income;
use App\Model\Table\Account;
use App\Model\Table\Bill;
use App\Model\Table\Budget as BudgetModel;
use App\Repositories\Budget\BudgetRepository;
use App\Support\Navigation;
use Cake\Collection\Iterator\SortIterator;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Class ReportHelper
 *
 * @package App\Helpers\Report
 */
class ReportHelper
{

    /** @var ReportQueryInterface */
    protected $query;

	/**
	 * @codeCoverageIgnore
	 *
	 * @param ReportQueryInterface $query
	 *
	 */
	public function __construct()
	{
		$this->query = new ReportQuery;
	}

	/**
	 * This method generates a full report for the given period on all
	 * the users asset and cash accounts.
	 *
	 * @param Time   $date
	 * @param Time   $end
	 * @param        $range
	 *
	 * @return AccountCollection
	 */
	public function getCashFlowReport(Time $start, Time $end, $range = '1M')
	{
		/** @var \App\Repositories\Budget\BudgetRepository $repository */
		$repository = new BudgetRepository;
		// Get all active categories for YEAR (otherwise table will be off)
		$roots = $this->query->getRootCategories($start);
		// $entries collection per category;
		$entries = [];
		// Now that root nodes are found, loop through and create recursive array
		foreach($roots as $root) {
			$begin = clone $start;
			// Process the main tree node
			// 'child' key will hold all the children, if any
			$entries[$root->lft] = [
				'id' 		 => $root->id,
				'user_id' 	 => $root->user_id,
				'parent_id'  => $root->parent_id,
				'lft' 		 => $root->lft,
				'title' 	 => $root->title,
				'profit' 	 => $root->profit,
				'expiration' => $root->expiration,
			];
			// current idea means taking the buildChild and returning an array with:
			// array[0] => details, which is the total of all the children below it
			// array[1] => child, this is the current output as it
			$temp = $this->query->buildChild($root->id, $begin, $end, $range);
			$entries[$root->lft]['child'] = $temp['child'];
			$entries[$root->lft]['detail'] = $temp['details'];
			// Now check for ledger entries, no children present
			if ($entries[$root->lft]['child'] === false) {
				// Cycle through the months in a year
				$c = 1;
				while ($begin <= $end) {
					$currentEnd = Navigation::endOfPeriod($begin, $range);
					$money 		= TableRegistry::get('CategoryTransactionJournal')->spentByCategoryNode([$root->id], $begin, $currentEnd);
					$budget		= $repository->getBudgetLimitRepetition([$root->id], $begin);
					$entries[$root->lft]['detail'][$c]['id'] 	  = $root->id;
					$entries[$root->lft]['detail'][$c]['amount']  = $money[0] + $money[1];
					$entries[$root->lft]['detail'][$c]['budget']  = $budget;
					$entries[$root->lft]['detail'][$c]['entered'] = $begin;
					$begin = Navigation::addPeriod($begin, $range, 0);
					$c++;
				}
			}
		}
		return $entries;
	}

	/**
	 * This method generates a full categorized budget report for the
	 * logged in user.
	 *
	 * @param Time   $date
	 * @param Time   $end
	 * @param        $range
	 *
	 * @return AccountCollection
	 */
	public function getBudgetReport(Time $start, Time $end, $range = '1M')
	{
		/** @var \App\Repositories\Budget\BudgetRepository $repository */
		$repository = new BudgetRepository;
		// Get all active categories for YEAR (otherwise table will be off)
		$begin = clone $start;
		$roots = $this->query->getRootCategories($start);
		// $entries collection per category;
		$entries = [];
		// Now that root nodes are found, loop through and create recursive array
		foreach($roots as $root) {
			// Process the main tree node
			// 'child' key will hold all the children, if any
			$entries[$root->lft] = [
				'id' 		 => $root->id,
				'user_id' 	 => $root->user_id,
				'parent_id'  => $root->parent_id,
				'lft' 		 => $root->lft,
				'title' 	 => $root->title,
				'profit' 	 => $root->profit,
				'expiration' => $root->expiration,
			];
			// current idea means taking the buildChild and returning an array with:
			// array[0] => details, which is the total of all the children below it
			// array[1] => child, this is the current output as it
			$temp = $this->query->buildChild($root->id, $begin, $end, $range);
			$entries[$root->lft]['child'] = $temp['child'];
			$entries[$root->lft]['detail'] = $temp['details'];
			// Now check for ledger entries, no children present
			if ($entries[$root->lft]['child'] === false) {
				// Cycle through the months in a year
				$c = 1;
				while ($start <= $end) {
					$currentEnd = Navigation::endOfPeriod($start, $range);
					$money 		= TableRegistry::get('CategoryTransactionJournal')->spentByCategoryNode([$root->id], $start, $currentEnd);
					$budget		= $repository->getBudgetLimitRepetition([$root->id], $start);
					$entries[$root->lft]['detail'][$c]['id'] 	  = $root->id;
					$entries[$root->lft]['detail'][$c]['amount']  = $money[0] + $money[1];
					$entries[$root->lft]['detail'][$c]['budget']  = $budget;
					$entries[$root->lft]['detail'][$c]['entered'] = $start;
					$start = Navigation::addPeriod($start, $range, 0);
					$c++;
				}
			}
		}
		return $entries;
	}

	/**
	 * This method generates a full report for the given period on all
	 * the users asset and cash accounts.
	 *
	 * @param Time   $date
	 * @param Time   $end
	 * @param        $shared
	 *
	 * @return AccountCollection
	 */
	public function getAccountReport(Time $date, Time $end, $shared)
	{
		$accounts = $this->query->getAllAccounts($date, $end, $shared);
		$start    = '0';
		$end      = '0';
		$diff     = '0';
		bcscale(2);

		// remove cash account, if any:
		$accounts = $accounts->filter(
			function (Account $account) {
				if ($account->accountType->type != 'Cash account') {
					return $account;
				}

				return null;
			}
		);

		// summarize:
		foreach ($accounts as $account) {
			$start = bcadd($start, $account->startBalance);
			$end   = bcadd($end, $account->endBalance);
			$diff  = bcadd($diff, bcsub($account->endBalance, $account->startBalance));
		}

		$object = new AccountCollection;
		$object->setStart($start);
		$object->setEnd($end);
		$object->setDifference($diff);
		$object->setAccounts($accounts);

		return $object;
	}

    /**
     *
     * The balance report contains a Balance object which in turn contains:
     *
     * A BalanceHeader object which contains all relevant user asset accounts for the report.
     *
     * A number of BalanceLine objects, which hold:
     * - A budget
     * - A number of BalanceEntry objects.
     *
     * The BalanceEntry object holds:
     *   - The same budget (again)
     *   - A user asset account as mentioned in the BalanceHeader
     *   - The amount of money spent on the budget by the user asset account
     *
     * @param Time    $start
     * @param Time    $end
     * @param boolean $shared
     *
     * @return Balance
     */
    public function getBalanceReport(Time $start, Time $end, $shared)
    {
        $repository    = app('App\Repositories\Budget\BudgetRepositoryInterface');
        $tagRepository = app('App\Repositories\Tag\TagRepositoryInterface');
        $balance       = new Balance;

        // build a balance header:
        $header = new BalanceHeader;

        $accounts = $this->query->getAllAccounts($start, $end, $shared);
        $budgets  = $repository->getBudgets();
        foreach ($accounts as $account) {
            $header->addAccount($account);
        }

        /** @var BudgetModel $budget */
        foreach ($budgets as $budget) {
            $line = new BalanceLine;
            $line->setBudget($budget);

            // get budget amount for current period:
            $rep = $repository->getCurrentRepetition($budget, $start);
            $line->setRepetition($rep);

            // loop accounts:
            foreach ($accounts as $account) {
                $balanceEntry = new BalanceEntry;
                $balanceEntry->setAccount($account);

                // get spent:
                $spent = $this->query->spentInBudgetCorrected($account, $budget, $start, $end); // I think shared is irrelevant.

                $balanceEntry->setSpent($spent);
                $line->addBalanceEntry($balanceEntry);
            }
            // add line to balance:
            $balance->addBalanceLine($line);
        }

        // then a new line for without budget.
        // and one for the tags:
        $empty    = new BalanceLine;
        $tags     = new BalanceLine;
        $diffLine = new BalanceLine;

        $tags->setRole(BalanceLine::ROLE_TAGROLE);
        $diffLine->setRole(BalanceLine::ROLE_DIFFROLE);

        foreach ($accounts as $account) {
            $spent = $this->query->spentNoBudget($account, $start, $end);
            $left  = $tagRepository->coveredByBalancingActs($account, $start, $end);
            bcscale(2);
            $diff = bcsub($spent, $left);

            // budget
            $budgetEntry = new BalanceEntry;
            $budgetEntry->setAccount($account);
            $budgetEntry->setSpent($spent);
            $empty->addBalanceEntry($budgetEntry);

            // balanced by tags
            $tagEntry = new BalanceEntry;
            $tagEntry->setAccount($account);
            $tagEntry->setLeft($left);
            $tags->addBalanceEntry($tagEntry);

            // difference:
            $diffEntry = new BalanceEntry;
            $diffEntry->setAccount($account);
            $diffEntry->setSpent($diff);
            $diffLine->addBalanceEntry($diffEntry);

        }

        $balance->addBalanceLine($empty);
        $balance->addBalanceLine($tags);
        $balance->addBalanceLine($diffLine);

        $balance->setBalanceHeader($header);

        return $balance;
    }

    /**
     * This method generates a full report for the given period on all
     * the users bills and their payments.
     *
     * @param Time   $start
     * @param Time   $end
     *
     * @return BillCollection
     */
    public function getBillReport(Time $start, Time $end)
    {
        /** @var \App\Repositories\Bill\BillRepositoryInterface $repository */
        $repository = app('App\Repositories\Bill\BillRepositoryInterface');
        $bills      = $repository->getBills();
        $collection = new BillCollection;

        /** @var Bill $bill */
        foreach ($bills as $bill) {
            $billLine = new BillLine;
            $billLine->setBill($bill);
            $billLine->setActive(intval($bill->active) == 1);
            $billLine->setMin($bill->amount_min);
            $billLine->setMax($bill->amount_max);

            // is hit in period?
            bcscale(2);
            $set = $repository->getJournalsInRange($bill, $start, $end);
            if ($set->count() == 0) {
                $billLine->setHit(false);
            } else {
                $billLine->setHit(true);
                $amount = '0';
                foreach ($set as $entry) {
                    $amount = bcadd($amount, $entry->amount);
                }
                $billLine->setAmount($amount);
            }

            $collection->addBill($billLine);

        }

        return $collection;

    }

    /**
     * @param Time    $start
     * @param Time    $end
     * @param boolean $shared
     *
     * @return BudgetCollection
     */
    public function getBudgetReportOld(Time $start, Time $end, $shared)
    {
        $object = new BudgetCollection;
        /** @var \App\Repositories\Budget\BudgetRepositoryInterface $repository */
        $repository = app('App\Repositories\Budget\BudgetRepositoryInterface');
        $set        = $repository->getBudgets();

        bcscale(2);

        foreach ($set as $budget) {

            $repetitions = $repository->getBudgetLimitRepetitions($budget, $start, $end);

            // no repetition(s) for this budget:
            if ($repetitions->count() == 0) {
                $spent      = $repository->balanceInPeriod($budget, $start, $end, $shared);
                $budgetLine = new BudgetLine;
                $budgetLine->setBudget($budget);
                $budgetLine->setOverspent($spent);
                $object->addOverspent($spent);
                $object->addBudgetLine($budgetLine);
                continue;
            }

            // one or more repetitions for budget:
            /** @var LimitRepetition $repetition */
            foreach ($repetitions as $repetition) {
                $budgetLine = new BudgetLine;
                $budgetLine->setBudget($budget);
                $budgetLine->setRepetition($repetition);
                $expenses  = $repository->balanceInPeriod($budget, $repetition->startdate, $repetition->enddate, $shared);
                $left      = $expenses < $repetition->amount ? bcsub($repetition->amount, $expenses) : 0;
                $spent     = $expenses > $repetition->amount ? 0 : $expenses;
                $overspent = $expenses > $repetition->amount ? bcsub($expenses, $repetition->amount) : 0;

                $budgetLine->setLeft($left);
                $budgetLine->setSpent($spent);
                $budgetLine->setOverspent($overspent);
                $budgetLine->setBudgeted($repetition->amount);

                $object->addBudgeted($repetition->amount);
                $object->addSpent($spent);
                $object->addLeft($left);
                $object->addOverspent($overspent);
                $object->addBudgetLine($budgetLine);

            }

        }

        // stuff outside of budgets:
        $noBudget   = $repository->getWithoutBudgetSum($start, $end);
        $budgetLine = new BudgetLine;
        $budgetLine->setOverspent($noBudget);
        $object->addOverspent($noBudget);
        $object->addBudgetLine($budgetLine);

        return $object;
    }

    /**
     * @param Time    $start
     * @param Time    $end
     * @param boolean $shared
     *
     * @return CategoryCollection
     */
    public function getCategoryReport(Time $start, Time $end, $shared)
    {
        $object = new CategoryCollection;


        /**
         * GET CATEGORIES:
         */
        /** @var \App\Repositories\Category\CategoryRepositoryInterface $repository */
        $repository = app('App\Repositories\Category\CategoryRepositoryInterface');
        $set        = $repository->getCategories();
        foreach ($set as $category) {
            $spent = $repository->balanceInPeriod($category, $start, $end, $shared);
            $category->spent = $spent;
            $object->addCategory($category);
            $object->addTotal($spent);
        }

        return $object;
    }

    /**
     * Get a full report on the users expenses during the period.
     *
     * @param Time    $start
     * @param Time    $end
     * @param boolean $shared
     *
     * @return Expense
     */
    public function getExpenseReport($start, $end, $shared)
    {
        $object = new Expense;
        $set    = $this->query->expenseInPeriodCorrected($start, $end, $shared);
        foreach ($set as $entry) {
            $object->addToTotal($entry->amount);
            $object->addOrCreateExpense($entry);
        }

        return $object;
    }

    /**
     * Get a full report on the users incomes during the period.
     *
     * @param Time    $start
     * @param Time    $end
     * @param boolean $shared
     *
     * @return Income
     */
    public function getIncomeReport($start, $end, $shared)
    {
        $object = new Income;
        $set    = $this->query->incomeInPeriodCorrected($start, $end, $shared);
        foreach ($set as $entry) {
            $object->addToTotal($entry->amount);
            $object->addOrCreateIncome($entry);
        }

        return $object;
    }

	/**
	 * @param Time $date
	 * @param Time $end
	 *
	 * @return array
	 */
	public function listOfMonths(Time $date, Time $end = null)
	{
		$start  = clone $date;
		$end    = $end === null ? Time::now() : clone $end;
		$months = [];
		while ($start <= $end) {
			$year            = $start->year;
			$months[$year][] = [
				#'formatted' => $start->formatLocalized('%B %Y'),
				'formatted' => $start->format('M Y'),
				'month'     => $start->month,
				'year'      => $year,
			];
			$start->addMonth();
		}

		return $months;
	}
}

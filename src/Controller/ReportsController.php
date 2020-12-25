<?php
namespace App\Controller;

use App\Helpers\Report\ReportHelper;
use App\Repositories\Account\AccountRepository;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * Class ReportsController
 *
 * @package App\Controller
 */
class ReportsController extends AppController
{

	/** @var  App\Repositories\Account\AccountRepository */
    protected $repository;

	/** @var ReportHelperInterface */
    protected $helper;

	public function initialize()
	{
		parent::initialize();
		$this->helper = new ReportHelper;
	}

	public function beforeFilter(Event $event)
	{
		parent::beforeFilter($event);
		$this->set('title', 'LoadedFinance Reports');
		$this->set('mainTitleIcon', 'fa-line-chart');
	}

	public function isAuthorized($user)
	{
		// Logged in users can access
		if($user['id'] != 0)
			return true;
		$action = $this->request->params['action'];

		// The index, year, month actions are always allowed.
		if (in_array($action, ['index', 'year', 'month'])) {
			return true;
		}
		return parent::isAuthorized($user);
	}

	/**
	 * @param      $year
	 *
	 * @internal param ReportHelper $helper
	 * @return $this
	 */
	public function actual($year)
	{
		$start            = new Time('01-01-' . $year);
		$end              = clone $start;
		$what		   	  = 'Reports';
		$subTitle         = 'Yearly cash flow report for ' . $year;
		$subTitleIcon     = 'fa-bar-chart';
		$incomeTopLength  = 8;
		$expenseTopLength = 8;

		$end->endOfYear();

		$categories = $this->helper->getCashFlowReport($start, $end);

		$this->set('_charturl', $this->_charturl);
		$this->set(compact('what', 'subTitleIcon', 'subTitle', 'categories', 'start', 'year'));
	}

	/**
	 * @param      $year
	 *
	 * @internal param ReportHelper $helper
	 * @return $this
	 */
	public function budget($year)
	{
		$start            = new Time('01-01-' . $year);
		$end              = clone $start;
		$what		   	  = 'Reports';
		$subTitle         = $year . ' budget report';
		$subTitleIcon     = 'fa-bar-chart';

		$end->endOfYear();

		$categories = $this->helper->getBudgetReport($start, $end);

		$this->set('_charturl', $this->_charturl);
		$this->set(compact('what', 'subTitleIcon', 'subTitle', 'categories', 'start', 'year'));
	}

	/**
	 * @internal param AccountRepository $repository
	 * @internal param ReportHelper $helper
	 * @return View
	 */
	public function index()
	{
		$what				 = 'Reports';
		$this->repository 	 = new AccountRepository($this->Auth->user('id'));

		$start  = new Time($this->request->session()->read('first'));
		$months = $this->helper->listOfMonths($start);

		// does the user have shared accounts?
		$accounts  = $this->repository->getAccounts(['asset']);
		$hasShared = false;

		/** @var Account $account */
		foreach ($accounts as $account) {
			if ($account->account_meta[0] == 'sharedAsset') {
				$hasShared = true;
			}
		}
		$this->set('_charturl', $this->_charturl);
		$this->set(compact('what', 'months'));
	}

	/**
	 * @param string $year
	 * @param string $month
	 *
	 * @param bool   $shared
	 *
	 * @internal param ReportHelper $helper
	 * @return View
	 */
	public function month($year = '2014', $month = '1', $shared = false)
	{
		$start            = new Time($year . '-' . $month . '-01');
		$subTitle		  = 'Report for Month: ' . $start->formatLocalized($this->monthFormat);
		$subTitleIcon     = 'fa-calendar';
		$end              = clone $start;
		$incomeTopLength  = 8;
		$expenseTopLength = 8;
		if ($shared == 'shared') {
			$shared   = true;
			$subTitle = 'Shared report for Month: ' . $start->formatLocalized($this->monthFormat);
		}

		$end->endOfMonth();

		$accounts   = $this->helper->getAccountReport($start, $end, $shared);
		$incomes    = $this->helper->getIncomeReport($start, $end, $shared);
		$expenses   = $this->helper->getExpenseReport($start, $end, $shared);
		$budgets    = $this->helper->getBudgetReport($start, $end, $shared);
		$categories = $this->helper->getCategoryReport($start, $end, $shared);
		$balance    = $this->helper->getBalanceReport($start, $end, $shared);
		$bills      = $this->helper->getBillReport($start, $end);

		Session::flash('gaEventCategory', 'report');
		Session::flash('gaEventAction', 'month');
		Session::flash('gaEventLabel', $start->format('F Y'));

		return view(
			'reports.month',
			compact(
				'start', 'shared',
				'subTitle', 'subTitleIcon',
				'accounts',
				'incomes', 'incomeTopLength',
				'expenses', 'expenseTopLength',
				'budgets', 'balance',
				'categories',
				'bills'
			)
		);
	}

	/**
	 * @param      $year
	 *
	 * @param bool $shared
	 *
	 * @internal param ReportHelper $helper
	 * @return $this
	 */
	public function year($year, $shared = false)
	{
		$start            = new Carbon('01-01-' . $year);
		$end              = clone $start;
		$subTitle         = trans('app.reportForYear', ['year' => $year]);
		$subTitleIcon     = 'fa-bar-chart';
		$incomeTopLength  = 8;
		$expenseTopLength = 8;

		if ($shared == 'shared') {
			$shared   = true;
			$subTitle = trans('app.reportForYearShared', ['year' => $year]);
		}
		$end->endOfYear();

		$accounts = $this->helper->getAccountReport($start, $end, $shared);
		$incomes  = $this->helper->getIncomeReport($start, $end, $shared);
		$expenses = $this->helper->getExpenseReport($start, $end, $shared);

		Session::flash('gaEventCategory', 'report');
		Session::flash('gaEventAction', 'year');
		Session::flash('gaEventLabel', $start->format('Y'));

		return view(
			'reports.year',
			compact('start', 'shared', 'accounts', 'incomes', 'expenses', 'subTitle', 'subTitleIcon', 'incomeTopLength', 'expenseTopLength')
		);
	}
}

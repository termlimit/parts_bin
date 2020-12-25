<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Generator\Chart\Account\ChartJsAccountChartGenerator;
use App\Helpers\Report\ReportHelper;
use App\Helpers\Report\ReportQuery;
use App\Repositories\Account\AccountRepository;
use Cake\View\View;
use Cake\Event\Event;
use Cake\I18n\Number;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/**
 * Class JsonController
 *
 * @package App\Controller
 */
class JsonController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

    public function beforeFilter(Event $event)
    {
		parent::beforeFilter($event);
    }

    public function isAuthorized($user)
    {
		if($user['id'] != 0) {
			return true;
		}
		return parent::isAuthorized($user);
    }

	/**
	 * @internal param ReportQuery $reportQuery
	 * @return \Cake\Network\Response
	 */
	public function boxIn()
	{
		$start  	 = new Time($this->request->session()->read('start'));
		$end    	 = new Time($this->request->session()->read('end'));
		$repository  = new AccountRepository();
		$reportQuery = new ReportQuery;
		$amount		 = 0.00;

		$accounts = $repository->getAccounts(['asset']);
		if ($accounts->count() > 0) {
			$amount   = $reportQuery->income($accounts, $start, $end)->sumOf('debits');
		}

		$data = ['box' => 'in', 'amount' => Number::currency($amount, 'USD'), 'amount_raw' => $amount];

		if (!$this->request->is('requested')) {
			$this->response->body(json_encode($data));
			return $this->response;
		}
	}

	/**
	 * @internal param ReportQuery $reportQuery
	 * @return \Cake\Network\Response
	 */
	public function boxInYear()
	{
		$start  	 = (new Time($this->request->session()->read('start')))->startOfYear();
		$end    	 = new Time();
		$repository  = new AccountRepository();
		$reportQuery = new ReportQuery;
		$amount		 = 0.00;

		$accounts = $repository->getAccounts(['asset']);
		if ($accounts->count() > 0) {
			$amount   = $reportQuery->income($accounts, $start, $end)->sumOf('debits');
		}

		$data = ['box' => 'in_year', 'amount' => Number::currency($amount, 'USD'), 'amount_raw' => $amount];

		if (!$this->request->is('requested')) {
			$this->response->body(json_encode($data));
			return $this->response;
		}
	}

	/**
	 * @internal param ReportQuery $reportQuery
	 * @return \Cake\Network\Response
	 */
	public function boxOut()
	{
		$start  	 = new Time($this->request->session()->read('start'));
		$end    	 = new Time($this->request->session()->read('end'));
		$repository  = new AccountRepository();
		$reportQuery = new ReportQuery;
		$amount		 = 0.00;

		$accounts = $repository->getAccounts(['liability', 'expense'], [0]);
		if ($accounts->count() > 0) {
			$amount = $reportQuery->expense($accounts, $start, $end)->sumOf('debits');
		}

		$data = ['box' => 'out', 'amount' => Number::currency($amount, 'USD'), 'amount_raw' => $amount];

		if (!$this->request->is('requested')) {
			$this->response->body(json_encode($data));
			return $this->response;
		}
	}
	/**
	 * @internal param ReportQuery $reportQuery
	 * @return \Cake\Network\Response
	 */
	public function boxOutYear()
	{
		$start  	 = (new Time($this->request->session()->read('start')))->startOfYear();
		$end    	 = new Time();
		$repository  = new AccountRepository();
		$reportQuery = new ReportQuery;
		$amount		 = 0.00;

		$accounts = $repository->getAccounts(['liability', 'expense'], [0]);
		if ($accounts->count() > 0) {
			$amount = $reportQuery->expense($accounts, $start, $end)->sumOf('debits');
		}

		$data = ['box' => 'out_year', 'amount' => Number::currency($amount, 'USD'), 'amount_raw' => $amount];

		if (!$this->request->is('requested')) {
			$this->response->body(json_encode($data));
			return $this->response;
		}
	}

	/**
	 * Returns a list of categories.
	 *
	 * @param CRI $repository
	 *
	 * @return \Cake\Network\Response
	 */
	public function categories(CRI $repository)
	{
		$list   = $repository->listCategories();
		$return = [];
		foreach ($list as $entry) {
			$return[] = $entry->name;
		}

		return Response::json($return);
	}

	/**
	 * @return \Cake\Network\Response
	 */
	public function endTour()
	{
		if ($this->Preferences->set('tour', 'false')) {
			$this->response->body(json_encode('preference updated'));
		} else {
			$this->response->body(json_encode('failed'));
		}

		return $this->response;
	}

	/**
	 * Returns a JSON list of all beneficiaries.
	 *
	 * @return \Cake\Network\Response
	 */
	public function expenseAccounts()
	{
		$list	= TableRegistry::get('Accounts')->accountList(Configure::read('GlobalAuth.id'));
		$return = [];
		foreach ($list as $entry) {
			$return[] = $entry;
		}

		if (!$this->request->is('requested')) {
			$this->response->body(json_encode($return));
			return $this->response;
		}
	}

	/**
	 * @internal param ChartJsAccountChartGenerator $generator
	 * @return \Cake\Network\Response
	 */
	public function netWorth()
	{
		$end   		 = new Time($this->request->session()->read('end'));
		$start 		 = clone $end;
		$start->subYear()->startOfMonth();
		$end->endOfMonth();
		$generator 	 = new ChartJsAccountChartGenerator;

		$data	 	 = $generator->netWorth($start, $end);

		if (!$this->request->is('requested')) {
			$this->response->body(json_encode($data));
			return $this->response;
		}
	}

	/**
	 * @param ARI $accountRepository
	 *
	 * @return \Cake\Network\Response
	 */
	public function revenueAccounts(ARI $accountRepository)
	{
		$list   = $accountRepository->getAccounts(['Revenue account']);
		$return = [];
		foreach ($list as $entry) {
			$return[] = $entry->name;
		}

		return Response::json($return);
	}

	/**
	 * @return \Cake\Network\Response
	 */
	public function tour()
	{
		$view = new View($this->request, $this->response, null);
		$view->viewPath='Json';
		$view->layout=false;
		$pref = $this->Preferences->get('tour', true);
		if (!$pref) {
			abort(404);
		}
		$headers = ['main-content', 'sidebar-toggle', 'account-menu', 'budget-menu', 'report-menu', 'transaction-menu', 'option-menu', 'main-content-end'];
		$steps   = [];
		foreach ($headers as $header) {
			$steps[] = [
				'element' => '#' . $header,
				'title'   => __d('help', $header . '-title'),
				'content' => __d('help', $header . '-text'),
			];
		}
		$steps[0]['orphan']    = true;// orphan and backdrop for first element.
		$steps[0]['backdrop']  = true;
		$steps[1]['placement'] = 'left';// sidebar position left:
		$steps[7]['orphan']    = true; // final in the center again.
		$steps[7]['backdrop']  = true;
		#$template              = view('json.tour')->render();
		#$template			   = $this->render();

		$this->set('title', 'Dashboard');
		$this->set('steps', $steps);
		$this->set('_serialize', false);
		$template = $view->render('tour');
		#$this->set(compact('steps', 'template'));

 		if (!$this->request->is('requested')) {
			$this->response->body(json_encode(['steps' => $steps, 'template' => $template]));
			return $this->response;
		}
		#return Response::json(['steps' => $steps, 'template' => $template]);
	}

	/**
	 * @return \Cake\Network\Response
	 */
	public function transactionJournals()
	{
		$descriptions = [];
		$dbType       = $repository->getTransactionType($what);

		$journals = $repository->getJournalsOfType($dbType);
		foreach ($journals as $j) {
			$descriptions[] = $j->description;
		}

		$descriptions = array_unique($descriptions);
		sort($descriptions);

		return Response::json($descriptions);
	}
}

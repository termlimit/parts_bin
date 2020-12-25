<?php
namespace App\Controller;

use App\Repositories\Budget\BudgetRepository;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;

/**
 * Budgets Controller
 *
 * @package App\Controller
 */
class BudgetsController extends AppController
{
	/** @var  App\Repositories\Budget\BudgetRepository */
	protected $repository;

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
		$action = $this->request->params['action'];

		// The index, add, copyAll actions are always allowed.
		if (in_array($action, ['index', 'add', 'copyAll'])) {
			return true;
		}
		list($id, $date, $amount) = explode("_", $this->request->params['pass'][0]);

		// The owner of a budget can edit and delete it
		if (in_array($action, ['edit', 'delete', 'view', 'trash', 'amount', 'copy'])) {
			if ($this->Budgets->isOwnedBy($id, $user['id'])) {
				return true;
			}
		}
		return parent::isAuthorized($user);
    }

	/**
	 * @internal param BudgetRepository $this->repository
	 * @return Response
	 */
	public function amount($id)
	{
		$this->repository 	 = new BudgetRepository;
		#$amount          	 = intval(Input::get('amount'));
		$amount				 = $this->request->data('amount');//$this->request->data['amount'];
		$budget				 = $this->Budgets->get((int)$id, [
			'contain' => ['Categories']
		]);
		$date			 	 = clone $this->request->session()->read('start')->startOfMonth();
		$limitRepetition 	 = $this->repository->updateLimitAmount($budget, $date, $amount);
		if ($amount == 0) {
			$limitRepetition = null;
		}
		// For lastActivity of the user, implement in the future.
		$this->Preferences->mark();

		if (!$this->request->is('requested')) {
			$this->response->body(json_encode(['name' => $budget->category->title, 'repetition' => $limitRepetition ? $limitRepetition->budget_id : 0]));
			return $this->response;
		}
		$this->set(compact('limitRepetition'));
		$this->set('_serialize', 'limitRepetition');
	}

	/**
	 * copy
	 * Amount arrives either positive or negative, based on existing budget
	 * @param  string	$id [id_YYYY-MM-DD_$.$$]
	 * @internal param BudgetRepository $this->repository
	 * @return Response
	 */
	public function copy($id)
	{
		list($id, $date, $amount) = explode("_", $id);
		//if ($amount < 0) $amount = $amount*-1;
		$this->repository 	 = new BudgetRepository;
		$budget				 = $this->Budgets->get((int)$id, [
			'contain' => ['Categories']
		]);
		$date				 = Time::createFromFormat('Y-M-d', $date, $this->Preferences->get('time_zone', 'America/Los_Angeles'));
		$limitRepetition 	 = $this->repository->updateLimitAmount($budget, $date, $amount);
		if (!$limitRepetition) {
			$event = new Event('Controller.Budgets.afterCopy', $this->Controller, [
				'budget' => $Budgets
			]);
			EventManager::instance()->dispatch($event);
		}
		if ($amount == 0) {
			$limitRepetition = null;
		}
		// For lastActivity of the user, implement in the future.
		$this->Preferences->mark();

		$this->Flash->success(__('The budget has been copied to '.$date->format('Y-M').'.'));
		return $this->redirect('/budgets');
	}

	/**
	 * copyAll
	 * Copy all the budgets from a given month to another
	 * @param  string	$dates [YYYY-MM-DD_YYYY-MM-DD]
	 * @internal param BudgetRepository $this->repository
	 * @return Response
	 */
	public function copyAll($date)
	{
		$this->repository 	= new BudgetRepository;
		list($from, $to) 	= explode("_", $date);
		$from				= Time::createFromFormat('Y-M-d', $from, $this->Preferences->get('time_zone', 'America/Los_Angeles'));
		$to					= Time::createFromFormat('Y-M-d', $to, $this->Preferences->get('time_zone', 'America/Los_Angeles'));

		$budgets = $this->Budgets
			->find()
			->where([
				'Budgets.user_id' => $this->Auth->user('id'),
			])
			->contain([
				'BudgetLimits' => function ($q) use ($from) {
					return $q->where(['BudgetLimits.startdate' => $from]);
				}
			]);

		foreach ($budgets as $budget) {
			if (isset($budget->budget_limits[0]->amount)) {
				$limitRepetition = $this->repository->updateLimitAmount($budget, $to, $budget->budget_limits[0]->amount);
				if (!$limitRepetition) {
					$event = new Event('Controller.Budgets.afterCopyAll', $this->Controller, [
						'budget' => $Budgets
					]);
					EventManager::instance()->dispatch($event);
				}
			}
		}

		$this->Preferences->mark();

		$this->Flash->success(__('The budgets for '.$from->format('Y-M').' have been copied to '.$to->format('Y-M').'.'));
		return $this->redirect('/budgets');
	}

	/**
	 * @internal param BudgetRepository $this->repository
	 * @return Cake\View\View
	 */
	public function index()
	{
		// Only use start date, start of month only.
		$start   			 = clone $this->request->session()->read('start')->startOfMonth();
		$end				 = clone $start;
		$what		    	 = 'Budgets';
		$subTitle			 = 'for ';
		$subTitleIcon   	 = '';
		$this->repository 	 = new BudgetRepository;
		$categories			 = $this->loadModel('Categories')->getBudgetCategories($this->Auth->user('id'), $end);
		$budgets 			 = $this->repository->processBudgets($categories, $start, $end->endOfMonth());
		$spent    			 = '0';
		$budgeted 			 = '0';

		/**
		 * Do some cleanup:
		 */
		if (count($budgets) == 0) {
			$this->Flash->warning(__('You must create categories to set budgets.'));
			return $this->redirect('/categories/');
		}

		$budgetIncomeTotal	 = $this->repository->getEarned();
		$budgeted			 = $this->repository->getBudgeted();
		$spent 				 = $this->repository->getSpent();
		$defaultCurrency     = 'USD';

		$this->set('_charturl', $this->_charturl);
		$this->set(compact('what', 'subTitleIcon', 'subTitle', 'defaultCurrency', 'budgets', 'start', 'spent', 'budgeted', 'budgetIncomeTotal'));
	}
}

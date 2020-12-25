<?php
namespace App\Controller;

use App\Generator\Chart\Account\ChartJsAccountChartGenerator;
use App\Model\Entity\Account;
use App\Repositories\Account\AccountRepository;
use App\Repositories\Category\CategoryRepository;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Accounts Controller
 *
 * @property \App\Model\Table\AccountsTable $Accounts
 * @package App\Controller
 */
class AccountsController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

    public function beforeFilter(Event $event)
    {
		parent::beforeFilter($event);
		$this->set('mainTitleIcon', 'fa-credit-card');
		$this->repository = new AccountRepository();
    }

    public function isAuthorized($user)
    {
		$action = $this->request->params['action'];

		// The index, add actions are always allowed.
		if (in_array($action, ['index', 'add', 'closed'])) {
			return true;
		}
		// The owner of an account can edit and delete it
		if (in_array($this->request->action, ['edit', 'delete', 'moveUp', 'moveDown', 'view', 'trash'])) {
			$account_id = (int)$this->request->params['pass'][0];
			if ($this->Accounts->isOwnedBy($account_id, $user['id'])) {
				return true;
			}
		}
		return parent::isAuthorized($user);
    }

	/**
	 * Add method
	 *
	 * @return void Redirects on successful add, renders view otherwise.
	 */
	public function add()
	{
		$what		    = isset($this->request->params['pass'][0]) ? $this->request->params['pass'][0] : 'all';
		$accountRoles	= $this->loadModel('AccountTypes')->getTitles($what);
		$currencies 	= Configure::read('lf.accountCurrencies');

		$account = $this->Accounts->newEntity();
		if ($this->request->is('post') && $what != 'all') { # added check for all 2016.06.22
			// For account_meta creation, need to grab the title for accountRole, etc
			### account_types slug becomes accountRole to make it easier to lookup. Slug is not needed
			### double check changing account_meta hasOne to hasMany
			$this->request->data['role']			= $what;

			if ($what == 'asset') {
				$account = $this->Accounts->patchEntity($account, $this->request->data,
					['associated' => ['AccountMeta']]
				);
			} else {
				$account = $this->Accounts->patchEntity($account, $this->request->data);
			}
			$this->Preferences->mark();
            if ($this->Accounts->save($account)) {
				if (in_array($this->request->data['accountRole'], ['2','3','4'])) {
					$categoryRepository = new CategoryRepository;
					// mortgage, loan, and heloc get automatic categories ('title' and 'title Interest')
					$categoryRepository->store($this->request->data);
					$interest['title'] = $this->request->data['title'] . ' Interest';
					$interest['active'] = 1;
					// mortgage gets escrow as well
					$categoryRepository->store($interest);
					// Mortgage gets an escrow account as well
					if ($this->request->data['accountRole'] == 2) {
						$escrow['title'] = $this->request->data['title'] . ' Escrow';
						$escrow['active'] = 1;
						$categoryRepository->store($escrow);
					}
				}
				$this->Flash->success(__('The account has been saved.'));
				$action = $this->request->data['return'] == 1 ? 'add' : 'index';
				return $this->redirect('/accounts/'.$action.'/'.$what);
			} else {
				$event = new Event('Controller.Accounts.afterAdd', $this->Controller, [
					'account' => $account
				]);
				EventManager::instance()->dispatch($event);
				$this->Flash->error(__('There was an error with this action, please try again later, a message has been sent to the administrator.'));
				$this->set('errors', $account->errors());
            }
        }
		$subTitle				 = 'add ' . $what . ' accounts';
		$subTitleIcon   		 = Configure::read('lf.subIconsByIdentifier.' . $what);

		$this->set('_charturl', $this->_charturl);
        $this->set(compact('what', 'subTitle', 'subTitleIcon', 'account', 'accountRoles', 'currencies'));
        $this->set('_serialize', ['account']);
    }

	/**
	 * Closed method
	 *
	 * @return void
	 */
	public function closed()
	{
		$none 		    = true;
		$what		    = 'all';
		$subTitle		= $what . ' closed accounts';
		$subTitleIcon   = Configure::read('lf.subIconsByIdentifier.' . $what);
		$types          = Configure::read('lf.accountTypesByIdentifier.' . $what);
		$activities		= '';

		$accounts = $this->paginate($this->Accounts->getClosedAccounts());

		// start balances:
		foreach ($accounts as $account) {
			$none	    = false;
			// last activity:
			$activities[$account->id]['lastTransaction'] = $this->Accounts->findLastAccountTransaction($account);
		}

		$this->set(compact('accounts', 'what', 'subTitleIcon', 'subTitle', 'none', 'types', 'activities'));
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Account id.
	 * @return void Redirects to index.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function delete($id = null)
	{
		if ($this->request->is('post')) {
			$account = $this->Accounts->get($id);
			if ($this->Accounts->delete($account)) {
				$this->Flash->success(__('The account has been deleted.'));
			} else {
				$event = new Event('Controller.Accounts.afterDelete', $this->Controller, [
					'account' => $account
				]);
				EventManager::instance()->dispatch($event);
				$this->Flash->error(__('The account could not be deleted. Please, try again.'));
			}
			$this->Preferences->mark();
		}
		return $this->redirect($this->request->referer());
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Account id.
	 * @return void Redirects to index.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit($id = null)
	{
		$account				 = $this->repository->getAccountAndType((int)$id);
		$what		    		 = $account->account_type['role'];
		if ($this->request->is('put')) {
			$this->request->data['id'] = (int)$account->id;
			// Only do account_meta on change
			if ($account->account_type_id != $this->request->data['account_type']['id']) {
				$this->request->data['type'] = $what;
				$this->request->data['no_change'] = false; # beforeMarshall edit AccountMeta
				$this->loadModel('AccountMeta')->deleteByAccount($account);
				if ($what == 'asset') {
					$account = $this->Accounts->patchEntity($account, $this->request->data,
						['associated' => ['AccountMeta']]
					);
				} else {
					$account = $this->Accounts->patchEntity($account, $this->request->data);
				}
			} else {
				$this->request->data['no_change'] = true; # prevent beforeMarshall editing AccountMeta
				$account = $this->Accounts->patchEntity($account, $this->request->data);
			}
			$this->Preferences->mark();
			if ($this->Accounts->save($account)) {
				$this->Flash->success(__('The account has been saved.'));
				return $this->redirect('/accounts/index/'.$what);
			} else {
				$event = new Event('Controller.Accounts.afterEdit', $this->Controller, [
					'account' => $account
				]);
				EventManager::instance()->dispatch($event);
				$this->Flash->error(__('The account could not be saved. Please, try again.'));
				$this->set('errors', $account->errors());
			}
		}
		$accountRoles 		 	 = $this->loadModel('AccountTypes')->getTitles($what);
		$currencies 			 = Configure::read('lf.accountCurrencies');
		$subTitle				 = 'edit ' . $what . ' account';
		$subTitleIcon   		 = Configure::read('lf.subIconsByIdentifier.' . $what);
		$this->set('_charturl', $this->_charturl);
        $this->set(compact('what', 'subTitle', 'subTitleIcon', 'account', 'accountRoles', 'currencies'));
        $this->set('_serialize', ['account']);
	}

	/**
	 * Index method
	 *
	 * @return void
	 */
	public function index()
	{
		$none 		    = true;
		$what		    = isset($this->request->params['pass'][0]) ? $this->request->params['pass'][0] : 'all';
		$subTitle		= $what . ' accounts';
		$subTitleIcon   = Configure::read('lf.subIconsByIdentifier.' . $what);
		$types          = Configure::read('lf.accountTypesByIdentifier.' . $what);

		$accounts = $this->paginate($this->Accounts->getAccounts($types));

		/**
		 * HERE WE ARE
		 */
		$start = $this->request->session()->read('start');
		$end = Time::now()->endOfMonth();
		// Returns midnight on previous day (2015-08-31 00:00:00.000000) rather than 1 second later same day (2015-09-01 00:00:00.000000)
		$start->subDay();

		// start balances:
		$startBalances = [];
		$endBalances = [];
		foreach ($accounts as $account) {
			$none	    = false;
			$startBalances[$account->id] = $account->balance + $this->Accounts->getBalanceAdjust($account, $this->request->session()->read('start'));
			$endBalances[$account->id] 	 = $account->balance + $this->Accounts->getBalanceAdjust($account, $this->request->session()->read('end'));
			// last activity:
			$activities[$account->id]['lastTransaction'] = $this->Accounts->findLastAccountTransaction($account);
			$activities[$account->id]['deleteMessage']   = $activities[$account->id]['lastTransaction'] == 'Never' ? 'This account has never been used and can be safely deleted.' : 'This account is associated with one or more transactions, deleting it will affect these transactions. If you no longer need this account, you can deactivate it by editing it!';
		}

		$this->set(compact('accounts', 'what', 'subTitleIcon', 'subTitle', 'none', 'start', 'types', 'activities', 'startBalances', 'endBalances'));
		$this->set('liabilities', []);
		$this->set('assetTotal', '0.00');
		$this->set('liabilityTotal', '0.00');
		$this->set('total', '0.00');
	}

	/**
	 * View method
	 *
	 * @param string|null $id Account id.
	 * @return void
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function view($id = null)
	{
		$account		  = $this->repository->getAccountAndType($id);

		// event fire and redirect to index.
		if (!$account) {
			$event = new Event('Controller.Accounts.afterView', $this->Controller, [
				'account' => $account
			]);
			EventManager::instance()->dispatch($event);
			return $this->redirect('/accounts/index/asset');
		}

		// Will need to adjust balance out to day before the date range provided.
		// periodBalance will handle current view transaction sum
		$what		      = $account->account_type->role;
		$subTitle		  = 'view ' . $what . ' account';
		$subTitleIcon     = Configure::read('lf.subIconsByIdentifier.' . $what);
		$journals  		  = $this->Accounts->getJournals($account->id, $this->request->session()->read('start'), $this->request->session()->read('end'));
		$none			  = ($journals !== null && $journals->count() > 0) ? false : true;
		// Grab balance at the beginning of the month based on account type
		// 2,3,4 are all loans, only matching categories come off of balance
		$periodBalance 	  = $account->balance + $this->Accounts->getBalanceAdjust($account, $this->request->session()->read('start'));
		$account->balance = $account->balance + $this->Accounts->getBalanceAdjust($account, $this->request->session()->read('end'));

		// will add/subtract to while traversing resultset
		$currency = $this->Preferences->get('currency', 'USD');

		$this->set(compact('what', 'subTitle', 'subTitleIcon', 'account', 'journals', 'none', 'periodBalance', 'currency'));
	}
}

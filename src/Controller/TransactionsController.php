<?php
namespace App\Controller;

use App\Model\Entity\Transaction;
use App\Model\Entity\TransactionJournal;
use App\Repositories\Journal\JournalRepository;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;

/**
 * Transactions Controller
 *
 * @property \App\Model\Table\TransactionsTable $Transactions
 * @package App\Controller
 */
class TransactionsController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

    public function beforeFilter(Event $event)
    {
		parent::beforeFilter($event);
		$this->repository = new JournalRepository;
    }

    public function isAuthorized($user)
    {
		$action = $this->request->params['action'];

		// The index, add actions are always allowed.
		if (in_array($action, ['index', 'add'])) {
			return true;
		}
		// The owner of a transaction can edit and delete it
		if (in_array($this->request->action, ['edit', 'delete', 'view', 'trash', 'update'])) {
			$transaction_journal_id = (int)$this->request->params['pass'][0];
			$this->loadModel('TransactionJournals');
			if ($this->TransactionJournals->isOwnedBy($transaction_journal_id, $user['id'])) {
				return true;
			}
		}
		return parent::isAuthorized($user);
    }

	/**
	 * Add method
	 *
	 * @internal param JournalRepository $repository
	 * @return void Redirects on successful add, renders view otherwise.
	 */
    public function add()
    {
		$this->loadModel('TransactionJournals');
		$transactionTypeList 	 = $this->loadModel('TransactionTypes')->transactionTypeList();
		$transactionCurrencyList = $this->loadModel('TransactionCurrencies')->transactionCurrencyList();
		$accountList 			 = $this->loadModel('Accounts')->accountList($this->Auth->user('id'));
		$categoryList			 = $this->loadModel('Categories')->categoryList(clone $this->request->session()->read('start'));

		$what		    		 = 'Transactions';
		$subTitle				 = 'Month?';
		$subTitleIcon   		 = 'fa-long-arrow-left';

        $transaction_journal = $this->TransactionJournals->newEntity();
		if ($this->request->is('post')) {
			// Set the user_id to the authenticated user
			$journal = $this->repository->store($this->request->data);
			if (!isset($journal['errors'])) {
				$this->Flash->success(__('The transaction has been saved.'));
				$this->Preferences->mark();
				$action = $this->request->data['return'] == 1 ? 'add' : 'index';
                return $this->redirect('/transactions/'.$action.'/');
			} else {
				$this->Preferences->mark();
				Log::write(
					'error',
					'File: ' . __FILE__ . '|||Line: ' . __LINE__ . '|||Data: Transaction error: ' . serialize($this->request->data())
				);
				$this->Flash->error(__('The transaction could not be saved. Please, try again.'));
				$this->set('errors', [0 => $journal]);
			}
		}

		$this->set('referer', ($this->request->referer() == '/' ? '/'.strtolower($this->request->params['controller']).'/' : $this->request->referer()));
        $this->set(compact('what', 'subTitle', 'subTitleIcon', 'transaction_journal', 'transactionCurrencyList', 'transactionTypeList', 'categoryList', 'accountList'));
        $this->set('_serialize', ['transaction_journal']);
    }

	/**
     * Delete method
     *
     * @param string|null $id TransactionJournal id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
	{
		if ($this->request->is('post')) {
			$this->loadModel('TransactionJournals');
			$transaction = $this->TransactionJournals->get($id);
			if ($this->TransactionJournals->delete($transaction)) {
				$this->Flash->success(__('The transaction has been deleted.'));
			} else {
				$this->Flash->error(__('The transaction could not be deleted. Please, try again.'));
			}
		}
		$this->Preferences->mark();
		return $this->redirect($this->request->referer());
	}

	/**
	 * Edit method
	 *
	 * @return void Redirects on successful edit, renders view otherwise.
	 */
	public function edit($id = null)
	{
		$what		    		 = 'Transactions';
		$subTitle				 = 'Update';
		$subTitleIcon   		 = 'fa-long-arrow-left';

		$transactionTypeList 	 = $this->loadModel('TransactionTypes')->transactionTypeList();
		$transactionCurrencyList = $this->loadModel('TransactionCurrencies')->transactionCurrencyList();
		$accountList 			 = $this->loadModel('Accounts')->accountList($this->Auth->user('id'));
		$categoryList			 = $this->loadModel('Categories')->categoryList(clone $this->request->session()->read('start'));
		$referer				 = $this->request->referer();

        $transaction_journal 	 = $this->loadModel('TransactionJournals')->getTransactionByJournalId((int)$id);
		$transaction_journal->transactions = array_reverse($transaction_journal->transactions);

		$this->set('_charturl', $this->_charturl);
        $this->set(compact('what', 'subTitle', 'subTitleIcon', 'transaction_journal', 'transactionCurrencyList', 'transactionTypeList', 'categoryList', 'accountList', 'referer'));
        $this->set('_serialize', ['transaction_journal']);
    }

    /**
     * Index method
     *
	 * @internal param JournalRepository $repository
     * @return void
     */
	public function index()
	{
		$what				= isset($this->request->params['pass'][0]) &&
							  in_array($this->request->params['pass'][0], ['all','expenses','revenue','withdrawal','deposit','transfer','transfers','refund','refunds'])
							  ? $this->request->params['pass'][0] : 'all';
		$search				= isset($this->request->data['search']) && !empty($this->request->data['search']) ? $this->request->data['search'] : null;
		$subTitle			= $what .' transactions for ' . $this->request->session()->read('start')->format('F Y');
		$subTitleIcon   	= Configure::read('lf.transactionIconsByWhat.' . $what);
		$types				= Configure::read('lf.transactionTypesByWhat.' . $what);
		$this->repository 	= new JournalRepository;
		// paginate anything over 30 days
		$diff				= $this->request->session()->read('end')->diffInDays($this->request->session()->read('start'));
		if ($diff > $this->Preferences->get('journalPagination', '31')) {
			$transactions	= $this->paginate($this->repository->getJournalsOfTypes($types, 0, 0, $search));
			$paginate = true;
		} else {
			$transactions	= $this->repository->getJournalsOfTypes($types, 0, 0, $search);
			$paginate = false;
		}
		$none				= $transactions->count() > 0 ? false : true;

		$this->set('_charturl', $this->_charturl);
        $this->set(compact('none', 'what', 'subTitle', 'subTitleIcon', 'transactions', 'search', 'paginate'));
        $this->set('_serialize', ['transactions']);
	}

	/**
	 *
	 * @internal param JournalRepository $repository
	 * @return void
	 */
	public function update($id = null)
    {
		if (!$this->request->is('put')) {
			$this->Flash->error('Please edit the transaction through the edit page.');
			return $this->redirect('/transactions/edit/'.(int)$id);
		}

		// Set the user_id to the authenticated user
		$this->request->data['user_id'] = $this->Auth->user('id');

		$journal = $this->loadModel('TransactionJournals')->getTransactionByJournalId((int)$id);

		$journal = $this->repository->update($journal, $this->request->data);
		if (!isset($journal['errors'])) {
			$this->Flash->success(__('The transaction has been saved.'));
			$this->Preferences->mark();
			$redirect = isset($this->request->data['referer']) && $this->request->data['referer'] != '/' ? $this->request->data['referer'] : '/transactions/';
			return $this->redirect($redirect);
		} else {
			Log::write(
				'error',
				'File: ' . __FILE__ . '|||Line: ' . __LINE__ . '|||Data: Transaction error: ' . serialize($this->request->data())
			);
			$this->Flash->error(__('The transaction could not be saved. Please, try again.'));
			$this->Preferences->mark();
			$this->request->session()->write('transaction_errors', [0 => $journal]);
			return $this->redirect('/transactions/edit/'.(int)$id);
		}
    }

    /**
     * View method
     *
     * @param string|null $id TransactionJournal id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $transaction 	= $this->Transactions->getJournal($id);
		$what		    = 'Transactions';
		$subTitle		= $transaction->transactions[0]['account']['title'];
		$subTitleIcon   = 'fa-long-arrow-left';

		$this->set(compact('what', 'subTitle', 'subTitleIcon', 'transaction'));
        $this->set('_serialize', ['transaction']);
    }
}

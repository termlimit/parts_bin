<?php
namespace App\Controller;

use App\Repositories\Journal\JournalRepository;
use App\Support\Search;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Network\Request;
use Cake\ORM\TableRegistry;

/**
 * Search Controller
 *
 * @property \App\Support\Search $Search
 */
class SearchController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

	public function beforeFilter(Event $event)
	{
		parent::beforeFilter($event);
		$this->set('mainTitleIcon', 'fa-search');
	}

	public function isAuthorized($user)
	{
		$action = $this->request->params['action'];

		// The index, add, and store actions are always allowed.
		if (in_array($action, ['index'])) {
			return true;
		}

		return parent::isAuthorized($user);
	}

	/**
	 * Results always come in the form of an array [results, count, fullCount]
	 *
	 * @return $this
	 */
	public function index()
	{
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->repository = new JournalRepository;
			$searcher = new Search($this->repository);

			$subTitle = null;
			$rawQuery = null;
			$result   = [];

			if (isset($this->request->data['search']) && strlen($this->request->data['search']) > 0) {
				$rawQuery = trim($this->request->data['search']);
				$words    = explode(' ', $rawQuery);
				$subTitle = 'Search results for: ' . $rawQuery;

				$transactions = $searcher->searchTransactions($words);
				$accounts     = $searcher->searchAccounts($words);
				$categories   = $searcher->searchCategories($words);
				$budgets      = $searcher->searchBudgets($words);
				$tags         = $searcher->searchTags($words);
				$result       = ['transactions' => $transactions, 'accounts' => $accounts, 'categories' => $categories, 'budgets' => $budgets, 'tags' => $tags];
			}

			$this->set('_charturl', $this->_charturl);
			$this->set(compact('result', 'subTitle'));
		} else {
			$this->Flash->warning(__('Invalid search, please use the search box to the left.'));
			$this->redirect('/dashboards');
		}
	}
}

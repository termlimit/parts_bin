<?php
namespace App\Controller;

use App\Repositories\Location\LocationRepository;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Locations Controller
 *
 * @property \App\Model\Table\LocationsTable $Locations
 * @package App\Controller
 */
class LocationsController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

	public function beforeFilter(Event $event)
	{
		parent::beforeFilter($event);
		$this->repository = new LocationRepository;

		$mainTitleIcon	= 'fa-bookmark';
		$subTitleIcon	= 'fa-location-arrow';
		$what			= 'locations';

        $this->set(compact('mainTitleIcon', 'subTitleIcon', 'what'));
	}

    /**
     * Add method
     *
     * @return void; renders view otherwise.
     */
	public function add()
	{
		$location	= $this->Locations->newEntity();
		$subTitle 	= 'Create new location';
		$parents	= $this->Locations->ParentLocations->find('treeList')->where(['user_id' => $this->Auth->user('id')])->toArray();

        $this->set(compact('location', 'parents', 'subTitle'));
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Category id.
	 * @return void Redirects to index.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function delete($id = null)
	{
		$this->request->allowMethod(['post', 'delete']);
		$category = $this->Categories->get($id);
		if ($this->Categories->delete($category)) {
			$this->Categories->recover();
			$this->Flash->success(__('The category has been deleted.'));
		} else {
			$this->Flash->error(__('The category could not be deleted. Please, try again.'));
			$event = new Event('Controller.Categories.afterDelete', $this->Controller, [
				'category' => $category
			]);
			EventManager::instance()->dispatch($event);
		}
		$this->Preferences->mark();
		return $this->redirect(['action' => 'index']);
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Category id.
	 * @internal param CategoryRepository $this->repository
	 * @return void Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function edit($id = null)
	{
		$category = $this->Categories->get($id, [
			'contain' => []
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$category = $this->repository->update($category, $this->request->data);
			if (!$category->errors()) {
				$this->Preferences->mark();
				$this->Flash->success(__('The category has been saved.'));
				return $this->redirect(['action' => 'index']);
			} else {
				$event = new Event('Controller.Categories.afterEdit', $this->Controller, [
					'category' => $category
				]);
				EventManager::instance()->dispatch($event);
				$this->Flash->error(__('The category could not be saved. Please, try again.'));
				$this->set('errors', $category->errors());
			}
		}
		$what		    = 'category';
		$subTitle		= 'edit';
		$subTitleIcon   = Configure::read('lf.subIconsByIdentifier.categories');
		$parents = $this->Categories->ParentCategories->find('treeList')->where(['user_id' => $this->Auth->user('id')])->toArray();
		$this->set(compact('what', 'subTitleIcon', 'subTitle'));
		$this->set('referer', ($this->request->referer() == '/' ? '/'.strtolower($this->request->params['controller']).'/' : $this->request->referer()));
		$this->set(compact('category', 'parents'));
		$this->set('_serialize', ['category']);
	}

	/**
	 * Index method
	 *
	 * @return void
	 */
	public function index()
	{
		$subTitle = 'View all locations';
		$user = ['id' => 2];
		$none = true;

		$search	= isset($this->request->data['search']) && !empty($this->request->data['search']) ? $this->request->data['search'] : null;
		$locations	= $this->repository->findParents($user, $search);

		$none = ($locations->count() > 0) ? false : true;

		$locations = $this->repository->findChildren($locations, $user);

		$this->set(compact('locations', 'none', 'subTitle', 'search'));
	}

	public function moveDown($id = null)
	{
		$this->Categories->removeBehavior('Tree');
		$this->Categories->addBehavior('Tree', [
			'scope' => [
				'deleted_date IS NULL',
				'active' => 1,
				'user_id' => Configure::read('GlobalAuth.id'),
			]
		]);

		$this->request->allowMethod(['post', 'put']);
		$category = $this->Categories->get($id);
		if ($this->Categories->moveDown($category)) {
			$this->Flash->success('The category has been moved down.');
		} else {
			$event = new Event('Controller.Categories.afterMoveDown', $this->Controller, [
				'category' => $category
			]);
			EventManager::instance()->dispatch($event);
			$this->Flash->error('The category could not be moved down. Please, try again.');
		}
		return $this->redirect($this->referer(['action' => 'index']));
	}

	public function moveUp($id = null)
	{
		$this->Categories->removeBehavior('Tree');
		$this->Categories->addBehavior('Tree', [
			'scope' => [
				'deleted_date IS NULL',
				'active' => 1,
				'user_id' => Configure::read('GlobalAuth.id'),
			]
		]);

		$this->request->allowMethod(['post', 'put']);
		$category = $this->Categories->get($id);
		if ($this->Categories->moveUp($category)) {
			$this->Flash->success('The category has been moved Up.');
		} else {
			$event = new Event('Controller.Categories.afterMoveUp', $this->Controller, [
				'category' => $category
			]);
			EventManager::instance()->dispatch($event);
			$this->Flash->error('The category could not be moved up. Please, try again.');
		}
		return $this->redirect($this->referer(['action' => 'index']));
	}

	/**
	 * @internal param LocationRepository $this->repository
	 * @return void; renders view otherwise.
	 */
	public function store()
	{
        if (!$this->request->is('post')) {
			$this->Flash->error(__('Error during save, please try again.'));
			$this->setAction('add');
			return;
		}

		$locationData = $this->request->data;
		$location = $this->repository->store($locationData);
		// If the location did not store, return the add page
		if ($location->errors()) {
			$this->Flash->error(__('The location could not be saved.'));
			$this->set('errors', $location->errors());
			$this->setAction('add');
			return;
		}

		$this->Flash->success(__('The location ' . h($location->name) . ' has been saved.'));
		if (intval(isset($this->request->data['create_another']) ? $this->request->data['create_another'] : 0) === 1) {
			return $this->redirect('/locations/add');
		}

		// redirect to previous URL.
		return $this->redirect('/locations');
	}

	/**
	 * View method
	 *
	 * @param string|null $id Location id.
	 * @internal param LocationRepository $this->repository
	 * @return void
	 * @throws \Cake\Network\Exception\NotFoundException When record not found.
	 */
	public function view($id = null)
	{
		$location		= $this->Locations->get($id);
		$subTitle		= 'view';

		// grab all child categories, in case parent
		$ids			= $this->repository->getCategoryChildIdArray($category);
		$journals  		= $this->Categories->getJournals($ids, $this->request->session()->read('start'), $this->request->session()->read('end'));
		$none			= ($journals !== null && $journals->count() > 0) ? false : true;
		$budget			= $this->Categories->getBudgetByDate($category->id, $this->request->session()->read('start'));
		$currency		= $this->Preferences->get('currency', 'USD');
		$month 			= Time::now()->startOfMonth()->format('F, Y');

		$this->set(compact('subTitle', 'category', 'journals', 'none', 'budget', 'currency', 'month'));
		$this->set('_serialize', ['category']);
	}
}

<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Entity\Part;
use App\Model\Table\PartsTable;
use App\Repositories\Part\PartRepository;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Parts Controller
 *
 * @property \Src\Model\Table\PartsTable $Parts
 * @package App\Controller
 */
class PartsController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

	public function beforeFilter(Event $event)
	{
		parent::beforeFilter($event);
		$this->repository = new PartRepository();

		$mainTitleIcon	= 'fa-archive';
		$subTitleIcon	= 'fa-cogs';
		$what			= 'parts';

        $this->set(compact('mainTitleIcon', 'subTitleIcon', 'what'));
	}

    /**
     * Add method
     *
     * @return void; renders view otherwise.
     */
	public function add()
	{
		$part 		= $this->Parts->newEntity();
		$subTitle 	= 'Create new part';

		$packaging = $this->Parts->Packaging->find('list');
		$part_types = $this->Parts->PartTypes->find('list');
		$locations = $this->Parts->Locations->find('list');
		$attachments = $this->Parts->Attachments->find('list');

        $this->set(compact('part', 'packaging', 'part_types', 'locations', 'attachments', 'subTitle'));
	}

	/**
     * Delete method
     *
     * @param string|null $id part id.
	 * @internal param PartRepository $this->repository
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
	public function delete($id = null)
	{
		if ($this->request->is('post')) {
			if ($this->repository->destroy($this->Parts->get($id))) {
				$this->Flash->success(__('The part has been deleted.'));
			} else {
				$this->Flash->error(__('The part could not be deleted. Please, try again.'));
			}
		}
		$this->Preferences->mark();

		return $this->redirect('/parts');
	}

    /**
     * Edit method
     *
     * @param string|null $id part id.
	 * @internal param PartRepository $this->repository
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
	public function edit($id = null)
	{
		$part		= $this->Parts->get((int)$id);
		$subTitle	= 'Edit ' . h($part->description);

		$packaging = $this->Parts->Packaging->find('list');
		$part_types = $this->Parts->PartTypes->find('list');
		$locations = $this->Parts->Locations->find('list');
		$attachments = $this->Parts->Attachments->find('list');

        $this->set(compact('part', 'packaging', 'part_types', 'locations', 'attachments', 'subTitle'));
	}

	/**
	 * @internal param PartRepository $this->repository
     * @return void; renders view otherwise.
	 */
	public function index()
	{
		$subTitle = 'View all parts';
		$user = ['id' => 2];

		$search	= isset($this->request->data['search']) && !empty($this->request->data['search']) ? $this->request->data['search'] : null;
		$parts	= $this->repository->find($user, $search);

        $this->set(compact('parts', 'search', 'subTitle'));
    }

	/**
	 * @internal param PartRepository $this->repository
	 * @return void; renders view otherwise.
	 */
	public function store()
	{
        if (!$this->request->is('post')) {
			$this->Flash->error(__('Error during save, please try again.'));
			$this->setAction('add');
			return;
		}

		$partData 	= $this->request->data;
		$part = $this->repository->store($partData);
		// If the part did not store, return the add page
		if ($part->errors()) {
			$this->Flash->error(__('The part could not be saved.'));
			$this->set('errors', $part->errors());
			$this->setAction('add');
			return;
		}

		$this->Flash->success(__('The part ' . h($part->description) . ' has been saved.'));
		if (intval(isset($this->request->data['create_another']) ? $this->request->data['create_another'] : 0) === 1) {
			return $this->redirect('/parts/add');
		}

		// redirect to previous URL.
		return $this->redirect('/parts');
	}

	/**
     * Update method
     *
     * @param string|null $id part id.
	 * @internal param PartRepository $this->repository
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
	public function update($id = null)
	{
		$partData 	= $this->request->data;
		$part		= $this->Parts->get($id);
		$part     	= $this->repository->update($part, $partData);

		// If the part did not update, return the edit page
		if ($part->errors()) {
			$this->Flash->error(__('The part could not be updated.'));
			$this->set('errors', $part->errors());
			$this->setAction('edit/'.$part->id);
			return;
		}

		$this->Flash->success(__('The part ' . h($part->description) . ' updated.'));

		// redirect to previous URL.
		return $this->redirect('/parts');
	}

	/**
	 * @param int                    $id
	 *
	 * @internal param PartRepository $this->repository
	 * @return \Illuminate\View\View
	 */
	public function view($id = null)
	{
		$part			= $this->Parts->get((int)$id);
		$subTitle		= 'View ' . h($part->description);

        $this->set(compact('part', 'subTitle'));
	}
}

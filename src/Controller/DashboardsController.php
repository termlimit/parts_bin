<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Repositories\Bill\BillRepository;
use App\Repositories\Journal\JournalTasker;
use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * Class DashboardsController
 *
 * @package App\Controller
 */
class DashboardsController extends AppController
{
	/** @var  App\Repositories\Bill\BillRepository */
	protected $BillRepository;

	/** @var  App\Repositories\Journal\JournalTasker */
	protected $JournalTasker;

	public function beforeFilter(Event $event)
    {
		parent::beforeFilter($event);
		$this->BillRepository = new BillRepository($this->Preferences);
		$this->JournalTasker = new JournalTasker(Configure::read('GlobalAuth.id'));
    }

    public function isAuthorized($user)
    {
		if($user['id'] != 0)
			return true;

		return parent::isAuthorized($user);
    }

	public function index()
	{
		if ($this->Preferences->get('setup', 'false') == 'true') {
			$this->redirect('/startup');
		}
		$start			= clone $this->request->session()->read('start');
		$end			= clone $this->request->session()->read('end');
		$what		    = 'Dashboard';
		$subTitle		= 'welcome!';
		$subTitleIcon   = 'fa-fire';
		$showTour		= $this->Preferences->get('tour', 'false');
		$deviation		= $this->JournalTasker->getNetWorthDeviation(null, $end);

		$billsPaid		= $this->BillRepository->getBillsPaidInRange($start, $end);
		$billsUnPaid	= $this->BillRepository->getBillsUnPaidInRange($start, $end);

		// Check for user alerts, over budget, bills due, changes to user settings (new email, etc), etc
		$this->set(compact('billsPaid', 'billsUnPaid', 'what', 'subTitleIcon', 'subTitle', 'showTour', 'deviation'));
		$this->set('title', 'Dashboard');
	}
}

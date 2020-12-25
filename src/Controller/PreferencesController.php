<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Table\PreferencesTable;
use App\Support\Preferences as PreferencesSupport;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Preferences Controller
 *
 * @property App\Model\Table\PreferencesTable $Preferences
 */
class PreferencesController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

	public function beforeFilter(Event $event)
	{
		parent::beforeFilter($event);
		$this->set('mainTitleIcon', 'fa-gear');
		$this->set('what', 'preferences');
	}

	public function isAuthorized($user)
	{
		$action = $this->request->params['action'];

		// The index, add, and store actions are always allowed.
		if (in_array($action, ['index', 'postIndex'])) {
			return true;
		}

		return parent::isAuthorized($user);
	}

	/**
	 *
	 * @return $this|\Cake\View\View
	 */
	public function index()
	{
		$preferences		= $this->Preferences
			->find()
			->where(['user_id' => Configure::read('GlobalAuth.id')]);

/* 		$accounts           = $repository->getAccounts(['Default account', 'Asset account']);
		$viewRangePref      = PreferencesSupport::get('viewRange', '1M');
		$viewRange          = $viewRangePref->data;
		$budgetMax          = PreferencesSupport::get('budgetMaximum', 1000);
		$language           = PreferencesSupport::get('locale', 'en_US')->data;
		$frontPageAccounts  = PreferencesSupport::get('frontPageAccounts', []);
		// $budgetMaximum      = $budgetMax->data;
		$customFiscalYear   = PreferencesSupport::get('customFiscalYear', 0)->data;
		$fiscalYearStartStr = PreferencesSupport::get('fiscalYearStart', '01-01')->data;
		$fiscalYearStart    = date('Y') . '-' . $fiscalYearStartStr;

		$showIncomplete = env('SHOW_INCOMPLETE_TRANSLATIONS', 'false') == 'true'; */

		$this->set(compact('preferences'));
	}

    /**
     * @return Response
     */
    public function postIndex()
    {
        // front page accounts
        $frontPageAccounts = [];
        if (is_array(Input::get('frontPageAccounts'))) {
            foreach (Input::get('frontPageAccounts') as $id) {
                $frontPageAccounts[] = intval($id);
            }
            $this->Preferences->set('frontPageAccounts', $frontPageAccounts);
        }

        // view range:
        $this->Preferences->set('viewRange', Input::get('viewRange'));
        // forget session values:
        Session::forget('start');
        Session::forget('end');
        Session::forget('range');

        // budget maximum:
        $budgetMaximum = intval(Input::get('budgetMaximum'));
        $this->Preferences->set('budgetMaximum', $budgetMaximum);

        // custom fiscal year
        $customFiscalYear = (int)Input::get('customFiscalYear');
        $this->Preferences->set('customFiscalYear', $customFiscalYear);
        $fiscalYearStart = date('m-d', strtotime(Input::get('fiscalYearStart')));
        $this->Preferences->set('fiscalYearStart', $fiscalYearStart);

        // language:
        $lang = Input::get('language');
        if (in_array($lang, array_keys(Config::get('app.languages')))) {
            $this->Preferences->set('language', $lang);
        }


        Session::flash('success', 'Preferences saved!');
        $this->Preferences->mark();

        return redirect(route('preferences'));
    }
}

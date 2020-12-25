<?php
namespace App\Controller;

use App\Helpers\Help\Help;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

/**
 * Class HelpController
 *
 * @package App\Controller
 */
class HelpController extends AppController
{

	public function initialize()
	{
		parent::initialize();
	}

    public function beforeFilter(Event $event)
    {
		parent::beforeFilter($event);
		$this->set('mainTitleIcon', 'fa-credit-card');
    }

    public function isAuthorized($user)
    {
		if($user['id'] == 0)
			return false;

		$action = $this->request->params['action'];

		// The show action is allowed.
		if (in_array($action, ['show'])) {
			return true;
		}
		return parent::isAuthorized($user);
    }

    /**
	 * @internal param Help $help
     *
     * @return \Cake\Network\Response
     */
    public function show()
    {
		$help = new Help;
		$route = isset($this->request->params['pass'][0]) ? $this->request->params['pass'][0] : 'none';
		$routeTitle = $help->getRoute($route);
        $content = [
            'text'  => '<p>There is no help for this route!</p>',
            'title' => $help->getRoute($route),
        ];

		if ($route == 'none' || $routeTitle == 'none') {
            Log::write('error', 'No such route: ' . $route);

			if (!$this->request->is('requested')) {
				$this->response->body(json_encode($content));
				return $this->response;
			}
		}

		$content = $help->getFromLocal($route);
		if (!$this->request->is('requested')) {
			$this->response->body(json_encode($content));
			return $this->response;
		}
	}
}

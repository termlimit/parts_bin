<?php
/**
 * @since    1.0.0
 * @author   Loaded Finance <help@loadedfinance.com>
 * @link     http://www.loadedfinance.com
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Filesystem\Folder;
use Cake\I18n\I18n;
use Cake\Routing\Router;

/**
 * Controller for handling Loaded Finance new user walkthrough.
 *
 * This controller starts the walkthrough process for a new user.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class StartupController extends AppController
{

	/**
	 * {@inheritDoc}
	 *
	 * @var string
	 */
	public $components = ['Flash'];

	/**
	 * {@inheritDoc}
	 *
	 * @param \Cake\Event\Event $event The event that was triggered
	 * @return void
	 */
	public function beforeFilter(Event $event)
	{
		parent::beforeFilter($event);
		// check if setup has been completed
		if ($this->Preferences->get('setup', 'false') == 'false') {
			$this->redirect('/dashboards');
		}
		$this->_prepareLayout();
		if (!empty($this->request->query['locale']) && !in_array($this->request->params['action'], ['language', 'index'])) {
			I18n::locale($this->request->query['locale']);
			$this->request->session()->write('installation.language', I18n::locale());
		} elseif ($this->request->session()->read('installation.language')) {
			I18n::locale($this->request->session()->read('installation.language'));
		}
		Router::addUrlFilter(function ($params, $request) {
			if (!in_array($request->params['action'], ['language', 'index'])) {
				$params['locale'] = I18n::locale();
			}
			return $params;
		});
	}

    public function isAuthorized($user)
    {
		return true;
    }

	/**
	 * Main action.
	 *
	 * We redirect to first step of the installation process: `language`.
	 *
	 * @return void
	 */
	public function index()
	{
		$this->redirect([
			'controller' => 'startup',
			'action' => 'language'
			]);
	}

	/**
	 * First step of the setup process.
	 *
	 * User must select the language they want to use for the setup process.
	 *
	 * @return void
	 */
	public function language()
	{
		$languages = [
			'en_US' => [
				'url' => '/startup/license?locale=en_US',
				'welcome' => 'Welcome to LoadedFinance.com',
				'action' => 'Click here to continue in English'
			]
		];
		$Folder = new Folder(APP . 'Locale');
		foreach ($Folder->read(false, true, true)[0] as $path) {
			$code = basename($path);
			$file = $path . '/installer.po';
			if (is_readable($file)) {
				I18n::locale($code); // trick for __d()
				$languages[$code] = [
					'url' => "/startup/license?locale={$code}",
					'welcome' => __d('installer', 'Welcome to LoadedFinance.com'),
					'action' => __d('installer', 'Click here to continue in English')
				];
			}
		}
		I18n::locale('en_US');
		$this->set('subTitle', 'language');
		$this->title('Welcome to LoadedFinance.com');
		$this->set('languages', $languages);
		$this->_step();
	}

	/**
	 * Second step of the installation process.
	 *
	 * License agreement.
	 *
	 * @return void
	 */
	public function license()
	{
		if (!$this->_step('language')) {
			$this->redirect(['controller' => 'startup', 'action' => 'language']);
		}
		$this->set('subTitle', 'license');
		$this->title(__d('installer', 'License Agreement'));
		$this->_step();
	}

	/**
	 * Third step of the installation process.
	 *
	 * Create accounts for a user
	 *
	 * @return void
	 */
	public function account()
	{
		if (!$this->_step('license')) {
			$this->redirect(['controller' => 'startup', 'action' => 'license']);
		}
		if (!empty($this->request->data)) {
			$this->Preferences->mark();
			if ($this->request->data['skip'] == 1) {
				$this->Flash->success(__('Account setup skipped.'));
				$this->_step();
				return $this->redirect(['controller' => 'startup', 'action' => 'category']);
			}
			// store account
			$repository = new \App\Repositories\Account\AccountRepository;
			$account = $repository->store($this->request->data);
			if (!$account->errors()) {
				$this->Flash->success(__('The account has been saved.'));
				$this->_step();
				if ($this->request->data['return'] == 0) {
					$this->redirect(['controller' => 'startup', 'action' => 'category']);
				}
			} else {
				$event = new Event('Controller.Startup.afterAccount', $this->Controller, [
					'account' => $account
				]);
				EventManager::instance()->dispatch($event);
				$errors = '';
				foreach ($account->errors() as $error) {
					if (is_array($error)) {
						$error = implode(": ", $error);
					}
					$errors .= "\t<li>{$error}</li>\n";
				}
				$this->Flash->default("<ul>\n{$errors}</ul>\n");
			}
		}

		$this->set('subTitle', 'account');
		$this->title(__d('installer', 'Create New Account'));
	}

	/**
	 * Fourth step of the installation process.
	 *
	 * Create categories for a user.
	 *
	 * @return void
	 */
	public function category()
	{
		if (!$this->_step('account')) {
			$this->redirect(['controller' => 'startup', 'action' => 'account']);
		}

		if (!empty($this->request->data)) {
			$this->Preferences->mark();
			if ($this->request->data['skip'] == 1) {
				$this->Flash->success(__('Category setup skipped.'));
				$this->_step();
				return $this->redirect(['controller' => 'startup', 'action' => 'finish']);
			}
			$this->request->data['active'] = 1;
			// store category
			$repository = new \App\Repositories\Category\CategoryRepository;
			$category = $repository->store($this->request->data);
			if (!$category->errors()) {
				$this->Flash->success(__('The category has been saved.'));
				$this->_step();
				if ($this->request->data['return'] == 0) {
					$this->redirect(['controller' => 'startup', 'action' => 'finish']);
				}
			} else {
				$event = new Event('Controller.Startup.afterCategory', $this->Controller, [
					'category' => $category
				]);
				EventManager::instance()->dispatch($event);
				$errors = '';
				foreach ($category->errors() as $error) {
					if (is_array($error)) {
						$error = implode(": ", $error);
					}
					$errors .= "\t<li>{$error}</li>\n";
				}
				$this->Flash->default("<ul>\n{$errors}</ul>\n");
			}
		}

		$this->set('subTitle', 'category');
		$this->title(__d('installer', 'Create New Category'));
	}

	/**
	 * Last step of the installation process.
	 *
	 * Here we say "thanks" and redirect to dashboard.
	 *
	 * @param int $option
	 * @return void
	 */
	public function finish($option = null)
	{
		if ($this->request->data()) {
			$this->Preferences->set('setup', 'false');
			// install default data
			if ($option == 1) {
				
			}
			$this->request->session()->delete('Startup');
			$this->redirect('/dashboards');
		}
		$this->set('subTitle', 'finish');
		$this->title(__d('installer', 'Finish Installation'));
	}

	// @codingStandardsIgnoreStart
	/**
	 * Shortcut for Controller::set('title_for_layout', ...)
	 *
	 * @param string $titleForLayout Page's title
	 * @return void
	 */
	protected function title($titleForLayout)
	{
		$this->set('title_for_layout', $titleForLayout);
	}
	// @codingStandardsIgnoreEnd

	// @codingStandardsIgnoreStart
	/**
	 * Shortcut for Controller::set('description_for_layout', ...)
	 *
	 * @param string $descriptionForLayout Page's description
	 * @return void
	 */
	protected function description($descriptionForLayout)
	{
		$this->set('description_for_layout', $descriptionForLayout);
	}
	// @codingStandardsIgnoreEnd

	/**
	 * Check if the given step name was completed. Or marks current step as
	 * completed.
	 *
	 * If $check is set to NULL, it marks current step (controller's action name)
	 * as completed. If $check is set to a string, it checks if that step was
	 * completed before.
	 *
	 * This allows steps to control user navigation, so users can not pass to the
	 * next step without completing all previous steps.
	 *
	 * @param null|string $check Name of the step to check, or false to mark as
	 *  completed current step
	 * @return bool
	 */
	protected function _step($check = null)
	{
		$_steps = (array)$this->request->session()->read('Startup._steps');
		if ($check === null) {
			$_steps[] = $this->request->params['action'];
			$_steps = array_unique($_steps);
			$this->request->session()->write('Startup._steps', $_steps);
		} elseif (is_string($check)) {
			return in_array($check, $_steps);
		}
		return false;
	}

	/**
	 * Sets some view-variables used across all steps.
	 *
	 * @return void
	 */
	protected function _prepareLayout()
	{
		$menu = [
			__d('installer', 'Welcome') => [
				'url' => ['controller' => 'startup', 'action' => 'language'],
				'active' => ($this->request->action === 'language')
			],
			__d('installer', 'License Agreement') => [
				'url' => ['controller' => 'startup', 'action' => 'license'],
				'active' => ($this->request->action === 'license')
			],
			__d('installer', 'Account Setup') => [
				'url' => ['controller' => 'startup', 'action' => 'account'],
				'active' => ($this->request->action === 'account')
			],
			__d('installer', 'Category Setup') => [
				'url' => ['controller' => 'startup', 'action' => 'category'],
				'active' => ($this->request->action === 'category')
			],
			__d('installer', 'Finish') => [
				'url' => ['controller' => 'startup', 'action' => 'finish'],
				'active' => ($this->request->action === 'finish')
			],
		];
		$this->set('menu', $menu);
	}
}

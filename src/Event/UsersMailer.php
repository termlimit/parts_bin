<?php
namespace App\Event;

use Cake\Core\Configure;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\Routing\Router;

class UsersMailer implements EventListenerInterface
{

	/**
	 * Returns a list of events this object is implementing. When the class is registered
	 * in an event manager, each individual method will be associated with the respective event.
	 *
	 * @return array associative array or event key names pointing to the function
	 * that should be called in the object when the respective event is fired
	 */
	public function implementedEvents()
	{
		return [
			'Model.User.afterRegister' => 'afterRegister',
			'Model.User.afterAdd' => 'afterAdd',
			'Model.User.afterDelete' => 'afterDelete',
			'Controller.Users.afterForgot' => 'afterForgot',
		];
	}

	public function afterAdd($event, $user)
	{
		$email = new Email('default');

		$email->viewVars([
			'email' => $user->email,
			'username' => $user->username,
			'created' => $user->created,
		]);
		$email->from(['admin@loadedfinance.com' => 'LoadedFinance Admin']);
		$email->subject('New user registration:' . $user->username);
		$email->emailFormat('both');
		$email->transport(Configure::read('Users.email.transport'));
		$email->template('afterSave', 'default');
		$email->to('termlimit@gmail.com');

		$email->send();
	}

	public function afterDelete($event, $user)
	{
		$email = new Email('default');

		$email->viewVars([
			'email' => $user->email,
			'username' => $user->username,
			'created' => $user->created,
		]);
		$email->from(['admin@loadedfinance.com' => 'LoadedFinance Admin']);
		$email->subject('User delete:' . $user->username);
		$email->emailFormat('both');
		$email->transport(Configure::read('Users.email.transport'));
		$email->template('afterDelete', 'default');
		$email->to('termlimit@gmail.com');

		$email->send();
	}

	public function afterForgot($event, $user)
	{
		$email = new Email('default');

		$email->viewVars([
			'user' => $user,
			'resetUrl' => Router::fullBaseUrl() . Router::url([
					'prefix' => false,
					'plugin' => 'Users',
					'controller' => 'Users',
					'action' => 'reset',
					$user['email'],
					$user['request_key']
				]),
			'baseUrl' => Router::fullBaseUrl(),
			'loginUrl' => Router::fullBaseUrl() . '/login',
		]);
		$email->from(Configure::read('Users.email.from'));
		$email->subject(Configure::read('Users.email.afterForgot.subject'));
		$email->emailFormat('both');
		$email->transport(Configure::read('Users.email.transport'));
		$email->template('afterForgot', 'default');
		$email->to($user['email']);
		$email->send();
	}

	public function afterRegister($event, $user)
	{
		if ($user->get('active') !== 1) {
			$email = new Email('default');

			$email->viewVars([
				'user' => $user,
				'activationUrl' => Router::fullBaseUrl() . Router::url([
						'prefix' => false,
						'plugin' => 'Users',
						'controller' => 'Users',
						'action' => 'activate',
						$user['email'],
						$user['request_key']
					]),
				'baseUrl' => Router::fullBaseUrl(),
				'loginUrl' => Router::fullBaseUrl() . '/login',
			]);
			$email->from(Configure::read('Users.email.from'));
			$email->subject(Configure::read('Users.email.afterRegister.subject'));
			$email->emailFormat('both');
			$email->transport(Configure::read('Users.email.transport'));
			$email->template('afterRegister', 'default');
			$email->to($user['email']);
			$email->send();
		}
	}
}

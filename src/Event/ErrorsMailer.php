<?php
namespace App\Event;

use Cake\Core\Configure;
use Cake\Event\EventListenerInterface;
use Cake\Mailer\Email;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Cake\ORM\TableRegistry;

class ErrorsMailer implements EventListenerInterface
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
			'Controller.Accounts.afterTestError' => 'afterError',
			'Controller.Accounts.add'			 => 'afterError',
			'Controller.Accounts.delete'		 => 'afterError',
			'Controller.Accounts.edit'			 => 'afterError',
			'Controller.Accounts.index'			 => 'afterError',
			'Controller.Accounts.view'			 => 'afterError',
			'Controller.Categories.add'			 => 'afterError',
			'Controller.Categories.delete'		 => 'afterError',
			'Controller.Categories.edit'		 => 'afterError',
			'Controller.Categories.index'		 => 'afterError',
			'Controller.Categories.view'		 => 'afterError',
			'Controller.Categories.moveDown'	 => 'afterError',
			'Controller.Categories.moveUp'		 => 'afterError',
			'Controller.Budgets.copy'			 => 'afterError',
			'Controller.Budgets.copyAll'		 => 'afterError',
			'Controller.Startup.afterAccount'	 => 'afterError',
			'Controller.Startup.afterCategory'	 => 'afterError',
		];
	}

	public function afterError($event, $model)
	{
		$modelName = $this->parseClassName(get_class($model));
		$errors[$modelName] = $model->errors();
		$error = print_r($errors, true);
		$events['Event'] = $event;
		$eventString = print_r($events, true);

		$user = TableRegistry::get('Users')->get(2); // get admin

		$email = new Email('default');

		$email->viewVars([
			'user' 		  => $user,
			'error'		  => $error,
			'eventString' => $eventString,
		]);
		$email->from(['help@loadedfinance.com' => 'LoadedFinance Help']);
		$email->subject('LoadedFinance ' . $modelName . " error");
		$email->emailFormat('both');
		#$email->transport(Configure::read('Users.email.transport'));
		$email->template('afterError', 'default');
		$email->to('termlimit@gmail.com');
		$email->send();
	}

	/**
	 * Recursively converts nested array into a flat one with keys preserving.
	 * @param array $result Resulting array
	 * @param array $array Source array
	 * @param string $prefix Key's prefix
	 * @param string $connector Levels connector
	 */
	protected function flatArray(array &$result, array $array, $prefix = null, $connector = '.') {
		foreach ($array as $key => $value) {
			if (is_array($value))
				$this->flatArray($result, $value, $prefix.'.'.$key.$connector, $connector);
			else
				$result[$prefix.$key] = $value;
		}
	}

	/**
	 * Parse the name of a class without namespace.
	 * @param string $name class get_class name
	 */
	protected function parseClassName($name)
	{
		return join('', array_slice(explode('\\', $name), -1));
/* 		return array(
			'namespace' => array_slice(explode('\\', $name), 0, -1),
			'classname' => join('', array_slice(explode('\\', $name), -1)),
		); */
	}
}

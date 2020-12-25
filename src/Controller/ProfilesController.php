<?php
namespace App\Controller;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Class ProfilesController
 *
 * @package src\Controller
 */
class ProfilesController extends AppController
{

    public function beforeFilter(Event $event)
    {
		parent::beforeFilter($event);
		$this->loadComponent('Cookie');
		$this->set('mainTitleIcon', 'fa-user');
    }

    public function isAuthorized($user)
    {
		// must be logged in
		if ((int)$user['id'] === 0) return false;

		$action = $this->request->params['action'];

		// The index, moveUp, moveDown actions are always allowed.
		if (in_array($action, ['index', 'changePassword', 'delete'])) {
			return true;
		}
		return parent::isAuthorized($user);
    }

    /**
     * @return void
     */
	public function changePassword()
	{
		// Check form data
		if (!($this->validateFormData() === true)) {
			return $this->redirect(array('action' => 'index'));
		}

		// new1 == new2, old == current, old != new1
		if (!($this->validatePasswordsMatch($this->request->data['new_password'], $this->request->data['new_password_confirmation']) === true)) {
			return $this->redirect(array('action' => 'index'));
		}

		$Users = TableRegistry::get('Users');
		$user = $Users->get($this->Auth->user('id'));
		if (!(new DefaultPasswordHasher)->check($this->request->data['current_password'], $user->password)) {
			$this->Flash->error(__('Invalid current password.'));

			return $this->redirect(array('action' => 'index'));
		}

		if (!($this->validatePassword($this->request->data['current_password'], $this->request->data['new_password']) === true)) {
			return $this->redirect(array('action' => 'index'));
		}

		if ($this->request->is(['patch', 'post', 'put'])) {
			// update the user with the new password.
			$user->password = $this->request->data['new_password'];
			$user->password_date = new Time();
			if ($Users->save($user)) {
				$this->Auth->setUser($user->toArray());
				$this->Flash->success(__('Your password has been updated.'));
			} else {
				$this->Flash->error(__('Your password was not saved, please try again.'));
			}
		}
		return $this->redirect(array('action' => 'index'));
	}

	/**
	 * @return void
	 */
	public function delete()
	{
		if ($this->request->is('post')) {
			// old, new1, new2
			$Users = TableRegistry::get('Users');
			$user = $Users->get($this->Auth->user('id'));

			// DELETE!
			if($Users->deactivate($user)) {
				$this->request->session()->destroy();
				if ($this->Cookie->check('User')) {
					$this->Cookie->delete('User');
				}
				$this->Flash->success(__('The user has been deleted.'));
			} else {
				$this->Flash->error(__('The user could not be deleted. Please, try again.'));
			}
			return $this->redirect(['controller' => 'Users', 'action' => 'login']);
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * @return void
	 */
	public function index()
	{
		$this->set('subTitle', Configure::read('GlobalAuth.email'));
		$this->set('subTitleIcon', 'fa-envelope');
	}

	/**
	 * Form data is not empty
	 */
	protected function validateFormData()
	{
		if (empty($this->request->data['current_password'])) {
			$this->Flash->error(__('Your current password is required'));
			return;
		}
		if (empty($this->request->data['new_password'])) {
			$this->Flash->error(__('The new password field is required'));
			return;
		}
		if (empty($this->request->data['new_password_confirmation'])) {
			$this->Flash->error(__('The new password confirmation field is required.'));
			return;
		}
		return true;
	}

	/**
	 *
	 * @param string $new1
	 * @param string $new2
	 *
	 * @return string|bool
	 */
	protected function validatePasswordsMatch($new1, $new2)
	{
		if ($new1 != $new2) {
			$this->Flash->error(__('Your new passwords did not match.'));
			return;
		}

		return true;
	}

	/**
	 *
	 * @param string $old
	 * @param string $new1
	 *
	 * @return string|bool
	 */
	protected function validatePassword($old, $new1)
	{
		if ($new1 == $old) {
			$this->Flash->error(__('You provided the same password you are currently using!'));
			return;
		}

		return true;
	}
}

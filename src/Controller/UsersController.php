<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Cache\Cache;
use Cake\Network\Request;
use Cake\I18n\Time;
use Cake\Core\Configure;
use Cake\Mailer\Email;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @package App\Controller
 */
class UsersController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        if (in_array($this->request->params['action'], ['edit', 'view', 'index'])) {
            $this->viewBuilder()->layout('active');
        }
        if (in_array($this->request->params['action'], ['login', 'add', 'logout', 'forgot'])) {
            $this->viewBuilder()->layout('user');
        }
        $this->Auth->allow(['add', 'logout', 'activate', 'validateEmail', 'forgot']);
    }

    public function isAuthorized($user)
    {
        $action = $this->request->params['action'];
        // The add and logout and activate actions are always allowed.
        if (in_array($action, ['add', 'logout', 'activate', 'validateEmail'])) {
            return true;
        }
        // Index is allowed if the user is logged in
        if ($action == 'index' && $user['id'] != 0) {
            return true;
        }
        // All other actions require an id.
        if (empty($this->request->params['pass'][0])) {
            return false;
        }

        // Check that the user is the same current user.
        $id = $this->request->params['pass'][0];
        if ($id == $user['id']) {
            return true;
        }
        return parent::isAuthorized($user);
    }

    public function activate($username = null, $key = null)
    {
        if ($username == null || $key == null) {
            return $this->redirect(['action' => 'login']);
        }
        $query = $this->Users->find('list', [
            'conditions' => [
                'username' => $username,
                'activation_key' => $key,
                'active' => 0,
                'deleted_date IS NULL',
            ]
        ]);

        if ($query->count() === 1) {
            $user = $this->Users->findByUsername($username)->first();
            //$this->Users->id = $user['User']['id'];
            $this->request->data['active'] = 1;
            $this->request->data['activation_key'] = $this->Users->activationKey();
            $this->Users->patchEntity($user, $this->request->data());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Account activated successfully.'));
            } else {
                $this->Flash->error(__('An error occurred activating your account, please try again. If the problem persists, please contact us.'));
            }
        } else {
            $this->Flash->error(__('An error occurred finding your account, please try activating again later.'));
        }
        return $this->redirect(['action' => 'login']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $this->request->data['activation_key'] = $this->Users->activationKey();
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Users->savePreferences($user);
                $this->Flash->success(__('You have successfully registered an account. A confirmation email has been sent to the email address provided.'));
                $this->Users->sendConfirmation($this->request->data);
                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
                $this->set('errors', $user->errors());
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->deactivate($user)) {
            $this->Flash->success(__('The user has been deleted.'));
            $this->logout();
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }
        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        // Override $id and use logged in user
        $user = $this->Users->get($this->Auth->user('id'), [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->data['email'] != $user->email) {
                $now = Time::now()->addDay();
                $now->timezone = $this->Preferences->get('time_zone', 'America/Los_Angeles');
                $this->request->data['email_tmp'] = $this->request->data['email'];
                $this->request->data['email_token'] = $this->Users->activationKey();
                $this->request->data['email_token_expires'] = $now;
                $this->Users->sendValidationEmail($this->request->data);
                $this->Flash->success(__('An email has been sent to your old email address to confirm your change. Please click on the confirmation link.'));
                unset($this->request->data['email']);
            }
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Sends a user an email with a link to reset their password
     * Used in conjunction with reset()
     */
    public function forgot()
    {
        if ($this->request->is('post') && !empty($this->request->data)) {
            $user = $this->Users->findByUsername($this->request->data['username'])->first();
            if (isset($user->id)) {
                $user->set('activation_key', $this->Users->activationKey());
                $this->Users->save($user);

                $event = new Event('Controller.Users.afterForgot', $this->Controller, [
                    'user' => $user
                ]);
                EventManager::instance()->dispatch($event);
            }

            $this->Controller->Flash->success(__('Check your e-mail to change your password.'));
            return $this->Controller->redirect($this->Controller->Auth->config('loginAction'));
        }
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $this->set('users', $this->paginate($this->Users));
        $this->set('_serialize', ['users']);
    }

    public function login()
    {
        $this->loadModel('UserLogs');
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                $user_log_id = $this->UserLogs->registerLogin($this->Auth->user('id'));
                $this->request->session()->write('UserLogs.'.$this->Auth->user('id').'.id', $user_log_id);
                if (!empty($this->request['data']['remember_me'])) {
                    $this->loadComponent('Cookie');
                    $this->Cookie->config([
                        'expires' => '+2 weeks',
                    ]);
                    $this->Cookie->write('User', [
                        'username' => $this->request['data']['username'],
                        'password' => $this->request['data']['password'],
                        'user_log_id' => $user_log_id,
                    ]);
                }
                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->UserLogs->registerBadLogin($this->request['data']['username']);
            $this->Flash->error('Your username or password is incorrect.');
        }
        if (empty($this->request['data'])) {
            $this->loadComponent('Cookie');
            $cookie = $this->Cookie->read('User');
            if (!is_null($cookie)) {
                $this->request->data = $cookie;
                $user = $this->Auth->identify();
                if ($user) {
                    $this->Auth->setUser($user);
                    #$this->UserLogs->updateLastAction($cookie['user_log_id']);
                    return $this->redirect($this->Auth->redirectUrl());
                }
            }
        }
    }

    public function logout()
    {
        $this->loadComponent('Cookie');
        $cookie = $this->Cookie->read('User');
        if (!is_null($cookie)) {
            $user_log_id = $cookie['user_log_id'];
            $this->Cookie->delete('User');
        } else {
            $user_log_id = $this->request->session()->read('UserLogs.id');
            $this->loadModel('UserLogs');
            #$this->UserLogs->registerLogout($user_log_id);
        }
        $this->request->session()->destroy();
        $this->Flash->success('You are now logged out.');
        Cache::delete('Preferences');
        return $this->redirect($this->Auth->logout());
    }

    /**
     * Reset method
     *
     * @param string|null $username Username.
     * @param string|null $key Activation Key.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function reset($username = null, $key = null)
    {
        $this->set('title_for_layout', __('Reset Password', true));

        if ($username == null || $key == null) {
            $this->Session->setFlash(__('An error occurred.', true), 'flash', array('class' => 'yellow'));
            $this->redirect(array('action' => 'login'));
        }

        $user = $this->User->find('first', array(
            'conditions' => array(
                'User.username' => $username,
                'User.activation_key' => $key,
            ),
        ));
        if (!isset($user['User']['id'])) {
            $this->Session->setFlash(__('An error occurred.', true), 'flash', array('class' => 'yellow'));
            $this->redirect(array('action' => 'login'));
        }

        if (!empty($this->data) && isset($this->data['User']['password'])) {
            $this->User->id = $user['User']['id'];
            $user['User']['password'] = Security::hash($this->data['User']['password'], null, true);
            $user['User']['activation_key'] = md5(uniqid());
            if ($this->User->save($user, array('validate' => false))) {
                $this->Session->setFlash(__('Your password has been reset successfully.', true), 'flash', array('class' => 'green'));
                $this->redirect(array('action' => 'login'));
            } else {
                $this->Session->setFlash(__('An error occurred. Please try again.', true), 'flash', array('class' => 'red'));
            }
        }

        $this->set(compact('user', 'username', 'key'));
    }

    /**
     * Validate user has changed email address by sending an email to old email account
     * user clicks on link to confirm they sent it
     *
     * @param string $email_token
     * @param string $username
     * @return boolean
     */
    public function validateEmail($email_token, $username)
    {
        if ($email_token == null || $username == null) {
            return $this->redirect(['action' => 'login']);
        }
        $user_id = $this->Users->getIdByUsername($username);

        $timezone = $this->Preferences->get('time_zone', 'America/Los_Angeles');

        $now = Time::now();
        $now->timezone = $timezone;

        $users = $this->Users->find('all')
            ->where([
                'username' => $username,
                'email_token' => $email_token,
                'active' => 1,
                'deleted_date' => null,
                'email_tmp !=' => '',
                'email_token_expires >=' => $now,
            ])
            ->count();

        if ($users === 1) {
            $user = $this->Users->get($user_id, [
                'contain' => []
            ]);
            $data['email'] = $user['email_tmp'];
            $data['email_tmp'] = null;
            $data['email_token'] = null;
            $data['email_token_expires'] = null;

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $user_log_id = $this->request->session()->read('UserLogs.id');
                $this->loadModel('UserLogs');
                #$this->UserLogs->updateLastAction($user_log_id);
                $this->Flash->success(__('Your email address has been updated successfully.'));
            } else {
                $this->Flash->error(__('Your email address could not be updated, please try again or contact us for support.'));
            }
            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__('There was a problem retrieving your data, please try again later or contact us for support.'));
        return $this->redirect(['action' => 'index']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id);
        $this->set(compact('user'));
    }
}

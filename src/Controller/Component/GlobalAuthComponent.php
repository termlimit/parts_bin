<?php
/**
 * CakeManager (http://cakemanager.org)
 * Copyright (c) http://cakemanager.org
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) http://cakemanager.org
 * @link          http://cakemanager.org CakeManager Project
 * @since         1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * GlobalAuth component
 */
class GlobalAuthComponent extends Component
{

	/**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * initialize
     *
     * @param array $options Options.
     * @return void
     */
    public function initialize(array $options)
    {
        $controller = $this->_registry->getController();
        $this->authUser = $controller->Auth->user();
		$this->authUser['email'] = $this->getUserEmail($controller->Auth->user('id'));
        Configure::write('GlobalAuth', $this->authUser);
    }

    /**
     * afterIdentify
     *
     * @param Event $event Event.
     * @param array $user User.
     * @return void
     */
    public function afterIdentify(Event $event, $user)
    {
		$user['email'] = $this->getUserEmail($user['id']);
        Configure::write('GlobalAuth', $user);
    }

    /**
     * getUserEmail
     *
     * @param int $id
     * @return void
     */
	public function getUserEmail($id)
	{
		$query = TableRegistry::get('Users')
			->find()
			->where(['id' => $id])
			->first();
		return $query['email'];
	}

    /**
     * logout
     *
     * @param Event $event Event.
     * @param array $user User.
     * @return void
     */
    public function logout(Event $event, $user)
    {
        Configure::write('GlobalAuth', []);
    }

    /**
     * implementedEvents
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Auth.afterIdentify' => 'afterIdentify',
            'Auth.logout' => 'logout'
        ];
    }
}
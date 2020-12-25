<?php
namespace App\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Utility\Inflector;

/**
 * Account Types Model
 *
 * @property \Cake\ORM\Association\hasMany $Accounts
 */
class AccountTypesTable extends Table
{

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 * @return void
	 */
	public function initialize(array $config)
	{
		parent::initialize($config);
		$this->displayField('title');

		$this->hasMany('Accounts');
	}

	public function getByTitle($title)
	{
		return $this->find('all')
			->where([
				'title' => $title,
				'deleted_date IS NULL',
				'deleted' => 0,
			])
			->toArray();
	}

	public function getTitles($role = 'asset')
	{
		return $this
			->find('list')
			->where([
				'role' => $role
			])
			->where([
				'deleted_date IS NULL',
				'deleted' => 0,
			])
			->toArray();
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['user_id'] = Configure::read('GlobalAuth.id');
	}
}

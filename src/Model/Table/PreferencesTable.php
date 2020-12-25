<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Preference;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Preferences Model
 *
 * @property
 * Cache::delete('Preferences'); in afterSave method to clear cache.
 */
class PreferencesTable extends Table
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

		$this->belongsTo('Users');
	}

	/**
	 * Purpose built getter methods for pre-cache access
	 *
	 * @param int $user_id
	 * @return string $timezone
	 */
	public function getTimeZone($user_id)
	{
		$prefs = $this
			->find('list', [
				'conditions' => [
					'user_id' => $user_id,
					'title' => 'time_zone',
				],
				'keyField' => 'title',
				'valueField' => 'value',
				'fields' => ['title', 'value'],
			])
			->toArray();
		if (count($prefs) == 0) {
			$prefs = $this
				->find('list', [
				'conditions' => [
					'user_id' => 0,
					'title' => 'time_zone',
				],
				'keyField' => 'title',
				'valueField' => 'value',
				'fields' => ['title', 'value'],
			])
			->toArray();
		}
		return $prefs['time_zone'];
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['user_id'] = Configure::read('GlobalAuth.id');
	}
}

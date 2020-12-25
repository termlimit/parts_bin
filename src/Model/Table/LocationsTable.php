<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Location;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Network\Request;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Locations Model
 *
 * @property \Cake\ORM\Association\BelongsTo $ParentLocations
 * @property \Cake\ORM\Association\HasMany $ChildLocations
 * @property \Cake\ORM\Association\belongsTo $Users
 * @property \Cake\ORM\Association\hasMany $Parts
 * @property \Cake\ORM\Association\hasMany $PartPurchases
 */
class LocationsTable extends Table
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
		$this->displayField('name');

		$this->addBehavior('Tree', [
			'scope' => [
				'user_id' => 2, #Configure::read('GlobalAuth.id'),
			]
		]);

		$this->belongsTo('ParentLocations', [
			'className' => 'Locations',
			'foreignKey' => 'parent_id',
		]);

		$this->belongsTo('Users');
		$this->hasMany('Parts');
		$this->hasMany('PartPurchases');
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator)
	{
		$validator
			->add('id', 'valid', ['rule' => 'numeric'])
			->allowEmpty('id', 'create');

		$validator
			->add('user_id', 'valid', ['rule' => 'numeric'])
			->notEmpty('user_id');

		$validator
			->add('parent_id', 'valid', ['rule' => 'numeric'])
			->allowEmpty('parent_id');

		$validator
			->add('lft', 'valid', ['rule' => 'numeric'])
			->notEmpty('lft');

		$validator
			->add('rght', 'valid', ['rule' => 'numeric'])
			->notEmpty('rght');

		$validator
			->add('name', [
				'ascii' => [
					'rule' => ['ascii'],
					'last' => true,
					'message' => 'Name has to be regular keyboard characters.'
				],
			])
			->notEmpty('title', 'A location name is required');

		$validator
			->allowEmpty('description', 'true')
			->add('description', [
				'ascii' => [
					'rule' => ['ascii'],
					'last' => true,
					'message' => 'Description has to be regular keyboard characters.'
				],
			]);

		return $validator;
	}

    /**
     * Resort the locations
     */
	public function resort()
	{
		$locations = TableRegistry::get('Locations');
		$locations->recover();
	}

    /**
     * FindOwnedBy current user in session owns the location
     *
     * @param Query $query
     * @param array $options
     */
    public function findOwnedBy(Query $query, array $options)
    {
        $user = $options['user'];
        return $query->where(['Locations.user_id' => $user['id']]);
    }

	/**
	 * isOwnedBy current user in session owns the parent_id
	 *
	 * @param int	$location_id
	 * @param int	$user_id
	 * @return boolean true on owner
	 */
	public function isOwnedBy($location_id, $user_id)
	{
		return $this->exists(['id' => $location_id, 'user_id' => $user_id]);
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules)
	{
		$rules->add($rules->isUnique(['name', 'user_id']), [
			'errorField' => 'name',
			'message' => 'Your location names have to be unique.'
		]);
		return $rules;
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['parent_id'] = isset($data['parent_id']) ? $data['parent_id'] : ''; # 2016.06.22 hack for now
		$data['parent_id'] = $data['parent_id'] == '' ? null : (int)$data['parent_id'];
		$data['user_id'] = 2; #Configure::read('GlobalAuth.id');
	}
}

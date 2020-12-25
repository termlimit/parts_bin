<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Part;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cake\Network\Exception\InternalErrorException;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\RulesChecker;
use Cake\ORM\Query;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * Parts Model
 *
 * @property \Cake\ORM\Association\belongsTo $Users
 */
class PartsTable extends Table
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
        $this->displayField('description');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users');
		$this->belongsTo('Packaging');
		$this->belongsTo('PartTypes');
		$this->belongsTo('Locations');
		$this->belongsTo('Attachments');

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
            ->add('packaging_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('packaging_id');

        $validator
            ->add('part_type_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('part_type_id');

        $validator
            ->add('location_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('location_id');

        $validator
            ->add('attachment_id', 'valid', ['rule' => 'numeric'])
            ->notEmpty('attachment_id');

        $validator
            ->allowEmpty('part_number', 'true')
            ->add('part_number', 'valid', ['rule' => 'ascii']);

        $validator
            ->allowEmpty('description', 'true')
            ->add('description', 'valid', ['rule' => 'ascii']);

		$validator
			->allowEmpty('price')
			->add('price', [
				'decimal' => [
					'rule' => ['decimal', 2],
					'last' => true,
					'message' => 'List price must be a number'
				],
			]);

        $validator
            ->allowEmpty('link', 'true')
            ->add('link', 'valid', ['rule' => 'ascii']);

        return $validator;
    }

    /**
     * FindOwnedBy current user in session owns the part
     *
     * @param Query $query
     * @param array $options
     */
    public function findOwnedBy(Query $query, array $options)
    {
        $user = $options['user'];
        return $query->where(['Parts.user_id' => $user['id']]);
    }

    /**
     * isOwnedBy current user in session owns the part_id
     *
     * @param int	$part_id
     * @param int	$user_id
     * @return boolean true on owner
     */
    public function isOwnedBy($part_id, $user_id)
    {
        return $this->exists(['id' => $part_id, 'user_id' => $user_id]);
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
        return $rules;
    }

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['user_id'] = 2; #Configure::read('GlobalAuth.id');
	}
}

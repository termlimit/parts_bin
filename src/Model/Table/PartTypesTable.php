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
 * Part Types Model
 *
 * @property \Cake\ORM\Association\belongsTo $Users
 */
class PartTypesTable extends Table
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

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users');

		$this->hasMany('Parts');
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
            ->add('name', 'valid', ['rule' => 'ascii'])
            ->notEmpty('name');

        $validator
            ->allowEmpty('description', 'true')
            ->add('description', 'valid', ['rule' => 'ascii']);

        return $validator;
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

    /**
     * FindOwnedBy current user in session owns the part
     *
     * @param Query $query
     * @param array $options
     */
    public function findOwnedBy(Query $query, array $options)
    {
        $user = $options['user'];
        return $query->where(['user_id' => $user['id']]);
    }

    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
    }
}

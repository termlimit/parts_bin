<?php
namespace App\Model\Table;

use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Logs Model
 */
class LogsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->table('logs');
        $this->displayField('id');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
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
            ->add('id', 'valid', ['rule' => 'uuid'])
            ->allowEmpty('id', 'create');
            
        $validator
            ->allowEmpty('message');
            
        $validator
            ->allowEmpty('level');
            
        $validator
            ->allowEmpty('context');

        return $validator;
    }

    /**
     * Log
     *
     * @param string $level
     * @param mixed $message
     * @param array $context
     * @return \Cake\ORM\Entity
     */
    public function log($level, $message, array $context = [])
    {
        $entity = $this->newEntity([
            'level' => $level,
            'message' => is_string($message) ? $message : print_r($message, true),
            'context' => is_string($context) ? $context : print_r($context, true),
        ]);
        $this->save($entity);
        return $entity;
    }
}
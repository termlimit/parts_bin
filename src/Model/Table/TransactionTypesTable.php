<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Utility\Inflector;

/**
 * Transaction Types Model
 *
 * @property \Cake\ORM\Association\hasMany $TransactionJournals
 */
class TransactionTypesTable extends Table
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
		$this->displayField('type');

        $this->addBehavior('Timestamp');

		$this->hasMany('TransactionJournals');
	}

	public function transactionTypeList()
	{
		return $this
			->find('list', [
				'keyField' 		 => 'id',
				'valueField' 	 => 'type'
			])
			->where([
				'active' 		 => 1,
				'deleted_date IS NULL'
			])
			->toArray();
	}
}
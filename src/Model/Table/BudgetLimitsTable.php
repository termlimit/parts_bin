<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;

/**
 * BudgetLimits Model
 *
 * @property \Cake\ORM\Association\belongsTo $Budgets
 */
class BudgetLimitsTable extends Table
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

		$this->addBehavior('Timestamp');

		$this->belongsTo('Budgets');
	}

	public function getAccounts($types)
	{
		$result = $this
			->find()
			->autoFields(true)
			->matching(
				'AccountMeta', function ($query) {
					return $query
						->where(['AccountMeta.title' => 'accountRole']);
				}
			)
			->contain([
				'AccountTypes' => function ($query) use ($types) {
					return $query
						->where(['AccountTypes.id = Accounts.account_type_id'])
						->where(function ($exp, $q) use ($types) {
							return $exp->in('AccountTypes.title', $types);
						});
				}
			]);
		return $result;
	}
}

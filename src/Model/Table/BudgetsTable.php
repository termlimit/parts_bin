<?php
namespace App\Model\Table;

use ArrayObject;
use App\Model\Entity\Budget;
use App\Model\Entity\BudgetLimits;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * Budgets Model
 *
 * @property \Cake\ORM\Association\belongsTo $Categories
 * @property \Cake\ORM\Association\belongsTo $Users
 * @property \Cake\ORM\Association\hasMany $BudgetLimits
 */
class BudgetsTable extends Table
{
	use SoftDeleteTrait;
	protected $softDeleteField = 'deleted_date';

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

		$this->belongsTo('Categories');
		$this->belongsTo('Users');
		$this->hasMany('BudgetLimits');
	}

	/**
	 * Get the budget data for a given category, month, and year
	 * @param	int		$id			Category ID
	 * @param	int		$user_id	Currently logged in user
	 * @param	Time	$startdate	Date of budget
	 * @access	public
	 * @return	array
	 */
	public function getBudget($id, $user_id, Time $startdate) {
		return $this
			->find()
			->where([
				'Budgets.category_id' => $id,
				'Budgets.user_id' => $user_id
			])
			->matching('BudgetLimits', function ($q) use ($startdate) {
				return $q->where([
					'BudgetLimits.startdate' => $startdate
				]);
			})
			->toArray();
	}

	/**
	 * @param int 			$id
	 * @param Time			$month
	 *
	 * @return array
	 */
	public function getByCategoryAndMonth($id, \Cake\I18n\Time $month)
	{
		return $this
			->find()
			->where(['Budgets.category_id' => $id])
			->matching('BudgetLimits', function ($q) use ($month) {
				return $q->where([
					'BudgetLimits.startdate' => $month,
				]);
			})
			->toArray();
	}

	/**
	 * isOwnedBy current user in session owns the budget
	 *
	 * @param int	$id
	 * @param int	$user_id
	 * @return boolean true on owner
	 */
	public function isOwnedBy($id, $user_id)
	{
		return $this->exists(['id' => $id, 'user_id' => $user_id]);
	}

	public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
	{
		$data['user_id'] = Configure::read('GlobalAuth.id');
	}
}
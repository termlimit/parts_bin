<?php
namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

class StartupHelper extends Helper
{

	/**
	 * @return array
	 */
	public function findAccountTypesList()
	{
		return TableRegistry::get('AccountTypes')
			->find('list')
			->where([
				'deleted_date IS NULL',
				'deleted' => 0,
			])
			->order(['title' => 'ASC'])
			->toArray();
	}

	/**
	 * @return array
	 */
	public function findCategoryTreeList()
	{
		return TableRegistry::get('Categories')
			->ParentCategories
			->find('treeList')
			->where(['user_id' => Configure::read('GlobalAuth.id')])
			->toArray();
	}

	/**
	 * @return string
	 */
	public function displayBudgets($budget, $Form) {
		$cell = '';
		$cell .= '<div class="col-xs-4">';
		$cell .= $Form->input('category.'.$budget->id.'.amount', ['class' => 'form-control', 'label' => __d('installer', $budget->title . ' Amount') . ' *', 'value' => 0.00]);
		$cell .= '<em>'.__d('installer', 'Amount to budget (always positive).').'</em>';
		$cell .= '</div>';
		if( count($budget->children) > 0) {
			foreach ($budget->children as $children) {
				$cell .= $this->displayBudgets($children, $Form);
			}
		}

		return $cell;
	}
}

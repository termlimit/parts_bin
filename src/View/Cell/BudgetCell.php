<?php
namespace App\View\Cell;

use Cake\View\Cell;
use Cake\I18n\Number;

class BudgetCell extends Cell
{
	/**
	 * @var object Time helper
	 */
	protected $Time;
	/**
	 * @var object Number helper
	 */
	protected $Number;
	/**
	 * @var object Form helper
	 */
	protected $Form;
	/**
	 * @var Time   Start Date
	 */
	protected $start;
	/**
	 * @var Time   Previous Month
	 */
	protected $prev;
	/**
	 * @var Time   Next Month
	 */
	protected $next;

	/**
	 * Process children to create a cell for parent categories
	 *
	 * @param object $children
	 * @param int $depth
	 * @param object $Html helper
	 * @param object $Time helper
	 * @param object $Form helper
	 */
	public function row($budgets, $depth, $start, $time, $number, $form)
	{
		$this->Time = $time;
		$this->Number = $number;
		$this->Form = $form;
		$this->start = $start;
		$this->next = clone $this->start;
		$this->prev = clone $this->start;
		$this->next->addMonth();
		$this->prev->subMonth();

		// If there are children, don't show details
		$cell = '<div class="box-body">';
		$cell .= '<!-- SET ACCORDION NUMBER -->';
		$cell .= '<div class="box-group" id="accordion-0">';
		foreach ($budgets as $budget) {
			#$cell .= $this->recursive($budget, $depth);
			$cell .= $this->recurse_div($budget, $depth);
		}
		$cell .= '</div><!-- box-group -->';
		$cell .= '</div><!-- box-body -->';
		$this->set(compact('cell'));
	}

	/**
	 * recurse_div
	 * @param object $children
	 * @param int $depth
	 * @access protected
	 * @see status(), money(), start(), recurse_div()
	 * @return string
	 */
	protected function recurse_div($child, $depth)
	{
		// cell color
		//$budget = $child->profit == 1 ? $child->budgets['budget'] : $child->budgets['budget']*-1;
		$budget = $child->profit;
		$color = $this->status($child);

		$cell = '<!-- add the .panel class so bootstrap.js collapse plugin detects it -->';
		$cell .= '<!-- SET BOX-COLOR -->';
		$cell .= '<div class="panel box box-'.$color.'">';
		$cell .= '<div class="box-header with-border">';
		$cell .= '<h3 class="box-title">';
		if( count($child->children) > 0) {
			$cell .= '<!-- SET DATA-PARENT AND HREF -->';
			$cell .= '<a data-toggle="collapse" data-parent="#accordion-'.$child->parent_id.'" href="#collapse'.$child->id.'">';
			$cell .= $child->title;
			$cell .= '</a>';
		} else {
			$cell .= $this->Form->postLink('<i class="fa fa-fw fa-arrow-left"></i>',
					['action' => 'copy', $child->budget_id.'_'.$this->prev->format('Y-M-d').'_'.$child->budgets['budget']],
					['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Copy this budget of '.$this->money($child->budgets['budget']).' to '.$this->prev->format('Y-M-d').'?')]);
			$cell .= $this->Form->postLink('<i class="fa fa-fw fa-arrow-right"></i>',
					['action' => 'copy', $child->budget_id.'_'.$this->next->format('Y-M-d').'_'.$child->budgets['budget']],
					['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Copy this budget of '.$this->money($child->budgets['budget']).' to '.$this->next->format('Y-M').'?')]);
			$cell .= $child->title;
		}
		$cell .= '</h3>';
		$cell .= '<div class="box-tools pull-right">';
		$cell .= '<!-- Buttons, labels, and many other things can be placed here! -->';
		$cell .= '<!-- Here is a label for example -->';
		$cell .= '<!-- SET LABEL-COLOR -->';
		if (count($child->children) > 0) {
			$cell .= '<span class="label label-primary">Budget: '.$this->money($child->budgets['budget']).'</span>' . "\n";
		} else {
			$cell .= '<!-- Buttons, labels, and many other things can be placed here! -->';
			$cell .= '<div class="btn-group">';
			$cell .= '<div class="form-group" style="margin-bottom:0;">';
			$cell .= '<div class="input-group-xs">';
			$cell .= '<input name="id" value="budget_'.$child->budget_id.'_'.$this->start->format('Y-M-d').'_'.$child->budgets['budget'].'" type="hidden">';
			$cell .= '<input name="budget_id" value="'.$child->budget_id.'" type="hidden">';
			$cell .= '<input class="form-control budgetAmount" data-original="'.$child->budgets['budget'].'" data-id="'.$child->budget_id.'" value="'.$child->budgets['budget'].'" autocomplete="off" step="1" min="0" max="99999999" name="amount" type="number">';
			$cell .= '</div><!-- input-group-->';
			$cell .= '</div><!-- form-group -->';
			$cell .= '</div><!-- btn-group -->' . "\n";
		}
		$cell .= '<!-- SET LABEL-COLOR -->';
		$cell .= '<span class="label label-primary">Actual: '.$this->money($child->budgets['spent']+$child->budgets['earned']).'</span>' . "\n";
		$cell .= '<!-- SET LABEL-COLOR -->';
		$cell .= '<span class="label label-'.$color.'">Difference: '.$this->money(($child->budgets['spent']+$child->budgets['earned'])-$child->budgets['budget']).'</span>' . "\n";
		$cell .= '</div>';
		$cell .= '<!-- /.box-tools -->';
		$cell .= '</div>';
		$cell .= '<!-- /.box-header -->';
		if( count($child->children) > 0) {
			$cell .= '<!-- SET ID -->';
			$cell .= '<div id="collapse'.$child->id.'" class="panel-collapse collapse">';
			$cell .= '<div class="box-body">';
			$cell .= '<!-- SET ACCORDION NUMBER -->';
			$cell .= '<div class="box-group" id="accordion'.$child->id.'">';
			// If there are children, don't show details
			foreach ($child->children as $children) {
				$depth++;
				$cell .= $this->recurse_div($children, $depth);
				$depth--;
			}
			$cell .= '</div>';
			$cell .= '</div>';
			$cell .= '</div>';
		} else {
			$cell .= '<div class="panel-collapse collapse">';
			$cell .= '<div class="box-body"></div>';
			$cell .= '</div>';
		}
		$cell .= '</div>';

		return $cell;
	}

	protected function budgets($budget_id, $amount, $color = null, $current = null)
	{
		$budgetMaximum = 99999999;
		$cell = '';
		$next = clone $this->start;
		$prev = clone $this->start;
		$next->addMonth();
		$prev->subMonth();

		if ($current === null) {
			// Cell shading, either <p class="text-green"><p class="text-red"><p class="text-muted">(default).
			$color = $color === null ? 'text-muted' : $color;
			$cell .= '<td><p class="'.$color.'">'.$this->Number->currency($amount, 'USD').'</p></td>';
		} else {
			$cell .= '<td>'.
				$this->Form->postLink('<i class="fa fa-fw fa-arrow-left"></i>',
					['action' => 'copy', $budget_id.'_'.$prev->format('Y-M-d').'_'.$amount],
					['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Copy this budget to '.$prev->format('Y-M-d').'?')])
				.'</td>';
			$cell .= '<td>';
			$cell .= '<div class="form-group" style="margin-bottom:0;">';
			$cell .= '<div class="input-group">';
			$cell .= '<div class="input-group-addon">$</div>';
			$cell .= '<input type="hidden" name="id" value="budget_'.$budget_id.'_'.$this->start->format('Y-M-d').'_'.$amount.'"/>';
			$cell .= '<input type="hidden" name="budget_id" value="'.$budget_id.'"/>';
			$cell .= '<input class="form-control budgetAmount" data-original="'.$amount.'" ';
			$cell .= 'data-id="'.$budget_id.'" value="'.$amount.'" autocomplete="off" ';
			$cell .= 'step="1" min="0" max="'.$budgetMaximum.'" name="amount" type="number">';
			$cell .= '</div>';
			$cell .= '</div>';
			$cell .= '</td>';
			$cell .= '<td>'.
				$this->Form->postLink('<i class="fa fa-fw fa-arrow-right"></i>',
					['action' => 'copy', $budget_id.'_'.$next->format('Y-M-d').'_'.$amount],
					['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Copy this budget to '.$next->format('Y-M-d').'?')])
				.'</td>';
		}
#id="budget_'.$row['id'].'_'.$year.'-'.sprintf("%02d", $b+1).'-01"
		return $cell;
	}

	protected function money($amount, $currency = 'USD')
	{
		return Number::currency($amount, $currency);
		//return sprintf("%01.2f", $amount);
	}

	protected function status($child)
	{
		$settings['cell_highlight_percent'] = 80;
		// primary (blue), danger (red), warning (yellow), success (green)
		// Spent is always a positive number, convert to negative
		$a = sprintf("%01.2f", ($child->budgets['spent'])+$child->budgets['earned']);
		// Budgets are relative to the category and user input
		#$b = sprintf("%01.2f", ($child->profit == 0 ? $child->budgets['budget'] : $child->budgets['budget']));
		$b = sprintf("%01.2f", $child->budgets['budget']);
/**		if ($child->id == 124) {
			echo '$a: ' . $a . '<br>';
			echo '$spent: ' . $child->budgets['spent'] . '<br>';
			echo '$earned: ' . $child->budgets['earned'] . '<br>';
			echo '$b: ' . $b . '<br>';
			echo '$budget: ' . $child->budgets['budget'];
			exit;
		}**/

		// Income, overage is a good thing
		if($child->profit == 1) {
			if($a < 0.00) {
				return 'danger';
			}
			if($a == 0.00 && $b == 0.00) {
				return 'success';
			}
			if($b == 0.00) {
				return 'success';
			}
			// Value is over
			if($a >= $b) {
				return 'success';
			}
			// Value is between warning range
			if( ($a / $b) > ( $settings['cell_highlight_percent'] / 100 ) && ($a / $b) < 1 ) {
				return 'danger';
			}
			// Value is under
			if( ($a / $b) < ( $settings['cell_highlight_percent'] / 100 ) ) {
				return 'danger';
			}
		}
		// Loss, overage is a bad thing
		if($child->profit == 0) {
			if($a == 0.00) {
				return 'success';
			}
			// If the budget is zero (or not set) and the value is less than 0.00, it is assumed a loss (red)
			if($b == 0.00 && $a < 0.00) {
				return 'danger';
			}
			// If the budget is zero (or not set) and the value is greater than 0.00, it is assumed profitable (green)
			if($b == 0.00 && $a >= 0.00) {
				return 'success';
			}
			// If the budget is positive and the value is positive, compare which is greater
			if( ($b > 0.00 && $a > 0.00) && $a >= $b) {
				return 'success';
			}
			// If the budget is positive and the value is positive, compare which is greater
			if( ($b > 0.00 && $a > 0.00) && $a < $b) {
				return 'danger';
			}
			// Value is over
			if($a / $b > 1) {
				return 'danger';
			}
			// Value is between warning range
			if( ($a / $b) > ( $settings['cell_highlight_percent'] / 100 ) && ($a / $b) <= 1 ) {
				return 'success';
			}
			// Value is under
			if( ($a / $b) < ( $settings['cell_highlight_percent'] / 100 ) ) {
				return 'success';
			}
		}
		if($profit == 3) {
			if($a < $b) {
				return 'danger';
			}
			if($a >= $b) {
				return 'success';
			}
		}
	}

	/**
	 * Compare two values based on the database settings
	 * $profit	string	Either 0 or 1. 1 would use cell_highlight_positive for over value
	 * $a = actual
	 * $b = budget
	 */
	#public function compare($a, $b, $settings = NULL, $profit = 0) {
	public function compare($child)
	{
		$settings = array(
			'cell_highlight_color_negative' => 'text-red',
			'cell_highlight_color_positive' => 'text-green',
			'cell_highlight_color_warning' => 'text-yellow',
			'cell_highlight_percent' => 80,
		);

		$a = sprintf("%01.2f", ($child->budgets['spent']*-1)+$child->budgets['earned']);
		$b = sprintf("%01.2f", ($child->profit == 0 ? $child->budgets['budget'] : $child->budgets['budget']));
		#$a = sprintf("%01.2f", $a);
		#$b = sprintf("%01.2f", $b);

		// Income, overage is a good thing
		if($child->profit == 1) {
			if($a < 0.00) {
				return $settings['cell_highlight_color_negative'];
			}
			if($a == 0.00 && $b == 0.00) {
				return $settings['cell_highlight_color_positive'];
			}
			if($b == 0.00) {
				return $settings['cell_highlight_color_positive'];
			}
			// Value is over
			if($a >= $b) {
				return $settings['cell_highlight_color_positive'];
			}
			// Value is between warning range
			if( ($a / $b) > ( $settings['cell_highlight_percent'] / 100 ) && ($a / $b) < 1 ) {
				return $settings['cell_highlight_color_negative'];
			}
			// Value is under
			if( ($a / $b) < ( $settings['cell_highlight_percent'] / 100 ) ) {
				return $settings['cell_highlight_color_negative'];
			}
		}
		// Loss, overage is a bad thing
		if($child->profit == 0) {
			if($a == 0.00) {
				return $settings['cell_highlight_color_positive'];
			}
			// If the budget is zero (or not set) and the value is less than 0.00, it is assumed a loss (red)
			if($b == 0.00 && $a < 0.00) {
				return $settings['cell_highlight_color_negative'];
			}
			// If the budget is zero (or not set) and the value is greater than 0.00, it is assumed profitable (green)
			if($b == 0.00 && $a >= 0.00) {
				return $settings['cell_highlight_color_positive'];
			}
			// If the budget is positive and the value is positive, compare which is greater
			if( ($b > 0.00 && $a > 0.00) && $a >= $b) {
				return $settings['cell_highlight_color_positive'];
			}
			// If the budget is positive and the value is positive, compare which is greater
			if( ($b > 0.00 && $a > 0.00) && $a < $b) {
				return $settings['cell_highlight_color_negative'];
			}
			// Value is over
			if($a / $b > 1) {
				return $settings['cell_highlight_color_negative'];
			}
			// Value is between warning range
			if( ($a / $b) > ( $settings['cell_highlight_percent'] / 100 ) && ($a / $b) <= 1 ) {
				return $settings['cell_highlight_color_positive'];
			}
			// Value is under
			if( ($a / $b) < ( $settings['cell_highlight_percent'] / 100 ) ) {
				return $settings['cell_highlight_color_positive'];
			}
		}
		if($profit == 3) {
			if($a < $b) {
				return $settings['cell_highlight_color_negative'];
			}
			if($a >= $b) {
				return $settings['cell_highlight_color_positive'];
			}
		}
	}
}
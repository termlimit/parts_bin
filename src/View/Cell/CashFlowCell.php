<?php
namespace App\View\Cell;

use Cake\View\Cell;

class CashFlowCell extends Cell
{
	/**
	 * Total actual for the month
	 * @var		array	$actual_month_total
	 * @access	protected
	 */
	protected $actual_month_total = array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

	/**
	 * Total budget for the month
	 * @var		array	$budget_month_total
	 * @access	protected
	 */
	protected $budget_month_total = array(0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

	/**
	 * Total for the year
	 * @var		float	$actual_year_total
	 * @access	protected
	 */
	protected $actual_year_total = 0.00;

	/**
	 * Total budget for the year
	 * @var		float	$budget_year_total
	 * @access	protected
	 */
	protected $budget_year_total = 0.00;

	/**
	 * Counter variable
	 * @var		int		$counter
	 * @access	protected
	 */
	protected $counter = 0;

	/**
	 * This will hold the HTML created from the array
	 * @var		array	$tableTree
	 * @access	protected
	 */
	protected $tableTree;

	/**
	 * Current root level number
	 * @var		int		$number
	 * @access	protected
	 */
	protected $number = 0;

	/**
	 * Number of root entries
	 * @var		int		$numberRoot
	 * @access	protected
	 */
	protected $numberRoot;

	/**
	 * Current year
	 * @var		int		$year
	 * @access	protected
	 */
	protected $year;

	/**
	 * Current month
	 * @var		int		$currentMonth
	 * @access	protected
	 */
	protected $currentMonth;

	/**
	 * @var object Time helper
	 */
	protected $Time;

	/**
	 * @var object Number helper
	 */
	protected $Number;

	/**
	 * @var object Html helper
	 */
	protected $Html;

	/**
	 * @var object Calculate helper
	 */
	protected $Calculate;

	/**
	 * Recursive function that returns categories as a nested html unorderd list
	 *
	 * This function will create the inner workings of the table for the category
	 * sub-category setup. It will eventually reference an external element for
	 * styling.
	 *
	 * @access	public
	 * @param	array	$category
	 * @param	int		$depth		This is the depth level to swap to the alternate display location
	 * @param	boolean	$display	This will determine where the total is located
	 * @param	int		$showDepth	At what level will the categories be nested
	 * @param 	object 	$Time helper
	 * @param	object 	$Number helper
	 * @param 	object 	$Html helper
	 * @param 	object 	$Calculate helper
	 * @return	string	$return
	 */
	public function display(array $category, $depth = 0, $display = 0, $showDepth = 1, $parent = 0, $year, $Time, $Number, $Html, $Calculate)
	{
		$this->Time = $Time;
		$this->Number = $Number;
		$this->Html = $Html;
		$this->Calculate = $Calculate;
		$this->year = $year;
		$this->currentMonth = date('m');
		$this->tableTree = '';

		$cell = $this->recurse($category, $depth, $display, $showDepth, $parent);
		$this->set(compact('cell'));
		$this->set('year', $this->year);
		$this->set('currentMonth', $this->currentMonth);
		$this->set('actual_month_total', $this->actual_month_total);
		$this->set('budget_month_total', $this->budget_month_total);
		$this->set('actual_year_total', $this->actual_year_total);
		$this->set('budget_year_total', $this->budget_year_total);
	}

	public function recurse(array $category, $depth = 0, $display = 0, $showDepth = 1, $parent = 0)
	{
		$padding = 5;
		foreach($category as $row) {
			// Count the root level categories
			if($depth == 0) {
				$this->number++;
			}
			// If the current depth is less than show depth, display total below other children
			if( isset($row['child']) && is_array($row['child']) ) {
				// This is the paddding for each depth level
				$padding = $depth * 10;
				// By default only show the root level, all others are hidden
				if($depth >= $showDepth) {
					#$hidden = 'display:none';
				}
				########
				# START Root categories
				########
				if($depth == 0) {
					$bg_col = $row['profit'] == 1 ? '#009900' : '#990000';
					$this->tableTree .= '<tr style="'.(isset($hidden) ? $hidden : '').';background-color:'.$bg_col.'; color:#FFF;" class="parent"><td width="160px" style="font-weight:bold; padding-left:'.($padding+5).'px">';
					$this->tableTree .= $row['title'].'</td><td colspan="13">&nbsp;</td></tr>';
				########
				# END Root categories
				########
				# START Child categories
				########
				} else {
					$this->tableTree .= '<tr style="'.(isset($hidden) ? $hidden : '').'" class="child-'.$parent.'"><td width="160px" style="font-weight:bold; padding-left:'.$padding.'px">';
					$this->tableTree .= $row['title'].'</td>';
					// Loop through the totals for the months
					if( isset($row['detail']) && is_array($row['detail']) ) {
						$category_year_total = 0.00;
						$budget_year_total = 0.00;
						$b = 0;
						foreach($row['detail'] as $detail) {
							$child_cell = $this->Calculate->compare(
								$detail['amount'],
								$detail['budget'],
								NULL, 3);
							//if( ($b+1) > $this->currentMonth ) $child_cell = '000000'; OLD METHOD ONLY DEALING WITH MONTH 2012-01-02
							if( (($b+1) > $this->currentMonth) && ($this->year >= date("Y")) ) $child_cell = '000000';
							$background_color = ($this->currentMonth == $b+1 && $this->year == date("Y")) ? 'background-color:#CCC' : '';
							$this->tableTree .= '<td style="color:#'.$child_cell.';'.$background_color.'; font-weight:bold;" title="'.$row['title'] . ' Budget: ' . $this->Number->currency($detail['budget'], 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2]).'">'.$this->Number->currency($detail['amount'], 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2]).'</td>';
							$category_year_total += $detail['amount'];
							$budget_year_total += $detail['budget'];
							$b++;
						}
						$child_cell = $this->Calculate->compare(
							$category_year_total,
							$budget_year_total,
							NULL, 3);
						$this->tableTree .= '<td style="color:#'.$child_cell.'" title="'.$row['title'] . ' Budget: ' . $this->Number->currency($budget_year_total, 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2]).'">'.$this->Number->currency($category_year_total, 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2]).'</td>';
						$this->tableTree .= '</tr>';
					}
				}
				########
				# END Child Categories
				########
				$depth++;
				$this->counter++;
				if( is_array($row['child']) ) {
					$this->recurse($row['child'], $depth, $display, 1, $row['id']);
				}
				$depth--;
				if($depth == 0) {
					########
					# START Root category total for each month
					########
					if( isset($row['detail']) && is_array($row['detail']) ) {
						$this->tableTree .= '<tr><td class="total-footer">&nbsp;</td>';
						$category_year_total = 0.00;
						$budget_year_total = 0.00;
#						foreach($row['detail'] as $detail) {
#							$this->tableTree .= '<td>'.$detail['amount'].'</td>';
#							$category_year_total += $detail['amount'];
#						}
						// Loop through the months and compile the year total and the month total
						for($a = 0; $a < 12; $a++) {
							// This is all based off of start of 0 for the month_total, but the arrays need +1
							$actual_grand_cell = $this->Calculate->compare( // This is the yearly total per group (parent_id = 0)
								$row['detail'][$a+1]['amount'],
								$row['detail'][$a+1]['budget'],
								NULL, $row['profit']);
								//if( ($a+1) > $this->currentMonth ) $actual_grand_cell = '000000'; OLD METHOD ONLY DEALING WITH MONTH 2012-01-02
								if( (($a+1) > $this->currentMonth) && ($this->year >= date("Y")) ) $actual_grand_cell = '000000';
								$background_color = ($this->currentMonth == $a+1 && $this->year == date("Y")) ? 'background-color:#CCC' : '';
							//$this->tableTree .= '<td>$'.number_format($row['detail'][$a+1]['amount'], 2, '.', ',').'</td>';
							$this->tableTree .= '<td class="total-footer" style="color:#'.$actual_grand_cell.';'.$background_color.'; font-weight:bold;" title="'.$this->Calculate->getMonthString( ($a+1) ) . ' ' . $row['title'] . ' Budget: ' . $this->Number->currency($row['detail'][$a+1]['budget'], 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)).'">'.$this->Number->currency($row['detail'][$a+1]['amount'], 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)).'</td>';
							$category_year_total += $row['detail'][$a+1]['amount'];
							$budget_year_total += $row['detail'][$a+1]['budget'];
						}
						$actual_total_cell = $this->Calculate->compare(
							$category_year_total,
							$budget_year_total,
							NULL, 3);
						$this->tableTree .= '<td class="total-footer" style="color:#'.$actual_total_cell.'" title="'.$row['title'] . ' Budget: ' . $this->Number->currency($budget_year_total, 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)).'">'.$this->Number->currency($category_year_total, 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)).'</td>';
						$this->tableTree .= '</tr>';
					}
					########
					# END Root category total for each month
					########
				}
				if($this->number < $this->numberRoot && $depth == 0) {
					$this->tableTree .= '<tr><td colspan="14">&nbsp;</td></tr>';
				}
			// If no children for this row
			} else {
				// Adjust the counter down one notch if depth > showDepth
				if($depth > $showDepth) {
					$this->counter--;
				}
				// By default only show the root level, all others are hidden
				if($depth >= $showDepth) {
					#$hidden = 'display:none';
				}
				$this->tableTree .= '<tr style="'.(isset($hidden) ? $hidden : '').'" class="child-'.$parent.'">';
				// This is the paddding for each depth level
				$padding = $depth * 10;
				$this->tableTree .= '<td width="160px" style="padding-left:'.$padding.'px;">';
				$this->tableTree .= $row['title'].'</td>';
				$category_year_total = 0.00;
				$budget_year_total = 0.00;
				$b = 0;
				########
				# START Cell for each of the category/month
				########
				foreach($row['detail'] as $detail) {
					$budget = 0.00; // Reset the budget each time
					$cell = '000000'; // Reset the cell each time
					if(is_array($detail)) { // If the detail is an array, there is data to calculate
						if ($detail['budget'] != 0.00) {
							// Budget exists, compare lines
							$cell = $this->Calculate->compare($detail['amount'], $detail['budget'], NULL, $row['profit']);
						} elseif ($detail['budget'] == 0.00 && $row['profit'] == 1) {
							// No budget exists, but this is a profitable line, so anything less than 0 is shown in red
							$cell = $this->Calculate->compare($detail['amount'], 0.00, NULL, $row['profit']);
						} elseif ($detail['budget'] == 0.00 && $row['profit'] == 0) {
							// No budget exists, but this is an expense, so anything less than 0 is shown in red
							$cell = $this->Calculate->compare($detail['amount'], 0.00, NULL, $row['profit']);
						}
						if( (($b+1) > $this->currentMonth) && ($this->year >= date("Y")) ) $cell = '000000';
						$background_color = ($this->currentMonth == $b+1 && $this->year == date("Y")) ? 'background-color:#CCC' : '';
						$category_year_total += $detail['amount'];
						$budget_year_total += $detail['budget'];
						$this->tableTree .= '<td style="color:#'.$cell.';'.$background_color.'; font-weight:bold;">'.
							$this->Html->link(
								$this->Number->currency($detail['amount'], 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)),
								array(
									'controller' => 'ledgers', 'action' => 'index/credit/'.$row['id'].'/?month='.sprintf("%02d", ($b+1)).'&year='.$this->year
								), array(
									'escape' => false,
									'title' => $row['title'] . ' ' . $this->Calculate->getMonthString( ($b+1) ) . ', ' . $this->year . ' Budget: '. $this->Number->currency($detail['budget'], 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)), 'style' => 'color:#'.$cell
								)
							).'</td>';
						// Add to the actual and budget month arrays
						$this->actual_month_total[$b] += $detail['amount'];
						$this->budget_month_total[$b] += $detail['budget'];
					} else {
						$this->tableTree .= '<td>'.$this->Number->currency(0.00, 'USD', array('after' => false, 'zero' => '$0.00', 'places' => 2)).'</td>';
					}
					$b++;
				}
				########
				# END Cell for each of the category/month
				########
				$this->actual_year_total += $category_year_total;
				$this->budget_year_total += $budget_year_total;
				// This is the yearly total for each category
				$actual_cell = $this->Calculate->compare(
					$category_year_total,
					$budget_year_total,
					NULL, $row['profit']
				);
				$this->tableTree .= '<td style="color:#'.$actual_cell.'" title="'.$row['title'] . ' Budget: ' . $this->Number->currency($budget_year_total, 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)).'">'.
					$this->Html->link(
						$this->Number->currency($category_year_total, 'USD', array('after' => false, 'zero' => '0.00', 'places' => 2)),
						array(
							'controller' => 'ledgers', 'action' => 'index/credit/'.$row['id'].'/?month=0&year='.$this->year
						), array(
							'escape' => false,
							'style' => 'color:#'.$actual_cell
						)
					)
				.'</td>';
				$this->tableTree .= '</tr>';
				$this->counter++;
				if($this->number < $this->numberRoot && $depth == 0) {
					$this->tableTree .= '<tr><td colspan="14">&nbsp;</td></tr>';
				}
			}
		}
		return $this->tableTree;
	}
}
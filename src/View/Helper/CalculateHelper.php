<?php
namespace App\View\Helper;

use Cake\View\Helper;

class CalculateHelper extends Helper
{

	public function calc($account, $amount, $total) {
		// Figure out the transfer either into an account or an expense
		if($account != 0) {
			$total += $amount;
		} else {
			$total -= $amount;
		}
		return $total;
	}

	/**
	 * Compare two values based on the database settings
	 * $profit	string	Either 0 or 1. 1 would use cell_highlight_positive for over value
	 */
	public function compare($a, $b, $settings = NULL, $profit = 0) {
		$settings = array(
			'cell_highlight_color_negative' => 'FF0000',
			'cell_highlight_color_positive' => '00CC00',
			'cell_highlight_color_warning' => '999900',
			'cell_highlight_percent' => 80,
		);
		//if($settings === NULL) { $settings = $this->getSettingData(); }
		$a = sprintf("%01.2f", $a);
		$b = sprintf("%01.2f", $b);

		// Income, overage is a good thing
		if($profit == 1) {
			if($a < 0.00) {
				return $settings['cell_highlight_color_negative'];
			}
			if($a == 0.00 && $b == 0.00) {
				//return '000000';
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
		if($profit == 0) {
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

	/**
	 * Given an integer, convert that to the equivalent month in text format
	 * @param	int		$month
	 * @access	public
	 * @return	string
	 */
	public function getMonthString($month) {
		// This has a hard-coded date, no need to make this variable and waste resources
		$timestamp = mktime(0, 0, 0, $month, 1, 2011);

		return date("F", $timestamp);
	}
}
?>
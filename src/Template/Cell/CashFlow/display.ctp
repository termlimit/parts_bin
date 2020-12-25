<?=$cell?>
<tr><td colspan="14">&nbsp;</td></tr>
<tr>
	<td class="foot">Cash Flow Totals:</td>
	<?php for($a = 0; $a < 12; $a++):?>
		<?php
			$actual_month_cell = $this->Calculate->compare($actual_month_total[$a],$budget_month_total[$a], NULL, 3);
			//if( ($a+1) > $currentMonth ) $actual_month_cell = '000000'; OLD METHOD ONLY DEALING WITH MONTH 2012-01-02
			if( (($a+1) > $currentMonth) && ($year >= date("Y")) ) $actual_month_cell = '000000';
			$background_color = ($currentMonth == $a+1 && $year == date("Y")) ? 'background-color:#CCC' : '';
		?>
		<td class="foot" style="border-top:2px solid black; color:#<?=$actual_month_cell?>;<?=$background_color?>" title="<?=$this->Calculate->getMonthString( ($a+1) ) . ', ' . $year . ' Budget: ' . $this->Number->currency($budget_month_total[$a], 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2])?>">
			<?=$this->Html->link(
				$this->Number->currency($actual_month_total[$a], 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2]),
				['controller' => 'ledgers', 'action' => '?month='.sprintf("%02d", ($a+1)).'&year='.$year],
				['escape' => false, 'style' => 'color:#'.$actual_month_cell]);
			?>
		</td>
	<?php endfor;?>
	<?php $year_cell = $this->Calculate->compare($actual_year_total, $budget_year_total, NULL, 3); // This is the total for the year of all months and categories?>
	<td style="border-top:2px solid black; color:#<?=$year_cell?>" title="<?=$year . ' Budget: ' . $this->Number->currency($budget_year_total, 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2])?>">
		<?=$this->Number->currency($actual_year_total, 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2])?>
	</td>
</tr>

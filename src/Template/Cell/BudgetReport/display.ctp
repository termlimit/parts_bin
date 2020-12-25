<?=$cell?>
<tr>
	<td class="foot">Budget totals:</td>
	<?php for($a = 0; $a < 12; $a++):?>
		<td class="foot" style="font-weight:bold; border-top:2px solid black;" title="<?=$this->Calculate->getMonthString( ($a+1) ) . ', ' . $year . ' actual: ' . $this->Number->currency($actual_month_total[$a], 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2])?>">
			<?=$this->Number->currency($budget_month_total[$a], 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2])?>
		</td>
	<?php endfor;?>
	<td style="font-weight:bold; border-top:2px solid black;" title="<?=$year . ' actual: ' . $this->Number->currency($actual_year_total, 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2])?>">
		<?=$this->Number->currency($budget_year_total, 'USD', ['after' => false, 'zero' => '0.00', 'places' => 2])?>
	</td>
</tr>

<?php
// Budget set, could be + or -.  Category could be + or - by expectation.
//$periodBalance = 50.00;
//$budget = 40.00;
if ($budget == 0 && $periodBalance >= $budget):?>
<span class="text-success">No budget set, credit of: <?=$this->Number->currency($periodBalance, $currency)?></span>
<?php endif;?>
<?php if ($budget == 0 && $periodBalance < $budget):?>
<span class="text-danger">No budget set, in debt: <?=$this->Number->currency($periodBalance, $currency)?></span>
<?php endif;?>
<?php if ($budget < 0 && $periodBalance < $budget):?>
<span class="text-danger">Over budget by: <?=$this->Number->currency(($periodBalance + ($budget * -1)), $currency)?></span>
<?php endif;?>
<?php if ($budget == $periodBalance):?>
<span class="text-success">On budget!</span>
<?php endif;?>
<?php if ($budget < 0 && $periodBalance > $budget):?>
<span class="text-success">Budget remaining: <?=$this->Number->currency(($periodBalance - $budget), $currency)?></span>
<?php endif;?>
<?php if ($budget > 0 && $periodBalance < $budget):?>
<span class="text-danger">Budget goal not yet reached, under by: <?=$this->Number->currency(($budget - $periodBalance), $currency)?></span>
<?php endif;?>
<?php if ($budget > 0 && $periodBalance > $budget):?>
<span class="text-success">Budget reached by: <?=$this->Number->currency(($periodBalance - $budget), $currency)?></span>
<?php endif;?>
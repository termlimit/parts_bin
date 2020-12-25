<?php if ($deviation > 0):?>
<span class="description-deviation text-green">
	<i class="fa fa-caret-up"></i> $<?=number_format($deviation, 2)?>
</span>
<?php else:?>
<span class="description-deviation text-red">
	<i class="fa fa-caret-down"></i> $<?=number_format($deviation, 2)?>
</span>
<?php endif;?>
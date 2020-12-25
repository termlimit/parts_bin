<h1>
	<?php if (isset($mainTitleIcon)):?>
		<i class="hidden-xs fa <?=$mainTitleIcon?>"></i>
	<?php endif;?>
	<?=$title?>
	<?php if (isset($subTitle)):?>
		<small id="subTitle">
			<?php if (isset($subTitleIcon)):?>
				<i class="hidden-xs fa <?=$subTitleIcon;?>"></i>
			<?php endif;?>
			<?=$subTitle?>
		</small>
	<?php endif;?>
</h1>

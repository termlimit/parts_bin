<ul class="nav nav-pills startup-menu">
	<?php foreach ($menu as $label => $link):?>
	<li class="<?=$link['active'] ? 'active' : 'disabled'?>">
		<?=$this->Html->link($label, ($link['active'] ? $link['url'] : '#'))?>
	</li>
	<?php endforeach;?>
</ul>

<?php
//Counter for row color
$counter = 0;
?>
<!-- Wrapper-->
<div id="wrapper-wide">
	<!-- Content-->
	<div id="content-wide">
		<!-- Box-->
		<div id="box">
			<h3>Categories<a href="/categories/add" class="button" title="New Category">
			<?php echo $this->Html->image('icons/plus.gif', array('alt' => 'New Category'));?>New Category</a></h3>
			<?php echo $this->Flash->render(); ?>
			<table width="100%">
				<thead>
					<tr>
						<th><a href="">Title</a></th>
						<th><a href="">Hide PL</a></th>
						<th><a href="">Account</a></th>
						<th><a href="">Expires</a></th>
						<th><a href="">Options</a></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($categories as $category): ?>
					<?php $class = $counter % 2 == 0 ? 'alternate-row' : ''; ?>
					<tr class="<?php echo $class; ?>">
						<td><img src="/theme/live/img/table/bullet.png" title="<?php echo h($category->title);?>" /><?= h($category->title) ?></td>
						<td><?= $category->hide_pl == 0 ? 'No' : 'Yes' ?></td>
						<td><?= $category->account_id == '' ? 'none' : h($category->account->title) ?></td>
						<td><?= $category->expiration == '' ? 'never' : $this->Time->format($category->expiration, 'YYYY-MM-dd') ?></td>
						<td class="actions">
							<?= $this->Html->link(__('View'), ['action' => 'view', $category->id]) ?>
							<?= $this->Html->link(__('Edit'), ['action' => 'edit', $category->id]) ?>
							<?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $category->id], ['confirm' => __('Are you sure you want to delete {0}?', h($category->title))]) ?>
							<?= $this->Form->postLink(__('Move down'), ['action' => 'moveDown', $category->id], ['confirm' => __('Are you sure you want to move down {0}?', h($category->title))]) ?>
							<?= $this->Form->postLink(__('Move up'), ['action' => 'moveUp', $category->id], ['confirm' => __('Are you sure you want to move up {0}?', h($category->title))]) ?>
						</td>
					</tr>
					<?php $counter++; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<!-- /Box-->
	</div>
	<!-- /Content-wide-->
</div>
<!-- /Wrapper-->
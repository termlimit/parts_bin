<?php $class = $counter % 2 == 0 ? 'alternate-row' : '';?>
<?php $padding = $depth * 20;// This is the paddding for each depth level?>
<?php echo '<tr class="<?= $class?>">';?>
	<td style="padding-left:<?= $padding;?>px;"><img src="img/table/bullet.png" id="<?= $row->id;?>" title="<?= h($row->title);?>" /><?= h($row->title)?></td>
	<td><?= ($row->hide_pl == 0 ? 'No' : 'Yes');?></td>
	<td><?= ($row->account_id == '' ? 'none' : h($row->account->title))?></td>
	<td><?= ($row->expiration == '' ? 'never' : $time->format($row->expiration, 'YYYY-MM-dd'))?></td>
	<td class="actions">
		<?= $html->link(__('View'), ['action' => 'view', $row->id])?>
		<?= $html->link(__('Edit'), ['action' => 'edit', $row->id])?>
		<?= $form->postLink(__('Delete'), ['action' => 'delete', $row->id], ['confirm' => __('Are you sure you want to delete {0}?', h($row->title))])?>
		<?= $form->postLink(__('Move down'), ['action' => 'moveDown', $row->id], ['confirm' => __('Are you sure you want to move down {0}?', h($row->title))])?>
		<?= $form->postLink(__('Move up'), ['action' => 'moveUp', $row->id], ['confirm' => __('Are you sure you want to move up {0}?', h($row->title))])?>
	</td>
</tr>
// If there are children, don't show details
if (isset($row['children']) && count($row['children']) > 0) {
	foreach ($row['children'] as $child) {
		$depth++;
		$counter++;
		$this->element('category');
		$cell .= $this->cell('Category::row', [
			'children' => $row,
			'counter' => $counter,
			'depth' => 1,
			'html' => $html,
			'time' => $time,
			'form' => $form
		]);?>
		$cell .= generateCell($child, $counter, $depth, $html, $time, $form);
		$depth--;
	}
}
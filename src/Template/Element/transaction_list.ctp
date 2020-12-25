<div class="box-body table table-responsive no-padding">
	<table class="table table-hover sortable">
		<thead>
			<tr class="ignore">
				<th>&nbsp;</th>
				<th class="hidden-md hidden-sm hidden-xs">&nbsp;</th>
				<th>Date</th>
				<th class="hidden-xs">From</th>
				<th>To</th>
				<th class="hidden-md hidden-sm hidden-xs">Description</th>
				<th>Amount</th>
				<th class="hidden-sm hidden-xs"><i class="fa fa-bar-chart fa-fw" title="category"></i></th>
				<th class="hidden-md hidden-sm hidden-xs"><i class="fa fa-fw fa-rotate-right" title="bill"></i></th>
			</tr>
		</thead>
		<tbody>
			<?php if($none):?>
				<tr><td colspan="10" style="text-align:center; font-weight:bold;font-size:18px;"><?=__('No transactions available')?></td></tr>
			<?php else:?>
			<?php foreach ($journals as $journal): ?>
			<?php
				// Hack to figure out user friendly transaction information
				$n = count($journal->transactions) > 2 ? (count($journal->transactions)-1) : 1;
				$nn = count($journal->transactions) > 2 ? (count($journal->transactions)-2) : 0;
			?>
			<tr>
				<td>
					<div class="btn-group btn-group-xs">
						<a class="btn btn-default btn-xs" title="view" href="/transactions/view/<?=$journal->id?>"><i class="fa fa-fw fa-file-text"></i></a>
						<a class="btn btn-default btn-xs" title="edit" href="/transactions/edit/<?=$journal->id?>"><i class="fa fa-fw fa-pencil"></i></a>
						<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i>', ['controller' => 'transactions', 'action' => 'delete', $journal->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to delete?')]) ?>
					</div>
				</td>
				<td class="hidden-md hidden-sm hidden-xs">
					<?=$this->Journal->typeIcon($journal->transaction_type['type'])?>
				</td>
				<td data-value="<?=$this->Time->format($journal->entered, 'M-d-Y')?>">
					<em><?=$this->Time->format($journal->entered, 'M-d-Y')?></em><br />
					<em style="font-size:10px;"><?=$this->Time->format($journal->posted, 'M-d-Y')?></em>
				</td>
				<td class="hidden-xs"><a href="/accounts/view/<?=$journal->transactions[$n]['account']['id']?>"><?=h($journal->transactions[$n]['account']['title'])?></a></td>
				<td>
					<?php if ($n === 1):?>
						<a href="/accounts/view/<?=$journal->transactions[0]['account']['id']?>"><?=h($journal->transactions[0]['account']['title'])?></a><br />
					<?php else:?>
						<br />
					<?php for ($x = count($journal->transactions)-2; $x >= 0; $x--):?>
					<a href="/accounts/view/<?=$journal->transactions[$x]['account']['id']?>"><?=h($journal->transactions[$x]['account']['title'])?></a><br />
					<?php endfor;?>
					<?php endif;?>
				</td>
				<td class="hidden-md hidden-sm hidden-xs"><?=h($journal->description)?></td>
				<?=$this->element('transaction_amount', ['transaction' => $journal, 'n' => $n])?>
				<td class="hidden-sm hidden-xs" data-value="">
					<?php foreach ($journal->category_transaction_journal as $category => $value):?>
						<a href="/categories/view/<?=$value['category']['id']?>"><?=$value['category']['title']?>
							(<?=$this->Number->currency($value['amount'], $journal->transaction_currency['code'])?>)
						</a><br />
					<?php endforeach;?>
				</td>
				<td class="hidden-md hidden-sm hidden-xs"></td>
			</tr>
			<?php endforeach; ?>
			<?php endif;?>
		</tbody>
	</table>
</div>
<?php if (isset($periodBalance)):?>
<div class="box-footer">
	<div class="pull-right">
		<?php if($none === false):?>
			<?php if($periodBalance < $account->balance):?>
				<span class="text-success"><?=$this->Number->currency($account->balance, $currency)?></span>
			<?php else:?>
				<span class="text-danger"><?=$this->Number->currency($account->balance, $currency)?></span>
			<?php endif;?>
		<?php else:?>
			<span class="text-success"><?=$this->Number->currency($account->balance, $currency)?></span>
		<?php endif;?>
	</div>
</div>
<?php endif;?>
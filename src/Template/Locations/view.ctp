<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<?=$this->element('page-header');?>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li><a href="/<?=$what?>"><?=$what?></a></li>
			<li class="active"> <?=$subTitle . ': ' . $category->title?> for <?=$month?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Current month
							<?php if ($category->expiration != null && $category->expiration < date('YYYY-mm-dd')):?>
							<span class="label label-danger"><i class="fa fa-asterisk text-white"></i> Category expired</span>
							<?php endif;?>
						</h3>
						<div class="box-tools pull-right">
							<div class="btn-group">
								<a href="/categories/edit/<?=$category->id?>" class="btn btn-success btn-xs"><i class="fa fa-fw fa-pencil"></i> Edit category</a>
							</div>
							<div class="btn-group">
								<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i> Delete category', ['action' => 'delete', $category->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to delete {0}?', h($category->title))]) ?>
							</div>
						</div>
					</div>
					<div class="box-body">
						<canvas id="month" style="width:100%;height:350px;"></canvas>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">12 months</h3>
					</div>
					<div class="box-body">
						<canvas id="all" style="width:100%;height:350px;"></canvas>
					</div>
				</div>
			</div>
		</div>
		<!-- actual content -->
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Transactions</h3>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<thead>
								<tr class="ignore">
									<th class="hidden-xs" colspan="2">&nbsp;</th>
									<th class="hidden-sm hidden-xs">Date</th>
									<th class="hidden-xs">From</th>
									<th class="hidden-xs">To</th>
									<th>Description</th>
									<th>Amount</th>
									<th class="hidden-xs"><i class="fa fa-fw fa-rotate-right" title="bill"></i></th>
								</tr>
							</thead>
							<tbody>
								<?php if($none):?>
									<tr><td colspan="8" style="text-align:center; font-weight:bold;font-size:18px;"><?=__('No transactions available')?></td></tr>
								<?php $periodBalance = 0.00;?>
								<?php else:?>
								<?php $periodBalance = 0.00;?>
								<?php foreach ($journals as $journal): ?>
								<?php $periodBalance += $this->Journal->correctAmountByType($journal->transaction_type['type'], $journal->category_transaction_journal[0]['amount']);?>
								<tr>
									<td>
										<div class="btn-group btn-group-xs">
											<a class="btn btn-default btn-xs" title="view" href="/transactions/view/<?=$journal->id?>"><i class="fa fa-fw fa-file-text"></i></a>
											<a class="btn btn-default btn-xs" title="edit" href="/transactions/edit/<?=$journal->id?>"><i class="fa fa-fw fa-pencil"></i></a>
											<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i>', ['controller' => 'Transactions', 'action' => 'delete', $journal->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to delete?')]) ?>
										</div>
									</td>
									<td class="hidden-xs">
										<?=$this->Journal->typeIcon($journal->transaction_type['type'])?>
									</td>
									<td class="hidden-sm hidden-xs" data-value="<?=$this->Time->format($journal->entered, 'M-d-Y')?>">
										<?=$this->Time->format($journal->entered, 'M-d-Y')?>
									</td>
									<td><a href="/accounts/view/<?=$journal->transactions[1]['account']['id']?>"><?=h($journal->transactions[1]['account']['title'])?></a></td>
									<td><a href="/accounts/view/<?=$journal->transactions[0]['account']['id']?>"><?=h($journal->transactions[0]['account']['title'])?></a></td>
									<td><?=h($journal->description)?></td>
									<?=$this->element('transaction_amount', ['transaction' => $journal, 'n' => 1])?>
									<td></td>
								</tr>
								<?php endforeach; ?>
								<?php endif;?>
							</tbody>
						</table>
					</div>
					<div class="box-footer">
						<div class="pull-right">
							<?php if($none === true && $budget == 0):?>
								<span class="text-success">No budget set!</span>
							<?php else:?>
								<?=$this->element('budget_remaining', ['currency' => $currency, 'budget' => $budget, 'periodBalance' => $periodBalance])?>
							<?php endif;?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $this->Html->scriptStart(['block' => 'chartTop']);
	echo "var categoryID = " . $category->id . ";";
$this->Html->scriptEnd();?>
<?=$this->Html->script('Chart.min', ['block' => 'chartBottom'])?>
<?=$this->Html->script('charts', ['block' => 'chartBottom'])?>
<?=$this->Html->script('categories', ['block' => 'chartBottom'])?>
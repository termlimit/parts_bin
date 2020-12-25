<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<?=$this->element('page-header');?>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li class="active"> <?=$subTitle?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Categories</h3>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<thead>
								<tr>
									<th data-defaultsort="disabled">&nbsp;</th>
									<th>Title</th>
									<th class="hidden-xs">Income/Expense</th>
									<th class="hidden-xs">Status</th>
									<th class="hidden-xs">Last Activity</th>
								</tr>
							</thead>
							<tbody>
								<?php if($none):?>
									<tr><td colspan="5" style="text-align:center; font-weight:bold;font-size:18px;"><?=__('No categories available')?></td></tr>
								<?php else:?>
								<?php foreach ($categories as $row): ?>
								<tr>
									<td>
										<div class="btn-group btn-group-xs">
											<a class="btn btn-default btn-xs" href="/categories/edit/<?=$row->id?>"><i class="fa fa-fw fa-pencil"></i></a>
											<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i>', ['action' => 'undelete', $row->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to undelete {0}?', h($row->title))]) ?>
										</div>
									</td>
									<td><a href="/categories/view/<?=$row->id?>"><?= h($row->title)?></a></td>
									<td><?=$row->profit == 0 ? 'Expense' : 'Income'?></td>
									<td><?=$row->deleted_date != null ? 'Deleted' : 'Expired'?></td>
									<td><?=$row->lastTransaction != 'Never' ? $this->Time->format($row->lastTransaction, 'YYYY-MM-dd') : h($row->lastTransaction)?></td>
								</tr>
								<?php endforeach;?>
								<?php endif;?>
							</tbody>
						</table>
					</div>
					<?=$this->element('pagination');?>
				</div>
			</div>
		</div>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
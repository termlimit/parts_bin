<?php $counter = 0;?>
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
						<h3 class="box-title"><?=$subTitle?></h3>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<thead>
								<tr>
									<th data-defaultsort="disabled">&nbsp;</th>
									<th>Account Name</th>
									<th class="hidden-sm hidden-xs">Type</th>
									<th>Status</th>
									<th class="hidden-sm hidden-xs">Last Activity</th>
								</tr>
							</thead>
							<tbody>
								<?php if($none):?>
									<tr><td colspan="5" style="text-align:center; font-weight:bold;font-size:18px;"><?=__('No accounts available')?></td></tr>
								<?php else:?>
								<?php foreach ($accounts as $account):?>
								<tr>
									<td>
										<div class="btn-group btn-group-xs">
											<a class="btn btn-default btn-xs" href="/accounts/edit/<?=$account->id?>"><i class="fa fa-fw fa-pencil"></i></a>
											<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i>', ['action' => 'restore', $account->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to restore this account?')])?>
										</div>
									</td>
									<td><a href="/accounts/view/<?=$account->id?>"><?=h($account->title)?></a></td>
									<td class="hidden-sm hidden-xs"><?=h($account->account_type->title)?></td>
									<td class="hidden-sm hidden-xs" data-value="<?=$account->active?>"><?=$account->deleted_date != null ? 'Deleted' : 'Expired'?></td>
									<td class="hidden-sm hidden-xs" data-value="0"><em><?=$activities[$account->id]['lastTransaction']?></em></td>
								</tr>
								<?php endforeach; ?>
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
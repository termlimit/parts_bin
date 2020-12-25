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
						<h3 class="box-title">Locations</h3>
						<!-- ACTIONS MENU -->
						<div class="box-tools pull-right">
							<?php if ($search !== null):?>
							<span class="label label-primary">
								<a style="color:#FFFFFF;" title="Remove search for <?=h($search)?>" href="/locations">Search: <?=h($search)?>  <i class="fa fa-remove text-black"></i></a>
							</span>
							<?php endif;?>
							<div class="btn-group">
								<a href="/locations/add" class="btn btn-success btn-xs"><i class="fa fa-plus fa-fw"></i> Create location</a>
							</div>
						</div>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<thead>
								<tr>
									<th data-defaultsort="disabled">&nbsp;</th>
									<th>Name</th>
									<th>description</th>
								</tr>
							</thead>
							<tbody>
								<?php if($none):?>
									<tr><td colspan="6" style="text-align:center; font-weight:bold;font-size:18px;"><?=__('No locations available')?></td></tr>
								<?php else:?>
								<?php foreach ($locations as $location): ?>
								<tr>
									<td>
										<div class="btn-group btn-group-xs">
											<a class="btn btn-default btn-xs" href="/locations/edit/<?=$location->id?>"><i class="fa fa-fw fa-pencil"></i></a>
											<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i>', ['action' => 'delete', $location->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to delete {0}?', h($location->name))]) ?>
											<?=$this->Form->postLink('<i class="fa fa-fw fa-arrow-down"></i>', ['action' => 'moveDown', $location->id], ['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to move down {0}?', h($location->name))]) ?>
											<?=$this->Form->postLink('<i class="fa fa-fw fa-arrow-up"></i>', ['action' => 'moveUp', $location->id], ['class' => 'btn btn-info btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to move up {0}?', h($location->name))]) ?>
										</div>
									</td>
									<td><i class="hidden-xs fa fa-folder-open-o"></i> <a href="/locations/view/<?=$location->id?>"><?=h($location->name)?></a></td>
									<td><?=$location->description?></td>
								</tr>
									<?php if (count($location->children) > 0) : //if( is_array($location['children']) && count($location['children']) > 0) :?>
									<?php // column count is reset per column for counting above
										echo $this->cell('Location::row', [
											'children' => $location,
											'depth' => 1,
											'html' => $this->Html,
											'time' => $this->Time,
											'form' => $this->Form
										]);?>
									<?php endif;?>
								<?php endforeach;?>
								<?php endif;?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<?=$this->element('page-header');?>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li><a href="/<?=$what?>"><?=$what?></a></li>
			<li class="active"> <?=$subTitle?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<div class="row">
			<div class="col-lg-12 col-sm-12 col-md-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title"><?=$part->description?></h3>
						<!-- ACTIONS MENU -->
						<div class="box-tools pull-right">
							<div class="btn-group">
								<?=$this->Html->link('<i class="fa fa-fw fa-pencil"></i> Edit', ['controller' => 'parts', 'action' => 'edit', $part->id], ['class' => 'btn btn-success btn-xs', 'escape' => false, 'title' => 'Edit'])?>
							</div>
							<div class="btn-group">
								<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i> Delete part', ['action' => 'delete', $part->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to delete {0}?', h($part->description))]) ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->

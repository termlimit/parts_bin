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
		<!-- title row -->
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">
							Parts 
							<?php if ($search !== null):?>
							<span class="label label-primary">
								<a style="color:#FFFFFF;" title="Remove search for <?=h($search)?>" href="/parts">Search: <?=h($search)?>  <i class="fa fa-remove text-black"></i></a>
							</span>
							<?php endif;?>
							<div class="btn-group">
								<a href="/parts/add" class="btn btn-success btn-xs"><i class="fa fa-plus fa-fw"></i> Add a part</a>
							</div>
						</h3>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<?=$this->element('parts')?>
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->

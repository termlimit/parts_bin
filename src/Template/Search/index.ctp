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
						<h3 class="box-title">Search results</h3>
						<!-- ACTIONS MENU -->
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<?=pr($result)?>
		</div>
		<!-- /.row -->
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->

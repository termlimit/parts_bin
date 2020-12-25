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
						<?=$this->element('startup_menu', compact($menu))?>
					</div>
					<div class="box-body table-responsive">
						<h1><?=__d('installer', 'Thanks!')?></h1>
						<p><?=__d('installer', 'Thanks for choosing LoadedFinance.com, you can now begin using your account. Feel free to email us or check the user guide if you have questions. LoadedFinance.com, budget and get Loaded!')?></p>

						<hr />

						<p>
							<?=$this->Form->create('Startup', ['class' => 'pull-right'])?>
							<?=$this->Form->submit(__d('installer', 'Complete setup'), ['name' => 'dashboards', 'class' => 'btn btn-primary'])?>
							<?=$this->Form->end()?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>


<div class="row">
	<div class="col-md-12">
		<?=$this->Flash->render()?>

	</div>
</div>

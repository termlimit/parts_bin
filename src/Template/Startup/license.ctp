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
						<?php
							echo $this->Form->textarea('license', [
								'class' => 'form-control',
								'readonly',
								'rows' => 10,
								'value' => 'TEXT'
							]);
						?>
						<p>
							<?php
								echo $this->Html->link(__d('installer', 'I Agree'), [
								'controller' => 'startup',
								'action' => 'account'
								], [
								'class' => 'btn btn-primary pull-right'
								]);
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

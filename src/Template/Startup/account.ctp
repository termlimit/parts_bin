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
		<fieldset>
			<legend><?=__d('installer', 'Create Accounts')?></legend>
			<small><em><?=__d('installer', 'Complete the following information to quickly setup your accounts.')?></em></small>
			
			<hr />
			<?=$this->Flash->render(); ?>
			<?=$this->Form->create('Startup', [])?>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="box">
						<div class="box-header with-border">
							<?=$this->element('startup_menu', compact($menu))?>
						</div>
						<div class="box-body table-responsive">
							<div class="col-xs-3">
								<?=$this->Form->input('title', ['class' => 'form-control', 'label' => __d('installer', 'Title') . ' *'])?>
								<em><?=__d('installer', 'Account title, e.g. My Bank Checking.')?></em>
							</div>
							<div class="col-xs-3">
								<?=$this->Form->input('account_type_id', ['class' => 'form-control',
									'type' => 'select', 'options' => $this->Startup->findAccountTypesList(),
								'label' => __d('installer', 'Account Type') . ' *'])?>
								<em><?=__d('installer', 'Account type, e.g. Cash, Checking.')?></em>
							</div>
							<div class="col-xs-3">
								<?=$this->Form->input('balance', ['class' => 'form-control', 'label' => __d('installer', 'Balance') . ' *'])?>
								<em><?=__d('installer', 'Account balance, e.g. 145.34.')?></em>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-3 col-md-offset-9">
					<div class="box">
						<div class="box-header with-border">
							<h3 class="box-title"><?=__d('installer', 'Entry option')?></h3>
						</div>
						<div class="box-body">
							<?=$this->Form->input('skip', ['type' => 'checkbox', 'label' => __d('installer', 'Skip this step')])?>
							<?=$this->Form->input('return', ['type' => 'checkbox', 'label' => __d('installer', 'Add another account?')])?>
							<p><?=$this->Form->submit(__d('installer', 'Create Account'), ['class' => 'btn btn-primary pull-right'])?></p>
						</div>
					</div>
				</div>
			</div>
			<?=$this->Form->end()?>
		</fieldset>
	</section>
</div>
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
			<legend><?=__d('installer', 'Create Categories')?></legend>
			<small><em><?=__d('installer', 'Complete the following information to quickly setup your categories.')?></em></small>
			
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
								<em><?=__d('installer', 'Category title, e.g. Groceries.')?></em>
							</div>
							<div class="col-xs-3">
								<?=$this->Form->input('parent_id', ['class' => 'form-control',
									'type' => 'select', 'options' => $this->Startup->findCategoryTreeList(),
									'empty' => 'No parent category',
									'label' => __d('installer', 'Parent Category') . ' *'])?>
								<em><?=__d('installer', 'File within another category.')?></em>
							</div>
							<div class="col-xs-3">
								<?=$this->Form->input('profit', ['class' => 'form-control',
									'type' => 'select', 'options' => [0 => 'Expense', 1 => 'Income'],
												'label' => __d('installer', 'Profit') . ' *'])?>
								<em><?=__d('installer', 'Profit or loss, e.g. income is profit.')?></em>
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
							<?=$this->Form->input('return', ['type' => 'checkbox', 'label' => __d('installer', 'Add more categories?')])?>
							<p><?=$this->Form->submit(__d('installer', 'Create Category'), ['class' => 'btn btn-primary pull-right'])?></p>
						</div>
					</div>
				</div>
			</div>
			<?=$this->Form->end()?>
		</fieldset>
	</section>
</div>

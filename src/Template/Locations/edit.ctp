<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<?=$this->element('page-header');?>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li><a href="/<?=$what?>"><?=$what?></a></li>
			<li class="active"> <?=$subTitle . ': ' . $category->title?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<div class="row">
			<?=$this->Form->create($category, ['name' => 'add'])?>
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Category information</h3>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<tr>
								<td>Active</td>
								<td><?=$this->Form->input('active', ['type' => 'checkbox', 'label' => ''])?></td>
							</tr>
							<tr>
								<td>Category Type</td>
								<td><?=$this->Form->input('profit', ['type' => 'select', 'options' => [0 => 'Expense', 1 => 'Income'], 'label' => false])?></td>
							</tr>
							<tr>
								<td>Title</td>
								<td><?=$this->Form->input('title', ['label' => false])?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Optional information</h3>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<tr>
								<td>Description</td>
								<td><?=$this->Form->input('description', ['label' => false])?></td>
							</tr>
							<tr>
								<td>Parent</td>
								<td><?=$this->Form->input('parent_id', [
										'type' => 'select',
										'empty' => 'No parent category',
										'options' => $parents,
										'label' => false
									])?></td>
							</tr>
							<tr>
								<td>Expiration date</td>
								<td><?=$this->Form->input('expiration', ['type' => 'date', 'label' => false, 'empty' => true])?></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Entry option</h3>
					</div>
					<div class="box-footer">
						<button type="submit" id="account-btn" class="btn btn-success pull-right">
							Save <?=$what?> update
						</button>
					</div>
				</div>
			</div>
			<?=$this->Form->end()?>
		</div>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
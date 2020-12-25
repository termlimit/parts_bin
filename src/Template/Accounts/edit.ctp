<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<?=$this->element('page-header');?>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li><a href="/accounts/index/<?=$what?>"><?=$what?> accounts</a></li>
			<li class="active"> <?=$subTitle . ': ' . $account->title?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<div class="row">
			<?=$this->Form->create($account, ['name' => 'edit'])?>
			<?=$this->Form->hidden('type', ['value' => $what])?>
			<div class="col-lg-6 col-md-6 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Account information</h3>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<tr>
								<td>Active</td>
								<td><?=$this->Form->input('active', ['type' => 'checkbox', 'label' => ''])?></td>
							</tr>
							<tr>
								<td>Title</td>
								<td><?=$this->Form->input('title', ['label' => false])?></td>
							</tr>
							<tr>
								<td>Description</td>
								<td><?=$this->Form->input('description', ['label' => false])?></td>
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
								<td>Account type</td>
								<td><?=$this->Form->input('account_type.id', [
										'type' => 'select',
										'options' => $accountRoles,
										'label' => false
									])?></td>
							</tr>
							<tr>
								<td>Entered</td>
								<td><?=$this->Form->input('entered', ['type' => 'date', 'label' => false])?></td>
							</tr>
							<tr>
								<td>Opening balance</td>
								<td><?=$this->Form->input('balance', ['label' => false])?></td>
							</tr>
							<tr>
								<td>Expiration date</td>
								<td><?=$this->Form->input('expiration', ['type' => 'date', 'label' => false, 'empty' => true])?></td>
							</tr>
							<tr>
								<td>Currency</td>
								<td><?=$this->Form->input('currency', [
										'type' => 'select',
										'options' => $currencies,
										'label' => false
									])?></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Entry option</h3>
					</div>
					<div class="box-body table-responsive no-padding">
						<table class="table table-hover sortable">
							<tr>
								<td>Return to this form?</td>
								<td><?=$this->Form->input('return', ['type' => 'checkbox', 'label' => false])?></td>
							</tr>
						</table>
					</div>
					<div class="box-footer">
						<button type="submit" id="account-btn" class="btn btn-success pull-right">
							Update account
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
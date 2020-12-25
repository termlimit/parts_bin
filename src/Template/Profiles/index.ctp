<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<?=$this->element('page-header');?>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li class="active"> <?=$title?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<div class="row">
			<div class="col-lg-6 col-lg-offset-3 col-md-6 col-sm-12">
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Options</h3>
					</div>
					<div class="box-body">
						<ul>
							<li>
								<?=$this->Form->postLink(__('Delete account'), ['action' => 'delete', $user['id']], ['confirm' => __('Are you sure you want to delete your account? This action is not reversible'), 'class' => 'text-danger'])?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?=$this->Form->create('Profiles', ['url' => ['action' => 'change_password'], 'class' => 'form-horizontal', 'name' => 'change_password'])?>
		<div class="row">
			<div class="col-lg-6 col-lg-offset-3 col-md-6 col-sm-12">
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Change your password</h3>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="inputOldPassword" class="col-sm-4 control-label">Current password</label>
							<div class="col-sm-8">
								<input type="password" class="form-control" id="inputOldPassword" placeholder="Current password" name="current_password">
							</div>
						</div>

						<div class="form-group">
							<label for="inputNewPassword1" class="col-sm-4 control-label">New password</label>
							<div class="col-sm-8">
								<input type="password" class="form-control" id="inputNewPassword1" placeholder="New password" name="new_password">
							</div>
						</div>

						<div class="form-group">
							<label for="inputNewPassword2" class="col-sm-4 control-label">New password again</label>
							<div class="col-sm-8">
								<input type="password" class="form-control" id="inputNewPassword2" placeholder="New password again"
									   name="new_password_confirmation">
							</div>
						</div>
					</div>
					<div class="box-footer">
						<button type="submit" class="btn btn-success pull-right">Change your password</button>
					</div>
				</div>
			</div>
		</div>
		<?=$this->Form->end()?>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->

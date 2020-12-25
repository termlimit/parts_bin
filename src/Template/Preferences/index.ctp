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
		<?=$this->Flash->render('Auth'); ?>
		<?=$this->Form->create('Preferences', ['url' => ['action' => 'index'], 'class' => 'form-horizontal', 'name' => 'index'])?>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Newsletter and updates</h3>
					</div>
					<div class="box-body">
						<p class="text-info">Receive our newsletter and system updates</p>
						<?=$this->Form->input('email_announcements', ['type' => 'checkbox', 'checked' => 'checked', 'value' => '1', 'label' => 'Newsletter'])?>
						<?=$this->Form->input('system_updates', ['type' => 'checkbox', 'checked' => 'checked', 'value' => '1', 'label' => 'System updates'])?>
					</div>
				</div>
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Budget settings</h3>
					</div>
					<div class="box-body">
						<p class="text-info">
							Alert when a budget goes close to or over
						</p>
						<?=$this->Form->input('budget_alert_over', ['type' => 'checkbox', 'value' => 'true', 'label' => 'Over budget'])?>
						<?=$this->Form->input('budget_alert_warning', ['type' => 'checkbox', 'value' => 'true', 'label' => 'Warn budget'])?>
						<?=$this->Form->input('currency', ['type' => 'checkbox', 'value' => 'USD', 'label' => 'Currency (USD)'])?>
					</div>
				</div>
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Customized year start</h3>
					</div>
					<div class="box-body">
						<p class="text-info">
							Specify fiscal caledar year
						</p>
						<?=$this->Form->input('custom_fiscal_year', ['type' => 'checkbox', 'checked' => 'checked', 'value' => '1', 'label' => 'Custom fiscal year (2016-Jan-01)'])?>
					</div>
				</div>

			</div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Interactive data</h3>
					</div>
					<div class="box-body">
						<p class="text-info">System information</p>
						<?=$this->Form->input('user_walk_through', ['type' => 'checkbox', 'checked' => 'checked', 'value' => '1', 'label' => 'Start user walk through'])?>
						<?=$this->Form->input('sample_data', ['type' => 'checkbox', 'checked' => 'checked', 'value' => '1', 'label' => 'Install sample data'])?>
						<?=$this->Form->input('remove_sample_data', ['type' => 'checkbox', 'value' => '1', 'label' => 'Remove sample data'])?>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-6">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Language and timezone</h3>
					</div>
					<div class="box-body">
						<p class="text-info">Select preferred language and timezone</p>
						<?=$this->Form->input('locale', ['type' => 'checkbox', 'checked' => 'checked', 'value' => 'en_US', 'label' => 'English'])?>
						<?=$this->Form->input('time_zone', ['type' => 'checkbox', 'checked' => 'checked', 'value' => 'America/Los_Angeles', 'label' => 'America/Los_Angeles'])?>
						<?=$this->Form->input('date_format', ['type' => 'checkbox', 'checked' => 'checked', 'value' => 'YYYY-MMM-DD', 'label' => 'Year-Month-Day (2016-Jan-01)'])?>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="form-group">
					<div class="col-sm-12">
						<button type="submit" class="btn btn-success btn-lg">Save settings</button>
					</div>
				</div>
			</div>
		</div>
		<?=$this->Form->end()?>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->

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
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title"><?=$account->title?>
							<?php if ($account->expiration != null && $account->expiration < date('YYYY-mm-dd')):?>
							<span class="label label-danger"><i class="fa fa-asterisk text-white"></i> Account closed</span>
							<?php endif;?>
						</h3>
						<!-- ACTIONS MENU -->
						<div class="box-tools pull-right">
							<div class="btn-group">
								<a href="/accounts/edit/<?=$account->id?>" class="btn btn-success btn-xs"><i class="fa fa-fw fa-pencil"></i> Edit account</a>
							</div>
							<div class="btn-group">
								<?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i> Delete account', ['action' => 'delete', $account->id], ['class' => 'btn btn-danger btn-xs', 'escape' => false, 'confirm' => __('Are you sure you want to delete {0}?', h($account->title))]) ?>
							</div>
						</div>
					</div>
					<div class="box-body">
						<canvas id="overview-chart" style="width:100%;height:400px;"></canvas>
					</div>
				</div>
			</div>
		</div>
		<!-- actual content -->
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Transactions</h3>
						<div class="pull-right">
							<span class="text-success"><?=$this->Number->currency($periodBalance, $currency)?></span>
						</div>
					</div>
					<?=$this->element('transaction_list', ['journals' => $journals, 'periodBalance' => $periodBalance])?>
				</div>
			</div>
		</div>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $this->Html->scriptStart(['block' => 'chartTop']);
	echo "var accountID = " . $account->id . ";";
$this->Html->scriptEnd();?>
<?=$this->Html->script('Chart.min', ['block' => 'chartBottom'])?>
<?=$this->Html->script('charts', ['block' => 'chartBottom'])?>
<?=$this->Html->script('accounts', ['block' => 'chartBottom'])?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			<i class="hidden-xs fa fa-tasks"></i>
			<?=$what?>
		</h1>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li class="active"><?=$what?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render()?>
		<div class="row">
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Summary reports</h3>
					</div>
					<div class="box-body">
						<?php foreach ($months as $year => $month):?>
							<h4><?=$year?></h4>
							<ul class="list-inline">
								<li><?=$this->Html->link(__(h('Budgeted cash flow')), ['controller' => 'reports', 'action' => 'budget', $year])?></li>
								<li><?=$this->Html->link(__(h('Actual cash flow')), ['controller' => 'reports', 'action' => 'actual', $year])?></li>
							</ul>
						<?php endforeach;?>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Detailed Reports</h3>
					</div>
					<div class="box-body">
						<?php foreach ($months as $year => $month):?>
							<h4><a href="{{ route('reports.year',year) }}"><?=$year?></a></h4>
							<ul class="list-inline">
								<li><a href="{{ route('reports.month',[month.year, month.month]) }}">Net worth</a></li>
								<li><a href="{{ route('reports.month',[month.year, month.month]) }}">Average account balances</a></li>
								<li><a href="{{ route('reports.month',[month.year, month.month]) }}">Asset barchart</a></li>
								<li><a href="{{ route('reports.month',[month.year, month.month]) }}">Liability barchart</a></li>
								<li><a href="{{ route('reports.month',[month.year, month.month]) }}">Monthly cash flow</a></li>
								<li><a href="{{ route('reports.month',[month.year, month.month]) }}">Category spending report</a></li>
							</ul>
						<?php endforeach;?>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<?=$this->Html->script('reports', ['block' => 'chartBottom'])?>

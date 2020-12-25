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
			<li><a href="/dashboards/reports">Reports</a></li>
			<li class="active"><?=$subTitle?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render()?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Cash flow reports</h3>
					</div>
					<div class="box-body table-responsive table-condensed table-minified no-padding">
						<table class="table table-hover sortable table-striped">
							<thead>
								<tr>
									<th data-defaultsort="disabled">&nbsp;</th>
									<?php for($z = 1; $z <= 12; $z++):?>
									<?php $style = date('n', time() ) == $z ? ( date( 'Y', time() ) == $year ? 'current' : 'line-left') : 'line-left';?>
									<th class="table-header-repeat <?php echo $style;?>" style="width:100px" title="<?php echo date("F", mktime(0, 0, 0, $z, 1, $year));?>">
										<span><?=$this->Html->link(
										date("F", mktime(0, 0, 0, $z, 1, $year)),
										['controller' => 'transactions', 'action' => '?month='.sprintf("%02d", ($z)).'&year='.$year],
										['escape' => false]);?></span>
									</th>
									<?php endfor; ?>
									<th class="table-header-repeat line-left" style="width:150px"><span>Total</span></th>
								</tr>
							</thead>
							<tbody>
								<?=$this->cell('CashFlow::display', [
									'category' => $categories,
									'depth' => 0,
									'display' => 0,
									'showDepth' => 1,
									'parent' => 0,
									'year' => $year,
									'Time' => $this->Time,
									'Number' => $this->Number,
									'Html' => $this->Html,
									'Calculate' => $this->Calculate
								]);?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

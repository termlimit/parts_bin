<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1>
			<i class="hidden-xs fa fa-folder-open"></i>
			<?=$what?> <small id="subTitle"><i class="hidden-xs fa <?=$subTitleIcon?>"></i> <?=$subTitle?></small>
		</h1>
		<ol class="breadcrumb">
			<li class="active">Home</li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<?=$this->element('boxes')?>
		<div class="row">
			<div class="col-lg-8 col-md-12 col-sm-12">
				<!-- ACCOUNTS -->
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Net worth past 12 months - <?=$this->element('net_worth', ['deviation' => $deviation])?></h3>
						<div class="box-tools pull-right">
							<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="box-body">
						<canvas id="networth-chart" style="width:100%;height:400px;"></canvas>
					</div>
				</div>

				<!-- CATEGORIES -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Category budget differences</h3>
						<div class="box-tools pull-right">
							<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="box-body">
						<canvas id="categories-chart" style="width:100%;height:400px;"></canvas>
					</div>
				</div>

				<?php if (\Cake\Core\Configure::read('GlobalAuth.id') == 2):?>
				<!-- BUDGETS -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">{{ 'budgetsAndSpending'|_ }}</h3>
						<div class="box-tools pull-right">
							<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="box-body">
						<canvas id="budgets-chart" style="width:100%;height:400px;"></canvas>
					</div>
				</div>
				<?php endif;?>
			</div>

			<div class="col-lg-4 col-md-6 col-sm-12">
				<!-- RECURRING TRANSACTIONS -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Recurring Transactions</h3>
						<!-- ACTIONS MENU -->
						<div class="box-tools pull-right">
							<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="box-body table no-padding">
						<table class="table">
							<thead>
								<tr>
									<th>Date</th>
									<th>Title</th>
									<th>Amount</th>
								</tr>
							</thead>
							<tbody>
								<tr><td colspan="3"><h4>Paid this month:</h4></td></tr>
							<?php foreach ($billsPaid as $bill):?>
								<tr>
									<td><?=$bill->entered->format('M-d-Y')?></td>
									<td><?=$bill->_matchingData['Bills']->title?></td>
									<td>$<?=number_format($bill->transactions[0]->amount, 2)?>
								</tr>
							<?php endforeach;?>
								<tr><td colspan="3"><h4>UnPaid this month:</h4></td></tr>
							<?php foreach ($billsUnPaid as $bill):?>
								<tr>
									<td><?=$bill->nextExpectedMatch->format('M-d-Y')?></td>
									<td><?=$bill->title?></td>
									<td>$<?=number_format($bill->amount, 2)?>
								</tr>
							<?php endforeach;?>							
							</tbody>
						</table>
					</div>
				</div>
				<?php if (\Cake\Core\Configure::read('GlobalAuth.id') == 2):?>
				<!-- CATEGORIES -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Categories</h3>
                        <!-- ACTIONS MENU -->
                        <div class="box-tools pull-right">
                            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                    </div>
                    <div class="box-footer clearfix">
                        <a class="btn btn-sm btn-default btn-flat pull-right"
                           href="{{ route('accounts.show',data[1].id) }}">{{ (data[1]|balance)|formatAmountPlain }}</a>
                    </div>
                </div>
				<?php endif;?>
			</div>
		</div>
		<?php if (\Cake\Core\Configure::read('GlobalAuth.id') == 2):?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<!-- EXPENSE ACCOUNTS -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">{{ 'expense_accounts'|_ }}</h3>

						<div class="box-tools pull-right">
							<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="box-body">
						<canvas id="expense-accounts-chart" style="width:100%;height:400px;"></canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-6 col-sm-12 col-md-12">
				<!-- SAVINGS -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">{{ 'savings'|_ }}</h3>

						<div class="box-tools pull-right">
							<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="box-body">
						<p class="small"><em>{{ 'markAsSavingsToContinue'|_ }}</em></p>
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<h5><a href="{{ route('accounts.show', account.id) }}">{{ account.name }}</a></h5>
							</div>
						</div>
						<div class="row">
							<!-- start -->
							<div class="col-lg-2 col-md-2 col-sm-3 col-xs-4">{{ account.startBalance|formatAmount }}</div>
							<!-- bar -->
							<div class="col-lg-8 col-md-8 col-sm-6 col-xs-4">
									<!-- green (pct), then blue (100-pct) -->
									<div class="progress">
										<div class="progress-bar progress-bar-success" style="width: {{ account.percentage }}%">
											{% if account.percentage <= 50 %}
												{{ account.difference|formatAmountPlain }}
												{{ account.difference|formatAmountPlain }}
											{% endif %}
										</div>
										<div class="progress-bar progress-bar-info" style="width: {{ 100 - account.percentage }}%">
											{% if account.percentage > 50 %}
												{{ account.difference|formatAmountPlain }}
											{% endif %}
										</div>
									</div>
							</div>
							<!-- end -->
							<div class="col-lg-2 col-md-2 col-sm-3 col-xs-4">{{ account.endBalance|formatAmount }}</div>
						</div>
					</div>
					<div class="box-footer clearfix">
						<span class="pull-right">{{ 'sum'|_ }}: {{ savingsTotal|formatAmount }}</span>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-sm-12 col-md-12">
				<!-- PIGGY BANKS -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">{{ 'piggyBanks'|_ }}</h3>
						<div class="box-tools pull-right">
							<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
						</div>
					</div>
					<div class="box-body">
						<p class="small"><em>{{ 'createPiggyToContinue'|_ }}</em></p>
					</div>
				</div>
			</div>
		</div>
		<?php endif;?>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- show tour? -->
<script type="text/javascript">
	<?php if ($showTour == 'true'):?>
	var showTour = true;
	<?php else:?>
	var showTour = false;
	<?php endif;?>
</script>
<?=$this->Html->script('Chart', ['block' => 'chartBottom'])?>
<?=$this->Html->script('charts', ['block' => 'chartBottom'])?>
<?=$this->Html->script('index', ['block' => 'chartBottom'])?>

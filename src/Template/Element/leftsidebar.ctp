<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
	<!-- sidebar: style can be found in sidebar.less -->
	<section class="sidebar">
		<!-- search form -->
		<form action="/<?=strtolower($this->request->params['controller'])?>/<?=$this->request->params['action']?><?=strtolower($this->request->params['controller']) == 'transactions' ? '/all' : ''?>" method="post" name="search-form" class="sidebar-form">
			<input type="hidden" name="_method" value="PUT" />
			<div class="input-group">
				<?=$this->Form->input('search', ['label' => false, 'placeholder' => 'Search...', 'class' => 'form-control ui-widget'])?>
				<span class="input-group-btn">
					<button type='submit' name='submit' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
				</span>
			</div>
		</form>
		<!-- /.search form -->
		<!-- sidebar menu: : style can be found in sidebar.less -->
		<ul class="sidebar-menu">
			<!-- home / dashboard -->
			<li class="<?=$this->name == 'Dashboards' ? 'active ' : ''?>treeview">
				<a href="/dashboards"><i class="fa fa-dashboard fa-fw"></i>
					<span>Dashboard</span></a>
			</li>
			<!-- accounts -->
			<li class="<?=$this->name == 'Accounts' ? 'active ' : ''?>treeview" id="account-menu">
				<a href="#">
					<i class="fa fa-credit-card fa-fw"></i>
					<span>Accounts</span>
					<i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					<li class="<?=($this->name == 'Parts') ? 'active ' : ''?>">
						<a href="/accounts/index/asset">
						<i class="fa fa-long-arrow-right fa-fw"></i> Asset Accounts</a>
					</li>
					<li class="<?=($this->name == 'Accounts' && (isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'liability')) ? 'active ' : ''?>">
						<a href="/accounts/index/liability">
						<i class="fa fa-long-arrow-left fa-fw"></i> Liability Accounts</a>
					</li>
					<li class="<?=($this->name == 'Accounts' && ((isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'revenue'))) ? 'active ' : ''?>">
						<a href="/accounts/index/revenue">
						<i class="fa fa-long-arrow-right fa-fw"></i> Revenue Accounts</a>
					</li>
					<li class="<?=($this->name == 'Accounts' && ((isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'expense'))) ? 'active ' : ''?>">
						<a href="/accounts/index/expense">
						<i class="fa fa-long-arrow-left fa-fw"></i> Expense Accounts</a>
					</li>
					<li class="<?=($this->name == 'Accounts' && $this->request->params['action'] == 'closed') ? 'active ' : ''?>">
						<a href="/accounts/closed/all">
						<i class="fa fa-long-arrow-left fa-fw"></i> Closed Accounts</a>
					</li>
				</ul>
			</li>
			<!-- parts-->
			<li class="<?=($this->name == 'Parts') ? 'active ' : ''?>" id="part-menu">
				<a href="/parts">
					<i class="fa fa-tasks fa-fw"></i>
					<span>Parts</span>
				</a>
			</li>
			<!-- locations -->
			<li class="<?=($this->name == 'Locations') ? 'active ' : ''?>" id="location-menu">
				<a href="/locations">
					<i class="fa fa-bar-chart fa-fw"></i>
					<span>Locations</span>
				</a>
			</li>
			<!-- projects -->
			<li class="<?=($this->name == 'Projects') ? 'active ' : ''?>" id="project-menu">
				<a href="/projects">
					<i class="fa fa-line-chart fa-fw"></i>
					<span>Projects</span>
				</a>
			</li>
			<!-- options -->
			<li class="<?=$this->name == 'Options' ? 'active ' : ''?>treeview" id="transaction-menu">
				<a href="#">
					<i class="fa fa-repeat fa-fw"></i>
					<span>Transactions<span class="fa arrow"></span></span>
					<i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					<li class="<?=($this->name == 'Transactions' && (!isset($this->request->params['pass'][0]) OR $this->request->params['pass'][0] == 'all')) ? 'active ' : ''?>">
						<a href="/transactions/">
						<i class="fa fa-arrows-alt fa-fw"></i> All</a>
					</li>
					<li class="<?=($this->name == 'Transactions' && (isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'withdrawal')) ? 'active ' : ''?>">
						<a href="/transactions/index/withdrawal">
						<i class="fa fa-long-arrow-left fa-fw"></i> Expenses</a>
					</li>
					<li class="<?=($this->name == 'Transactions' && (isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'deposit')) ? 'active ' : ''?>">
						<a href="/transactions/index/deposit"><i
						class="fa fa-long-arrow-right fa-fw"></i> Revenue / income</a>
					</li>
					<li class="<?=($this->name == 'Transactions' && (isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'refunds')) ? 'active ' : ''?>">
						<a href="/transactions/index/refund"><i
						class="fa fa-long-arrow-right fa-fw"></i> Refunds</a>
					</li>
					<li class="<?=($this->name == 'Transactions' && (isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'transfer')) ? 'active ' : ''?>">
						<a href="/transactions/index/transfer">
						<i class="fa fa-fw fa-exchange"></i> Transfers</a>
					</li>
				</ul>
			</li>
			<!-- money management -->
			<li class="<?=($this->name == 'Bills' || $this->name == 'PiggyBanks' || $this->name == 'Loan') ? 'active ' : ''?>treeview" id="money-menu">
				<a href="#">
					<i class="fa fa-dollar fa-fw"></i>
					<span>Money management</span>
					<i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					<li class="<?=$this->name == 'PiggyBanks' ? 'active ' : ''?>">
						<a href="/piggy-banks">
						<i class="fa fa-bank fa-fw"></i> Piggy banks</a>
					</li>
					<li class="<?=$this->name == 'Bills' ? 'active ' : ''?>">
						<a href="/bills">
						<i class="fa fa-book fa-fw"></i> Recurring Transactions</a>
					</li>
					<li class="<?=$this->name == 'Loan' ? 'active ' : ''?>">
						<a href="/loan">
						<i class="fa fa-book fa-fw"></i> Loan Calculator</a>
					</li>
				</ul>
			</li>
			<!-- options and preferences -->
			<li class="<?=($this->name == 'Profiles' || $this->name == 'Preferences') ? 'active ' : ''?>treeview" id="option-menu">
				<a href="#">
					<i class="fa fa-gears fa-fw"></i>
					<span>Options</span>
					<i class="fa fa-angle-left pull-right"></i>
				</a>
				<ul class="treeview-menu">
					<li class="<?=$this->name == 'Profiles' ? 'active ' : ''?>">
						<a class="" href="/profiles"><i class="fa fa-user fa-fw"></i> Profile</a>
					</li>
					<li class="<?=$this->name == 'Preferences' ? 'active ' : ''?>">
						<a class="" href="/preferences"><i class="fa fa-gear fa-fw"></i> Preferences</a>
					</li>
				</ul>
			</li>
			<!-- other options -->
			<li>
				<a href="/logout">
					<i class="fa fa-sign-out fa-fw"></i>
					<span>Logout</span>
				</a>
			</li>
		</ul>
	</section>
<!-- /.sidebar -->
</aside>
<?php $this->Html->scriptStart(['block' => 'scriptBottom']);
	$name = in_array($this->name, ['Bills', 'Categories', 'Transactions']) ? $this->name : null;
	if ($name != null) {
		$method = 'get' . $this->name . 'List';
		$searchList = $this->Search->$method($_chartstart, $_chartend);
	} else {
		$searchList = [];
	}
	$searchObject = 'var searchParams = [';
	$num = count($searchList);
	$count = 0;
	foreach ($searchList as $key => $value) {
		$searchObject .= '"'.$value['title'].'"';
		$count++;
		if ($count < $num) {
			$searchObject .= ',';
		}
	}
	echo $searchObject .= '];';
$this->Html->scriptEnd();?>
<?=$this->Html->script('jquery.suggest', ['block' => 'scriptBottom'])?>
<?=$this->Html->script('search', ['block' => 'scriptBottom'])?>

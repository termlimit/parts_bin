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
		<?=$this->Flash->render(); ?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="box">
					<div class="box-header with-border">
						<?=$this->element('startup_menu', compact($menu))?>
					</div>
					<div class="box-body table-responsive">
						<ul class="nav nav-pills nav-stacked language">
							<?php foreach ($languages as $code => $link): ?>
							<li class="<?=$code == 'en_US' ? 'active' : 'locale-'.$code?>">
								<?=$this->Html->link($link['action'], $link['url'], ['title' => $link['action'], 'data-welcome' => $link['welcome']])?>
							</li>
							<?php endforeach;?>
						</ul>
						<ul class="nav nav-pills nav-stacked language">
							<li>
								<?=$this->Form->postLink('Skip, install default data', ['action' => 'finish', 1], ['escape' => true]) ?>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

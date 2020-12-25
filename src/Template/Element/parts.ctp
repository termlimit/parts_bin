<?php foreach($parts as $part):?>
	<div class="col-lg-4 col-sm-4 col-md-4">
		<div class="box">
			<div class="box-header with-border">
				<!-- ACTIONS MENU -->
				<div class="box-tools pull-right">
					<button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
					<div class="btn-group">
						<button class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown"><i class="fa fa-ellipsis-v"></i></button>
						<ul class="dropdown-menu" role="menu">
							<li><?=$this->Html->link('<i class="fa fa-fw fa-pencil"></i> Edit', ['controller' => 'parts', 'action' => 'edit', $part->id], ['class' => '', 'escape' => false, 'title' => 'Edit'])?></li>
							<li><?=$this->Form->postLink('<i class="fa fa-fw fa-trash-o"></i> Delete', ['controller' => 'parts', 'action' => 'delete', $part->id], ['class' => '', 'escape' => false, 'confirm' => __('Are you sure you want to delete?')]) ?></li>
							<li><a href="/parts/add"><i class="fa fa-plus fa-fw"></i> New part</a></li>
						</ul>
					</div>
				</div>
				<h2 class="page-header">
					<i class="fa fa-barcode"></i> <a href="/parts/view/<?=$part->id?>" title="<?=($part->description)?>"><?=h($part->description)?></a>
				</h2>
			</div>
			<div class="box-body invoice-info">
				<div class="col-sm-4 invoice-col">
					Part number
					<address>
						<strong><?=h($part->part_number)?></strong><br>
					</address>
				</div>
				<div class="col-sm-2 invoice-col">
					Packaging
					<address>
						<strong><?=h($part->packaging->name)?></strong><br>
					</address>
				</div>
				<div class="col-sm-4 invoice-col">
					Part type
					<address>
						<strong><?=h($part->part_type->name)?></strong><br>
					</address>
				</div>
				<div class="col-sm-2 invoice-col">
					Quantity
					<address>
						<strong><?=array_sum(array_column($part->part_purchases,'quantity'))?></strong><br>
					</address>
				</div>
				<div class="col-xs-12 table-responsive">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Location</th>
								<th>Project</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?=$this->Html->link(__(h($part->location->name)), ['controller' => 'locations', 'action' => 'view', $part->location->id])?></td>
								<td>None</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="col-xs-12">
				</div>
				<div class="box-footer">
					<div class="pull-left">
						Price: <strong><?=$this->Number->currency($part->price, 'USD')?></strong>
					</div>
					<div class="pull-right">
						TBD
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- /.col -->
<?php endforeach;?>
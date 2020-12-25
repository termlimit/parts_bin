<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<?=$this->element('page-header');?>
		<ol class="breadcrumb">
			<li><a href="/dashboards">Home</a></li>
			<li><a href="/parts">Parts</a></li>
			<li class="active"> <?=$subTitle?></li>
		</ol>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render(); ?>
		<?=$this->Form->create($part, ['url' => ['action' => 'store'], 'class' => 'form-horizontal', 'name' => 'store'])?>
		<div class="row">
			<div class="col-lg-6 col-md-12 col-sm-6">
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Mandatory fields - part data</h3>
					</div>
					<div class="box-body">
						<div class="form-group" id="part_number_holder">
							<label for="part_number" class="col-sm-4 control-label">Part Number</label>
							<div class="col-sm-8">
								<?=$this->Form->input('part_number', ['label' => false, 'placeholder' => 'Part Number', 'class' => 'form-control'])?>
							</div>
						</div>

						<div class="form-group" id="packaging_holder">
							<label for="packaging" class="col-sm-4 control-label">Packaging type</label>
							<div class="col-sm-8">
								<?=$this->Form->select('packaging.id', $packaging, ['label' => false, 'class' => 'form-control', 'empty' => true])?>
							</div>
						</div>

						<div class="form-group" id="description_holder">
							<label for="description" class="col-sm-4 control-label">Description</label>
							<div class="col-sm-8">
								<?=$this->Form->input('description', ['label' => false, 'placeholder' => 'Description', 'class' => 'form-control'])?>
							</div>
						</div>

						<div class="form-group" id="list_price_holder">
							<label for="transfer" class="col-sm-4 control-label">Price</label>
							<div class="col-sm-8">
								<?=$this->Form->input('price', ['placeholder' => '0.00', 'class' => 'form-control', 'required' => false, 'label' => false])?>
							</div>
						</div>
					</div>
				</div>
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Optional fields - part amplifying information</h3>
					</div>
					<div class="box-body">
						<div class="form-group" id="bin_location_holder">
							<label for="bin_location" class="col-sm-4 control-label">Location</label>
							<div class="col-sm-8">
								<?=$this->Form->select('location.id', $locations, ['label' => false, 'class' => 'form-control', 'empty' => true])?>
							</div>
						</div>

						<div class="form-group" id="link_holder">
							<label for="link" class="col-sm-4 control-label">Part URL</label>
							<div class="col-sm-8">
								<?=$this->Form->input('link', ['placeholder' => 'URL to purchase', 'class' => 'form-control', 'required' => false, 'label' => false])?>
							</div>
						</div>

						<div class="form-group" id="part_type_holder">
							<label for="part_type" class="col-sm-4 control-label">Type of part</label>
							<div class="col-sm-8">
								<?=$this->Form->select('part_type.id', $part_types, ['label' => false, 'class' => 'form-control', 'empty' => true])?>
							</div>
						</div>

						<div class="form-group" id="project_holder">
							<label for="project" class="col-sm-4 control-label">Project</label>
							<div class="col-sm-8">
								<?=$this->Form->input('project', ['placeholder' => 'Assign part to project', 'class' => 'form-control', 'required' => false, 'label' => false])?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6 col-md-12 col-sm-6">
				<!-- panel for options -->
				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Options</h3>
					</div>
					<div class="box-body">
						<div class="form-group">
							<label for="bill_return_to_form" class="col-sm-4 control-label">Return here</label>
							<div class="col-sm-8">
								<div class="radio">
									<label>
										<input id="bill_return_to_form" name="create_another" type="checkbox" value="1">
										After adding, return here to create another one.
									</label>
								</div>
							</div>
						</div>
					</div>
					<div class="box-footer">
						<button type="submit" class="btn pull-right btn-success">
							Add part
						</button>
					</div>
				</div>
			</div>
		</div>
		<?=$this->Form->end()?>
	</section>
	<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $this->Html->scriptStart(['block' => 'scriptBottom']);
	$categoryOptions = '<option></option>';
	foreach ($categoryList as $key => $value) {
		$categoryOptions .= '<option value="'.$key.'">'.$value.'</option>';
	}
	echo "var categoryOptions = '" . $categoryOptions . "';";
	echo "\n\n";

	$accountObject = 'var availableAccounts = [';
	$num = count($accountList);
	$count = 0;
	foreach ($accountList as $key => $value) {
		$accountObject .= '"'.$value.'"';
		$count++;
		if ($count < $num) {
			$accountObject .= ',';
		}
	}
	echo $accountObject .= '];';
$this->Html->scriptEnd();?>
<?php $this->Html->scriptStart(['block' => 'scriptBottom']);
	echo "var what = 'withdrawal';\n";
	echo "var piggiesLength = 5;\n";
	echo "var doSwitch = true;\n";

	// some titles and names:
	echo "var txt = [];\n";
	echo "txt['withdrawal'] = 'Withdrawal';\n";
	echo "txt['refund'] = 'Refund';\n";
	echo "txt['deposit'] = 'Deposit';\n";
	echo "txt['transfer'] = 'Transfer';\n";
$this->Html->scriptEnd();?>
<?=$this->Html->script('jquery.inputmask', ['block' => 'scriptBottom'])?>
<?=$this->Html->script('jquery.inputmask.date.extensions', ['block' => 'scriptBottom'])?>
<?=$this->Html->script('jquery.inputmask.extensions', ['block' => 'scriptBottom'])?>
<?=$this->Html->script('bills', ['block' => 'scriptBottom'])?>

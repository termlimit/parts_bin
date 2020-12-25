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
		<?=$this->Form->create($part, ['url' => ['action' => 'update'], 'class' => 'form-horizontal', 'name' => 'update'])?>
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
								<?=$this->Form->input('packaging', ['label' => false, 'placeholder' => 'Package type', 'class' => 'form-control'])?>
							</div>
						</div>

						<div class="form-group" id="description_holder">
							<label for="description" class="col-sm-4 control-label">Description</label>
							<div class="col-sm-8">
								<?=$this->Form->input('description', ['label' => false, 'placeholder' => 'Description', 'class' => 'form-control'])?>
							</div>
						</div>

						<div class="form-group" id="list_price_holder">
							<label for="transfer" class="col-sm-4 control-label">List price</label>
							<div class="col-sm-8">
								<?=$this->Form->input('list_price', ['placeholder' => '0.00', 'class' => 'form-control', 'required' => false, 'label' => false])?>
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
								<?=$this->Form->input('bin_location', ['placeholder' => 'Where part is located', 'class' => 'form-control', 'required' => false, 'label' => false])?>
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
								<?=$this->Form->input('part_type', ['placeholder' => 'Assembly', 'class' => 'form-control', 'required' => false, 'label' => false])?>
							</div>
						</div>

						<div class="form-group" id="project_holder">
							<label for="project" class="col-sm-4 control-label">Project</label>
							<div class="col-sm-8">
								<?=$this->Form->input('project', ['placeholder' => 'Assign part to project', 'class' => 'form-control', 'required' => false, 'label' => false])?>
							</div>
						</div>

						<div class="form-group" id="current_price_holder">
							<label for="transfer" class="col-sm-4 control-label">Current price</label>
							<div class="col-sm-8">
								<?=$this->Form->input('current_price', ['placeholder' => '0.00', 'class' => 'form-control', 'required' => false, 'label' => false])?>
							</div>
						</div>

						<div class="form-group" id="price_date_holder">
							<label for="price_date" class="col-sm-4 control-label">Price date</label>
							<div class="col-sm-8">
								<div class="input-group">
									<div class="input-group-addon">
										<i class="fa fa-calendar"></i>
									</div>
									<input class="form-control" name="price_date" data-inputmask="'alias': 'yyyy/mm/dd'" data-mask="" type="text">
								</div>
								<!-- /.input group -->
							</div>
						</div>
						<div class="form-group" id="quantity_holder">
							<label for="quantity" class="col-sm-4 control-label">Quantity</label>
							<div class="col-sm-8">
								<?=$this->Form->input('quantity', ['label' => false, 'placeholder' => 'Quantity on hand', 'class' => 'form-control ui-widget'])?>
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
					<div class="box-footer">
						<button type="submit" id="bill-btn" class="btn pull-right btn-success">
							Update recurring transaction
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

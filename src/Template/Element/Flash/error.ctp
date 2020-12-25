<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<p>
			<div class="alert alert-danger alert-dismissible">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<h4><i class="icon fa fa-ban"></i> Error!</h4>
				<?=h($message)?>
				<?php if (isset($errors)) { ?>
					<p>
					<?php
						foreach($errors as $error => $value) {
							echo $error . ': ';
							foreach ($value as $key => $value) { echo $value . ' | '; }
							echo '<br/>';
						}
					?>
					</p>
				<?php } ?>
			</div>
		</p>
	</div>
</div>
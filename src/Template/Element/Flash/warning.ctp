<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<p>
			<div class="alert alert-warning alert-dismissible">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
				<h4><i class="icon fa fa-warning"></i> Warning!</h4>
				<?=h($message)?>
			<?php
			if (isset($errors)) {
				echo '<pre>';
				foreach($errors as $error => $value) {
					echo $error . ': ';
					foreach ($value as $key => $value) { echo $value . ' | '; }
					echo '<br/>';
				}
				echo '</pre>';
			}
			?>
			</div>
		</p>
	</div>
</div>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<h1></h1>
	</section>
	<!-- Main content -->
	<section class="content">
		<?=$this->Flash->render('auth')?>
		<?=$this->Flash->render()?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<div class="box">
					<?=$this->Form->create('Users', ['url' => ['action' => 'login'], 'name' => 'loginform', 'class' => 'form-signin', 'inputDefaults' => ['label' => false, 'div' => false]])?>
					<h2 class="form-signin-heading">Please sign in</h2>
					<label for="inputUsername" class="sr-only">Username</label>
					<input type="text" id="inputUsername" name="username" class="form-control" placeholder="Username required" required autofocus>
					<label for="inputPassword" class="sr-only">Password</label>
					<input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
					<div class="checkbox">
						<label><?=$this->Form->checkbox('remember_me', ['label' => 'false'])?> Remember me</label>
					</div>
					<button class="btn btn-lg btn-primary btn-block" name="data[User][submit]" id="UserSubmit" type="submit">Sign in</button>
					<div class="links">
						<a href="/users/forgot" class="forgot-pwd">Forgot Password?</a> | <a href="/users/add" class="register">Not Registered?</a>
					</div>
					<?php echo $this->Form->end(); ?>
				</div>
			</div>
		
	</section>
</div> <!-- /container-wrapper -->
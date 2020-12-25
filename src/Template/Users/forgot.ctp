<div class="container">
  <p><?=$this->Flash->render('auth'); ?></p>
  <p><?=$this->Flash->render(); ?></p>

  <?=$this->Form->create('User', ['url' => ['action' => 'login'], 'name' => 'loginform', 'class' => 'form-signin', 'inputDefaults' => ['label' => false, 'div' => false]])?>
	<h2 class="form-signin-heading">Enter username</h2>
	<label for="forgotUsername" class="sr-only">Username</label>
	<input type="text" id="forgotUsername" name="username" class="form-control last" placeholder="Username required" required autofocus>
	<button class="btn btn-lg btn-primary btn-block" name="data[User][submit]" id="UserSubmit" type="submit">Sign in</button>
	<div class="links">
	  <a href="/users/login">Already registered?</a> | <a href="/users/add" class="register">Not Registered?</a>
	</a>
  <?php echo $this->Form->end(); ?>

</div> <!-- /container -->
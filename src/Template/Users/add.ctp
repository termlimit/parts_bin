<div class="container">
  <p><?=$this->Flash->render('auth'); ?></p>
  <p><?=$this->Flash->render(); ?></p>

  <?=$this->Form->create('User', ['url' => ['action' => 'add'], 'name' => 'loginform', 'class' => 'form-signin', 'inputDefaults' => ['label' => false, 'div' => false]])?>
	<h2 class="form-signin-heading">Please register</h2>
	<label for="inputUsername" class="sr-only">Username</label>
	<input type="text" id="inputUsername" name="username" class="form-control" placeholder="Username required" required autofocus>
	<label for="inputEmail" class="sr-only">Email</label>
	<input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address required" required>
	<label for="inputPassword" class="sr-only">Password</label>
	<input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
	<button class="btn btn-lg btn-primary btn-block" name="data[User][submit]" id="UserSubmit" type="submit">Register</button>
	<div class="links">
	  <a href="/users/forgot" class="forgot-pwd">Forgot Password?</a> | <a href="/users/login" class="register">Already Registered?</a>
	</a>
  <?php echo $this->Form->end(); ?>

</div> <!-- /container -->
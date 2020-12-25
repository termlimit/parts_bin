<div id="wrapper">
	<div id="title">
		<h1>Sign up only takes a minute!</h1>
	</div>
	<div id="featured">
		<!--  start login-msg -->
		<div id="login-msg"><?php echo $session->flash(''); ?></div>
		<!--  end login-msg -->
		<!--  start login-inner -->
		<div id="signup">
			<?php echo $this->Form->create( 'User', array('action' => 'register', 'name' => 'loginform', 'id' => 'UserRegisterForm', 'inputDefaults' => array('label' => false, 'div' => false)) ); ?>
				<div style="display:none;">
					<input type="hidden" value="POST" name="_method">
				</div>
				<table cellspacing="0" cellpadding="0" border="0">
					<tbody>
						<tr>
							<th>Username</th>
							<td><?php echo $this->Form->input( 'username', array('class' => 'login-inp') ); ?></td>
						</tr>
						<tr>
							<th>Email</th>
							<td><?php echo $this->Form->input( 'email', array('class' => 'login-inp') ); ?></td>
						</tr>
						<tr>
							<th>Password</th>
							<td><?php echo $this->Form->input( 'password', array('class' => 'login-inp') ); ?></td>
						</tr>
						<tr>
							<td colspan="2"><input type="submit" style="cursor:pointer" name="data[User][submit]" id="UserSubmit" value="Signup"></td>
						</tr>
						<tr>
							<td colspan="2"><a href="/users/forgot">Forgot Password?</a> | <a href="/users/login">Login</a></td>
						</tr>
					</tbody>
				</table>
			<?php echo $this->Form->end(); ?>
		</div>
		<!--  end login-inner -->
		<div class="clear"></div>
	</div>
	<div style="clear:both"></div>
</div>
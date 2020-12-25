<!-- File: /app/views/themed/live/users/edit.ctp -->
<!-- Wrapper-->
<div id="wrapper-wide">
	<!-- Content-->
	<div id="content-wide">
		<!-- Box-->
		<div id="box" style="margin-bottom:10px;" >
			<h3>Edit User</h3>
			<?php echo $session->flash(); ?>
			<!-- start id-form -->
			<?php echo $this->Form->create( 'User', array('url' => ['action' => 'login'], 'inputDefaults' => array('label' => false, 'div' => false)) ); ?>
				<table width="100%">
					<tbody>
						<tr>
							<th valign="top">Current Password:</th>
							<td><?php echo $this->Form->input( 'password', array('class' => isset($errors['password']) ? 'inp-form-error' : 'inp-form', 'error' => false) ); ?></td>
							<td>
								<?php if( isset($errors['password']) ): ?>
									<div class="error-left"></div>
									<div class="error-inner"><?php echo $errors['password']; ?></div>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th valign="top" style="width:115px">User name:</th>
							<td><?php echo $this->data['User']['username']; ?></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<th valign="top">Email:</th>
							<td><?php echo $this->Form->input( 'email', array('class' => isset($errors['email']) ? 'inp-form-error' : 'inp-form', 'error' => false) ); ?></td>
							<td>
								<?php if( isset($errors['email']) ): ?>
									<div class="error-left"></div>
									<div class="error-inner"><?php echo $errors['email']; ?></div>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th valign="top">New Password:</th>
							<td><?php echo $this->Form->input( 'npassword', array('class' => isset($errors['npassword']) ? 'inp-form-error' : 'inp-form', 'error' => false) ); ?></td>
							<td>
								<?php if( isset($errors['npassword']) ): ?>
									<div class="error-left"></div>
									<div class="error-inner"><?php echo $errors['npassword']; ?></div>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th valign="top">Confirm Password:</th>
							<td><?php echo $this->Form->input( 'cpassword', array('class' => isset($errors['cpassword']) ? 'inp-form-error' : 'inp-form', 'error' => false) ); ?></td>
							<td>
								<?php if( isset($errors['cpassword']) ): ?>
									<div class="error-left"></div>
									<div class="error-inner"><?php echo $errors['cpassword']; ?></div>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th>&nbsp;</th>
							<td valign="top">
								<?php echo $this->Form->button(
									$this->Html->image('icons/save.png', array('alt' => 'save')
									) . 'Update', array('class' => 'button', 'id' => 'LedgerButton')
								);?>
								<?php echo $this->Form->button('Reset', array('class' => 'button', 'id' => 'LedgerButton', 'type' => 'reset'));?>
								<?php echo $this->Form->button(
									'Cancel', array('class' => 'button', 'id' => 'LedgerButton', 'type' => 'button', 'onClick' => 'location.href=\''.$_SERVER['HTTP_REFERER'].'\'')
								);?>
							</td>
							<td></td>
						</tr>
					</tbody>
				</table>
			<?php echo $this->Form->end(); ?>
			<!-- end id-form  -->
		</div>
		<!-- /Box-->
		</div>
		<!-- /Box-->
	</div>
	<!-- /Content-wide-->
</div>
<!-- /Wrapper-->
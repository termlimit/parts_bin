<?=sprintf('Hello %s', $user->username)?>,

<?php
	$url = $this->Url->build([
        'controller' => 'Users',
        'action' => 'reset',
        $user->username,
        $user->activation_key,
	]);
    echo sprintf('Please visit this link to reset your password: %s', $url);
?>

If you did not request a password reset, then please ignore this email.

<?=sprintf('IP Address: %s', $_SERVER['REMOTE_ADDR'])?>
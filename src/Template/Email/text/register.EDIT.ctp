<?php echo sprintf(__('Hello %s', true), $user['User']['username']); ?>,

<?php
    $url = Router::url(array(
        'controller' => 'users',
        'action' => 'activate',
        $user['User']['username'],
        $user['User']['activation_key'],
    ), true);
    echo sprintf(__('Please visit this link to activate your account: %s', true), $url);
?>
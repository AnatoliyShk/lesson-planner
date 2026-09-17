<p>Hi <?= h($name) ?>,</p>

<p>Someone asked to reset the password for your account. If that was you,
    click below within <?= h($ttlMinutes) ?> minutes:</p>

<p><?= $this->Html->link('Reset my password', $url) ?></p>

<p>If it wasn't you, ignore this email — your password has not changed.</p>

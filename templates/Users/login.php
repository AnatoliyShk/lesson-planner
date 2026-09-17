<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Log in');
?>
<section class="auth">
    <div class="section__title">
        <span class="index">01</span>
        <h1 class="auth__title">Log in</h1>
    </div>

    <?= $this->Form->create(null, ['class' => 'form-swiss']) ?>
        <?= $this->Form->control('email', [
            'type' => 'email',
            'required' => true,
            'autocomplete' => 'username',
            'autofocus' => true,
        ]) ?>
        <?= $this->Form->control('password', [
            'type' => 'password',
            'required' => true,
            'autocomplete' => 'current-password',
        ]) ?>
        <div class="form-swiss__actions">
            <?= $this->Form->button('Log in', ['class' => 'button-swiss']) ?>
        </div>
    <?= $this->Form->end() ?>

    <p class="auth__alt">
        No account yet?
        <?= $this->Html->link('Register', ['_name' => 'register']) ?>
    </p>
</section>

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Register');
?>
<section class="auth">
    <div class="section__title">
        <span class="index">02</span>
        <h1 class="auth__title">Register</h1>
    </div>

    <?= $this->Form->create($user, ['class' => 'form-swiss']) ?>
        <?= $this->Form->control('name', [
            'required' => true,
            'autocomplete' => 'name',
            'autofocus' => true,
        ]) ?>
        <?= $this->Form->control('email', [
            'type' => 'email',
            'required' => true,
            'autocomplete' => 'email',
        ]) ?>
        <?= $this->Form->control('password', [
            'type' => 'password',
            'required' => true,
            'autocomplete' => 'new-password',
        ]) ?>
        <?= $this->Form->control('password_confirm', [
            'type' => 'password',
            'label' => 'Confirm password',
            'required' => true,
            'autocomplete' => 'new-password',
        ]) ?>
        <div class="form-swiss__actions">
            <?= $this->Form->button('Create account', ['class' => 'button-swiss']) ?>
        </div>
    <?= $this->Form->end() ?>

    <p class="auth__alt">
        Already have an account?
        <?= $this->Html->link('Log in', ['_name' => 'login']) ?>
    </p>
</section>

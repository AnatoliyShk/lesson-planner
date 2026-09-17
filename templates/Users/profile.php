<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'Profile');
?>
<section class="section">
    <div class="section__title">
        <h2>Profile</h2>
    </div>

    <div class="grid">
        <div class="col-6">
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
                <div class="form-swiss__actions">
                    <?= $this->Form->button('Save changes', ['class' => 'button-swiss']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="section__title">
        <h2>Account</h2>
    </div>
    <ol class="swiss-list">
        <li class="swiss-list__item">
            <span class="swiss-list__index"></span>
            <span class="swiss-list__title">Role</span>
            <span class="swiss-list__meta"><?= h(ucfirst($user->role)) ?></span>
        </li>
        <li class="swiss-list__item">
            <span class="swiss-list__index"></span>
            <span class="swiss-list__title">Member since</span>
            <span class="swiss-list__meta"><?= $user->created ? h($user->created->format('d M Y')) : '—' ?></span>
        </li>
        <li class="swiss-list__item">
            <span class="swiss-list__index"></span>
            <span class="swiss-list__title">Last login</span>
            <span class="swiss-list__meta"><?= $user->last_login ? h($user->last_login->format('d M Y, H:i')) : __('Never') ?></span>
        </li>
    </ol>
</section>

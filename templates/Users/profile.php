<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var iterable<\App\Model\Entity\Lesson> $upcomingLessons
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

<section class="section">
    <div class="section__title">
        <h2>Upcoming lessons</h2>
    </div>
    <?php if ($upcomingLessons->isEmpty()) : ?>
        <p class="empty-state">
            No upcoming lessons.
            <?= $this->Html->link('Reserve one with a teacher', ['controller' => 'Home', 'action' => 'index']) ?>.
        </p>
    <?php else : ?>
        <ol class="swiss-list">
            <?php foreach ($upcomingLessons as $i => $lesson) : ?>
                <li class="swiss-list__item">
                    <span class="swiss-list__index"><?= sprintf('%02d', $i + 1) ?></span>
                    <span class="swiss-list__title">
                        <?= $this->Html->link($lesson->title, ['controller' => 'Lessons', 'action' => 'view', $lesson->id]) ?>
                    </span>
                    <span class="swiss-list__meta">
                        <?= h($lesson->start_time->i18nFormat('d MMM yyyy, HH:mm')) ?>
                        <?php if ($lesson->teacher?->user) : ?>
                            · <?= h($lesson->teacher->user->name) ?>
                        <?php endif; ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

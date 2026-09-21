<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Lesson $lesson
 */
$this->assign('title', $lesson->title);

$teacherName = $lesson->teacher?->user?->name;
$sameDay = $lesson->start_time->format('Y-m-d') === $lesson->end_time->format('Y-m-d');

$minutes = (int)$lesson->start_time->diffInMinutes($lesson->end_time);
$duration = trim(
    (intdiv($minutes, 60) ? intdiv($minutes, 60) . ' h ' : '')
    . ($minutes % 60 ? $minutes % 60 . ' min' : '')
) ?: '0 min';
?>
<section class="hero lesson-hero">
    <span class="hero__eyebrow"><?= __('Lesson #{0}', $this->Number->format($lesson->id)) ?></span>
    <h1><?= h($lesson->title) ?></h1>
    <?php if ($teacherName) : ?>
        <p class="hero__lede">
            <?= __('Taught by') ?>
            <?= $this->Html->link($teacherName, ['controller' => 'Teachers', 'action' => 'view', $lesson->teacher->id]) ?>
        </p>
    <?php endif; ?>

    <div class="lesson-actions">
        <?= $this->Html->link(__('Edit lesson'), ['action' => 'edit', $lesson->id], ['class' => 'button-swiss']) ?>
        <?= $this->Html->link(__('All lessons'), ['action' => 'index'], ['class' => 'button-swiss button-swiss--ghost']) ?>
        <?= $this->Html->link(__('New lesson'), ['action' => 'add'], ['class' => 'button-swiss button-swiss--ghost']) ?>
        <?= $this->Form->postLink(
            __('Delete'),
            ['action' => 'delete', $lesson->id],
            [
                'confirm' => __('Are you sure you want to delete # {0}?', $lesson->id),
                'class' => 'button-swiss button-swiss--danger lesson-actions__delete',
            ],
        ) ?>
    </div>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">01</span>
        <h2><?= __('When') ?></h2>
    </div>

    <div class="facts">
        <div class="fact fact--wide">
            <span class="fact__label"><?= __('Date') ?></span>
            <span class="fact__value"><?= h($lesson->start_time->i18nFormat('EEEE, d MMMM yyyy')) ?></span>
        </div>
        <div class="fact">
            <span class="fact__label"><?= __('Starts') ?></span>
            <span class="fact__value"><?= h($lesson->start_time->i18nFormat('HH:mm')) ?></span>
        </div>
        <div class="fact">
            <span class="fact__label"><?= __('Ends') ?></span>
            <span class="fact__value">
                <?= h($lesson->end_time->i18nFormat($sameDay ? 'HH:mm' : 'd MMM, HH:mm')) ?>
            </span>
        </div>
        <div class="fact">
            <span class="fact__label"><?= __('Duration') ?></span>
            <span class="fact__value fact__value--accent"><?= h($duration) ?></span>
        </div>
    </div>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">02</span>
        <h2><?= __('Details') ?></h2>
    </div>

    <dl class="detail-list">
        <dt><?= __('Teacher') ?></dt>
        <dd>
            <?= $lesson->hasValue('teacher')
                ? $this->Html->link($teacherName ?? __('Teacher #{0}', $lesson->teacher->id), ['controller' => 'Teachers', 'action' => 'view', $lesson->teacher->id])
                : '—' ?>
        </dd>

        <dt><?= __('Students') ?></dt>
        <dd>
            <?php if ($lesson->students) : ?>
                <?= h(implode(', ', array_map(fn($student) => $student->name, $lesson->students))) ?>
            <?php else : ?>
                <span class="detail-list__muted"><?= __('No students yet') ?></span>
            <?php endif; ?>
        </dd>

        <dt><?= __('Status') ?></dt>
        <dd>
            <?php if ($lesson->status) : ?>
                <span class="status-badge"><?= h($lesson->status) ?></span>
            <?php else : ?>
                <span class="detail-list__muted"><?= __('Not set') ?></span>
            <?php endif; ?>
        </dd>

        <dt><?= __('Course') ?></dt>
        <dd>#<?= $this->Number->format($lesson->course_id) ?></dd>

        <dt><?= __('Created') ?></dt>
        <dd><?= h($lesson->created?->i18nFormat('d MMM yyyy, HH:mm')) ?></dd>

        <dt><?= __('Last modified') ?></dt>
        <dd><?= h($lesson->modified?->i18nFormat('d MMM yyyy, HH:mm')) ?></dd>
    </dl>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">03</span>
        <h2><?= __('Notes') ?></h2>
    </div>

    <?php if (trim((string)$lesson->description) !== '') : ?>
        <div class="lesson-notes">
            <?= $this->Text->autoParagraph(h($lesson->description)) ?>
        </div>
    <?php else : ?>
        <p class="empty-state"><?= __('No notes for this lesson.') ?></p>
    <?php endif; ?>
</section>

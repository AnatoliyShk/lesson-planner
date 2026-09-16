<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Lesson> $recent
 */
$this->assign('title', 'Lesson Planner');
?>
<section class="hero">
    <span class="hero__eyebrow">Schedule &middot; Teach &middot; Track</span>
    <h1>Lesson<br>Planner</h1>
    <p class="hero__lede">
        A clear, no-frills system for scheduling lessons, assigning teachers,
        and keeping every session accounted for.
    </p>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">01</span>
        <h2>Recent lessons</h2>
    </div>

    <?php if ($recent->isEmpty()): ?>
        <p class="empty-state">No lessons scheduled yet.</p>
    <?php else: ?>
        <ol class="swiss-list">
            <?php foreach ($recent as $i => $lesson): ?>
                <li class="swiss-list__item">
                    <span class="swiss-list__index"><?= sprintf('%02d', $i + 1) ?></span>
                    <span>
                        <?= $this->Html->link(
                            h($lesson->title),
                            ['controller' => 'Lessons', 'action' => 'view', $lesson->id],
                            ['class' => 'swiss-list__title']
                        ) ?>
                        <?php if ($lesson->teacher && $lesson->teacher->user): ?>
                            <div class="swiss-list__meta">
                                <?= h($lesson->teacher->user->first_name . ' ' . $lesson->teacher->user->last_name) ?>
                            </div>
                        <?php endif; ?>
                    </span>
                    <span class="swiss-list__meta">
                        <?= $lesson->start_time?->format('d M Y, H:i') ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">02</span>
        <h2>Manage</h2>
    </div>
    <div class="grid">
        <div class="col-4">
            <?= $this->Html->link('Lessons →', ['controller' => 'Lessons', 'action' => 'index'], ['class' => 'button-swiss']) ?>
        </div>
        <div class="col-4">
            <?= $this->Html->link('Teachers →', ['controller' => 'Teachers', 'action' => 'index'], ['class' => 'button-swiss']) ?>
        </div>
        <div class="col-4">
            <?= $this->Html->link('Users →', ['controller' => 'Users', 'action' => 'index'], ['class' => 'button-swiss']) ?>
        </div>
    </div>
</section>

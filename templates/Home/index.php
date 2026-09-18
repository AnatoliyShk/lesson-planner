<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Lesson> $recent
 * @var iterable<\App\Model\Entity\Teacher> $teachers
 * @var array<array<string, string>> $calendarEvents
 * @var bool $loggedIn
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
        <h2>My recent lessons</h2>
    </div>

    <?php if (!$loggedIn) : ?>
        <p class="empty-state">
            <?= $this->Html->link('Log in', ['_name' => 'login']) ?> to see your lessons.
        </p>
    <?php elseif ($recent->isEmpty()) : ?>
        <p class="empty-state">You have no lessons yet. Reserve time with a teacher below.</p>
    <?php else : ?>
        <ol class="swiss-list">
            <?php foreach ($recent as $i => $lesson) : ?>
                <li class="swiss-list__item">
                    <span class="swiss-list__index"><?= sprintf('%02d', $i + 1) ?></span>
                    <span>
                        <?= $this->Html->link(
                            h($lesson->title),
                            ['controller' => 'Lessons', 'action' => 'view', $lesson->id],
                            ['class' => 'swiss-list__title'],
                        ) ?>
                        <?php if ($lesson->teacher && $lesson->teacher->user) : ?>
                            <div class="swiss-list__meta">
                                <?= h($lesson->teacher->user->name) ?>
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
        <h2>Teachers</h2>
    </div>

    <?php if ($teachers->isEmpty()) : ?>
        <p class="empty-state">No teachers yet.</p>
    <?php else : ?>
        <ol class="swiss-list">
            <?php foreach ($teachers as $i => $teacher) : ?>
                <li class="swiss-list__item">
                    <span class="swiss-list__index"><?= sprintf('%02d', $i + 1) ?></span>
                    <span>
                        <span class="swiss-list__title"><?= h($teacher->user->name) ?></span>
                        <?php if ($teacher->bio) : ?>
                            <div class="swiss-list__meta"><?= h($teacher->bio) ?></div>
                        <?php endif; ?>
                    </span>
                    <span class="swiss-list__meta">
                        <?= h($teacher->user->email) ?>
                        <?= $this->Html->link(
                            'Reserve time',
                            ['controller' => 'Teachers', 'action' => 'reserve', $teacher->id],
                            ['class' => 'button-swiss button-swiss--sm'],
                        ) ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">03</span>
        <h2>My schedule</h2>
    </div>

    <?php if ($loggedIn) : ?>
        <div id="calendar"></div>
    <?php else : ?>
        <p class="empty-state">
            <?= $this->Html->link('Log in', ['_name' => 'login']) ?> to see your schedule.
        </p>
    <?php endif; ?>
</section>

<?php $this->start('script'); ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            return;
        }
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek',
            },
            height: 'auto',
            events: <?= json_encode($calendarEvents) ?>,
        });
        calendar.render();
    });
</script>
<?php $this->end(); ?>

<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Lesson> $recent
 * @var array<array<string, string>> $calendarEvents
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
        <h2>Schedule</h2>
    </div>

    <div id="calendar"></div>
</section>

<?php $this->start('script'); ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
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

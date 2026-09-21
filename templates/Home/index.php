<?php
/**
 * @var \App\View\AppView $this
 * @var bool $loggedIn
 * @var iterable<\App\Model\Entity\Teacher> $teachers
 * @var iterable<\App\Model\Entity\Lesson>|null $recent Only set when logged in.
 * @var array<array<string, string>>|null $calendarEvents Only set when logged in.
 * @var \App\Model\Entity\User|null $user Only set when logged in.
 */
$this->assign('title', 'Lesson Planner');

// Sections are numbered in the order they appear; guests see fewer of them.
$section = 0;
$nextIndex = function () use (&$section): string {
    return sprintf('%02d', ++$section);
};
?>
<?php if ($loggedIn) : ?>
    <?php // Logged in: the user's own area (name, profile, schedule) replaces the intro. ?>
<section class="hero hero--profile">
    <div class="hero__profile">
        <div>
            <span class="hero__eyebrow">Your schedule</span>
            <h1><?= h($user->name) ?></h1>
        </div>
        <?= $this->Html->link('Profile', ['_name' => 'profile'], ['class' => 'button-swiss button-swiss--outline']) ?>
    </div>

    <div id="calendar"></div>
</section>
<?php else : ?>
<section class="hero">
    <span class="hero__eyebrow">Schedule &middot; Teach &middot; Track</span>
    <h1>Lesson<br>Planner</h1>
    <p class="hero__lede">
        A clear, no-frills system for scheduling lessons, assigning teachers,
        and keeping every session accounted for.
    </p>
    <div class="hero__actions">
        <?= $this->Html->link('Log in', ['_name' => 'login'], ['class' => 'button-swiss']) ?>
        <?= $this->Html->link('Register', ['_name' => 'register'], ['class' => 'button-swiss button-swiss--outline']) ?>
    </div>
</section>
<?php endif; ?>

<?php if ($loggedIn) : ?>
<section class="section">
    <div class="section__title">
        <span class="index"><?= $nextIndex() ?></span>
        <h2>Recent lessons</h2>
    </div>

    <?php if ($recent->isEmpty()) : ?>
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
<?php endif; ?>

<section class="section">
    <div class="section__title">
        <span class="index"><?= $nextIndex() ?></span>
        <h2>Teachers</h2>
    </div>

    <?php if ($teachers->isEmpty()) : ?>
        <p class="empty-state">No teachers yet.</p>
    <?php else : ?>
        <ol class="swiss-list">
            <?php foreach ($teachers as $i => $teacher) : ?>
                <?php
                $reserveUrl = ['controller' => 'Teachers', 'action' => 'reserve', $teacher->id];
                // Guests go to the login page first, then straight on to the reservation.
                if (!$loggedIn) {
                    $reserveUrl = ['_name' => 'login', '?' => ['redirect' => $this->Url->build($reserveUrl)]];
                }
                ?>
                <li class="swiss-list__item">
                    <span class="swiss-list__index"><?= sprintf('%02d', $i + 1) ?></span>
                    <span>
                        <span class="swiss-list__title"><?= h($teacher->user->name) ?></span>
                        <?php if ($teacher->bio) : ?>
                            <div class="swiss-list__meta"><?= h($teacher->bio) ?></div>
                        <?php endif; ?>
                    </span>
                    <span class="swiss-list__meta">
                        <?php if ($loggedIn) : ?>
                            <?= h($teacher->user->email) ?>
                        <?php endif; ?>
                        <?= $this->Html->link(
                            'Reserve time',
                            $reserveUrl,
                            ['class' => 'button-swiss button-swiss--sm'],
                        ) ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>

<?php if ($loggedIn) : ?>
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
<?php endif; ?>

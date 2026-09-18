<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Teacher $teacher
 * @var \App\Model\Entity\Lesson $lesson
 * @var array<array<string, string>> $busyEvents
 */
$this->assign('title', 'Reserve time — ' . $teacher->user->name);
?>
<section class="hero">
    <span class="hero__eyebrow">Reserve a lesson</span>
    <h1><?= h($teacher->user->name) ?></h1>
    <p class="hero__lede">
        Pick a slot on the calendar below, then confirm the details to book
        a lesson with <?= h($teacher->user->name) ?>.
    </p>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">01</span>
        <h2>Pick a time</h2>
    </div>

    <div id="calendar"></div>
</section>

<section class="section">
    <div class="section__title">
        <span class="index">02</span>
        <h2>Confirm booking</h2>
    </div>

    <div class="reserve-picked">
        <span class="reserve-picked__label">Selected time</span>
        <span id="picked-time" class="reserve-picked__value reserve-picked__value--empty">
            None yet — select a slot on the calendar above.
        </span>
    </div>

    <?= $this->Form->create($lesson, ['class' => 'form-swiss']) ?>
        <?php
            // These are set by JS after the visitor picks a slot on the calendar,
            // so FormProtection must not lock their initial (empty) value.
            $this->Form->unlockField('start_time');
            $this->Form->unlockField('end_time');
        ?>
        <?= $this->Form->hidden('start_time', ['id' => 'start_time']) ?>
        <?= $this->Form->hidden('end_time', ['id' => 'end_time']) ?>
        <?= $this->Form->control('description', ['label' => 'Notes (optional)']) ?>
        <div class="form-swiss__actions">
            <?= $this->Form->button('Confirm reservation', [
                'class' => 'button-swiss',
                'id' => 'reserve-submit',
                'disabled' => true,
            ]) ?>
            <?= $this->Html->link('Cancel', ['controller' => 'Home', 'action' => 'index']) ?>
        </div>
    <?= $this->Form->end() ?>
</section>

<?php $this->start('script'); ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var startInput = document.getElementById('start_time');
        var endInput = document.getElementById('end_time');
        var pickedEl = document.getElementById('picked-time');
        var submitBtn = document.getElementById('reserve-submit');

        function pad(n) {
            return String(n).padStart(2, '0');
        }

        function toSqlDateTime(date) {
            return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate())
                + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':00';
        }

        function toDisplay(date) {
            return date.toLocaleString(undefined, {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridWeek,timeGridDay',
            },
            height: 'auto',
            nowIndicator: true,
            slotDuration: '01:00:00',
            selectable: true,
            selectMirror: true,
            selectOverlap: false,
            events: <?= json_encode($busyEvents) ?>,
            select: function (info) {
                startInput.value = toSqlDateTime(info.start);
                endInput.value = toSqlDateTime(info.end);
                pickedEl.textContent = toDisplay(info.start) + ' – ' + toDisplay(info.end);
                pickedEl.classList.remove('reserve-picked__value--empty');
                submitBtn.disabled = false;
            },
        });
        calendar.render();
    });
</script>
<?php $this->end(); ?>

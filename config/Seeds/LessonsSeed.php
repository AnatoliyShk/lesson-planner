<?php
declare(strict_types=1);

use App\Model\Table\LessonsTable;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Migrations\BaseSeed;

/**
 * Seeds 3 student accounts and 4 one-hour lessons per seeded teacher,
 * spread over the weekdays of the current and next week so the calendars
 * have something to show. Each lesson's student is linked through the
 * lessons_users join table, and titles follow the same format as a
 * reservation made through the site: "<teacher> <date> with <student>".
 */
class LessonsSeed extends BaseSeed
{
    /**
     * Lessons belong to the teachers created by TeachersSeed.
     *
     * @return array<string>
     */
    public function getDependencies(): array
    {
        return ['TeachersSeed'];
    }

    /**
     * Run Method.
     *
     * @return void
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $hasher = new DefaultPasswordHasher();
        $password = $hasher->hash('Password123!');

        $students = [
            ['email' => 'mia.schulz@lessonplanner.test', 'name' => 'Mia Schulz'],
            ['email' => 'noah.silva@lessonplanner.test', 'name' => 'Noah Silva'],
            ['email' => 'olivia.brown@lessonplanner.test', 'name' => 'Olivia Brown'],
        ];

        $usersTable = $this->table('users');
        foreach ($students as $student) {
            $usersTable->insertOrSkip([
                'email' => $student['email'],
                'password' => $password,
                'name' => $student['name'],
                'role' => 'user',
                'is_active' => true,
                'created' => $now,
                'modified' => $now,
            ]);
        }
        $usersTable->save();

        // Same teacher accounts as TeachersSeed, with a topic for each lesson.
        $teachers = [
            'alice.novak@lessonplanner.test' => ['Linear equations', 'Quadratics', 'Limits', 'Derivatives'],
            'ben.okafor@lessonplanner.test' => ['Newton\'s laws', 'Momentum', 'Energy', 'Lab: pendulums'],
            'chiara.rossi@lessonplanner.test' => [
                'Poetry reading', 'Short story workshop', 'Essay structure', 'Shakespeare',
            ],
            'daniel.kim@lessonplanner.test' => [
                'Scales and keys', 'Piano technique', 'Harmony basics', 'Ensemble rehearsal',
            ],
            'elena.petrova@lessonplanner.test' => [
                'Line and form', 'Perspective', 'Colour theory', 'Still life painting',
            ],
        ];

        // Weekdays of the current and next week, as day offsets from Monday.
        $monday = strtotime('monday this week');
        $weekdayOffsets = [0, 1, 2, 3, 4, 7, 8, 9, 10, 11];

        $i = 0;
        foreach ($teachers as $email => $topics) {
            $teacher = $this->query(
                'SELECT teachers.id, users.name FROM teachers
                    INNER JOIN users ON users.id = teachers.user_id
                    WHERE users.email = :email',
                ['email' => $email],
            )->fetch('assoc');
            if (!$teacher) {
                $i++;
                continue;
            }

            foreach ($topics as $j => $topic) {
                // Distinct day per lesson, so a teacher's lessons never overlap.
                $day = $monday + $weekdayOffsets[($i + $j * 3) % count($weekdayOffsets)] * 86400;
                $start = strtotime(sprintf('%s %02d:00:00', date('Y-m-d', $day), 9 + ($i * 2 + $j) % 8));
                $startTime = date('Y-m-d H:i:s', $start);

                $student = $students[($i + $j) % count($students)];
                $lessonQuery = [
                    'SELECT id FROM lessons WHERE teacher_id = :teacher_id AND start_time = :start_time',
                    ['teacher_id' => $teacher['id'], 'start_time' => $startTime],
                ];

                // Skip slots that already exist, so re-running doesn't duplicate.
                $lesson = $this->query(...$lessonQuery)->fetch('assoc');
                if (!$lesson) {
                    $this->table('lessons')->insert([
                        'course_id' => LessonsTable::DEFAULT_COURSE_ID,
                        'teacher_id' => (int)$teacher['id'],
                        'title' => sprintf(
                            '%s %s with %s',
                            $teacher['name'],
                            date('j M Y, H:i', $start),
                            $student['name'],
                        ),
                        'description' => $topic,
                        'start_time' => $startTime,
                        'end_time' => date('Y-m-d H:i:s', $start + 3600),
                        'created' => $now,
                        'modified' => $now,
                    ])->save();
                    $lesson = $this->query(...$lessonQuery)->fetch('assoc');
                }

                $this->insertOrSkip('lessons_users', [
                    'lesson_id' => (int)$lesson['id'],
                    'user_id' => (int)$this->query(
                        'SELECT id FROM users WHERE email = :email',
                        ['email' => $student['email']],
                    )->fetch('assoc')['id'],
                ]);
            }
            $i++;
        }
    }
}

<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LessonsTable;
use App\Utility\Uuid;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\LessonsTable Test Case
 */
class LessonsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\LessonsTable
     */
    protected $Lessons;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Lessons',
        'app.Teachers',
        'app.Users',
        'app.LessonsUsers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Lessons') ? [] : ['className' => LessonsTable::class];
        $this->Lessons = $this->getTableLocator()->get('Lessons', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Lessons);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\LessonsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Reserving links the reserving user to the lesson as a student.
     *
     * @return void
     * @link \App\Model\Table\LessonsTable::reserve()
     */
    public function testReserveLinksReservingUser(): void
    {
        $teacher = $this->Lessons->Teachers->get(1, contain: ['Users']);
        $student = $this->Lessons->Students->get(1);

        $lesson = $this->Lessons->reserve($teacher, $student, [
            'start_time' => '2026-10-01 10:00:00',
            'end_time' => '2026-10-01 11:00:00',
        ]);

        $this->assertFalse($lesson->isNew(), 'Reservation should be saved');
        $saved = $this->Lessons->get($lesson->id, contain: ['Students']);
        $this->assertSame([1], array_map(fn($user) => $user->id, $saved->students));
        $this->assertStringEndsWith(' with ' . $student->name, $saved->title);
    }

    /**
     * Students load alongside the teacher's user (as on the lesson view page)
     * without the two Users-backed associations clashing.
     *
     * @return void
     */
    public function testStudentsLoadWithTeacherUser(): void
    {
        $lesson = $this->Lessons->get(1, contain: ['Teachers' => ['Users'], 'Students']);

        $this->assertSame(1, $lesson->teacher->user->id);
        $this->assertSame([1], array_map(fn($user) => $user->id, $lesson->students));
    }

    /**
     * attendedBy finds only lessons the user is linked to.
     *
     * @return void
     * @link \App\Model\Table\LessonsTable::findAttendedBy()
     */
    public function testFindAttendedBy(): void
    {
        $this->assertSame([1], $this->Lessons->find('attendedBy', userId: 1)->all()->extract('id')->toList());
        $this->assertSame([], $this->Lessons->find('attendedBy', userId: 999)->all()->extract('id')->toList());
    }

    /**
     * relatedTo finds lessons the user attends or teaches.
     *
     * @return void
     * @link \App\Model\Table\LessonsTable::findRelatedTo()
     */
    public function testFindRelatedTo(): void
    {
        $related = fn(int $userId) => $this->Lessons->find('relatedTo', userId: $userId)
            ->all()->extract('id')->toList();

        // User 1 teaches lesson 1: still related after the student link is removed.
        $this->Lessons->Students->junction()->deleteAll([]);
        $this->assertSame([1], $related(1));

        // A user who only attends lesson 1.
        $student = $this->Lessons->Students->newEntity(
            ['email' => 'student@example.test', 'password' => 'secret', 'name' => 'Student'],
            ['validate' => false],
        );
        $this->Lessons->Students->saveOrFail($student, ['checkRules' => false]);
        $this->assertSame([], $related($student->id));

        $this->Lessons->Students->link($this->Lessons->get(1), [$student]);
        $this->assertSame([1], $related($student->id));
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\LessonsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * New lessons get a UUIDv7 from UuidBehavior and can be fetched by it.
     *
     * @return void
     */
    public function testUuidAssignedOnCreate(): void
    {
        $lesson = $this->Lessons->newEntity([
            'course_id' => 1,
            'teacher_id' => 1,
            'title' => 'New',
            'start_time' => '2026-10-01 10:00:00',
            'end_time' => '2026-10-01 11:00:00',
        ]);
        $this->Lessons->saveOrFail($lesson);

        $this->assertTrue(Uuid::isValid($lesson->uuid));
        $this->assertSame($lesson->id, $this->Lessons->getByUuid($lesson->uuid)->id);

        // Not mass assignable, and never replaced on update.
        $this->Lessons->patchEntity($lesson, ['uuid' => Uuid::v7(), 'title' => 'Renamed']);
        $this->Lessons->saveOrFail($lesson);
        $this->assertSame($lesson->uuid, $this->Lessons->get($lesson->id)->uuid);
    }
}

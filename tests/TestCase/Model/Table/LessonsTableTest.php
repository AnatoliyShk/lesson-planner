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

<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Utility\Uuid;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Command\BackfillUuidsCommand
 */
class BackfillUuidsCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Teachers',
        'app.Lessons',
    ];

    /**
     * Start from rows that predate the uuid column.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Users', 'Teachers', 'Lessons'] as $alias) {
            $this->getTableLocator()->get($alias)->updateAll(['uuid' => null], []);
        }
    }

    public function testBackfillsEveryTable(): void
    {
        $this->exec('backfill_uuids');

        $this->assertExitSuccess();
        $this->assertOutputContains('Users: 1 row(s) backfilled');
        $this->assertOutputContains('Teachers: 1 row(s) backfilled');
        $this->assertOutputContains('Lessons: 1 row(s) backfilled');

        $lesson = $this->getTableLocator()->get('Lessons')->get(1);
        $this->assertTrue(Uuid::isValid($lesson->uuid));
        // Embeds the row's creation time.
        $this->assertSame(
            (int)$lesson->created->format('Uv'),
            hexdec(str_replace('-', '', substr($lesson->uuid, 0, 13))),
        );
    }

    public function testIsIdempotent(): void
    {
        $this->exec('backfill_uuids');
        $first = $this->getTableLocator()->get('Lessons')->get(1)->uuid;

        $this->exec('backfill_uuids');

        $this->assertOutputContains('Lessons: 0 row(s) backfilled');
        $this->assertSame($first, $this->getTableLocator()->get('Lessons')->get(1)->uuid);
    }

    public function testBatchesAcrossManyRows(): void
    {
        $users = $this->getTableLocator()->get('Users');
        foreach (range(1, 5) as $i) {
            $users->getConnection()->insertQuery('users', [
                'email' => "u{$i}@example.test",
                'password' => 'x',
                'name' => "User {$i}",
            ])->execute();
        }

        $this->exec('backfill_uuids --table Users --batch 2');

        $this->assertExitSuccess();
        $this->assertOutputContains('Users: 6 row(s) backfilled');
        $this->assertOutputNotContains('Lessons');
        $this->assertSame(0, $users->find()->where(['uuid IS' => null])->count());
        $this->assertSame(6, $users->find()->select(['uuid'])->distinct()->count());
    }

    public function testDryRunChangesNothing(): void
    {
        $this->exec('backfill_uuids --dry-run');

        $this->assertExitSuccess();
        $this->assertOutputContains('Lessons: 1 row(s) missing a UUID');
        $this->assertNull($this->getTableLocator()->get('Lessons')->get(1)->uuid);
    }

    public function testRejectsBadBatchSize(): void
    {
        $this->exec('backfill_uuids --batch 0');

        $this->assertExitError();
    }
}

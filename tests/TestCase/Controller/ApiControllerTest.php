<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Utility\Uuid;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\ApiController Test Case
 *
 * Fixture user 1 is made an admin (and is teacher 1); user 2 is a plain user.
 *
 * @link \App\Controller\ApiController
 */
class ApiControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected const PASSWORD = 'Password123!';

    // From the fixtures.
    protected const ADMIN_UUID = '01a0b589-0000-7000-8000-000000000001';
    protected const TEACHER_UUID = '01a0b589-0000-7000-8000-000000000002';
    protected const LESSON_UUID = '01a0b589-0000-7000-8000-000000000003';
    protected const USER_UUID = '01a0b589-0000-7000-8000-000000000004';

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Teachers',
        'app.Lessons',
    ];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $users = $this->getTableLocator()->get('Users');
        $hash = (new DefaultPasswordHasher())->hash(self::PASSWORD);
        $users->updateAll(
            ['email' => 'admin@example.test', 'password' => $hash, 'role' => 'admin'],
            ['id' => 1],
        );
        $users->getConnection()->insertQuery('users', [
            'uuid' => self::USER_UUID,
            'email' => 'user@example.test',
            'password' => $hash,
            'name' => 'Plain User',
            'role' => 'user',
            'is_active' => 1,
            'created' => '2026-09-17 00:00:00',
            'modified' => '2026-09-17 00:00:00',
        ])->execute();
    }

    /**
     * Authenticate the next request with HTTP Basic.
     *
     * @param string $email User email.
     * @return void
     */
    protected function as(string $email): void
    {
        // configRequest() merges recursively; start clean so credentials don't pile up.
        $this->_request = [];
        $this->configRequest([
            'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
            'environment' => ['PHP_AUTH_USER' => $email, 'PHP_AUTH_PW' => self::PASSWORD],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function json(): array
    {
        return json_decode((string)$this->_response->getBody(), true);
    }

    public function testRequiresAuthentication(): void
    {
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->get('/api/lessons');

        $this->assertResponseCode(401);
        $this->assertHeaderContains('WWW-Authenticate', 'Basic');
    }

    public function testWrongPasswordIsRejected(): void
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
            'environment' => ['PHP_AUTH_USER' => 'user@example.test', 'PHP_AUTH_PW' => 'nope'],
        ]);
        $this->get('/api/lessons');

        $this->assertResponseCode(401);
    }

    public function testLessons(): void
    {
        $this->as('user@example.test');
        $this->get('/api/lessons');

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $body = $this->json();
        $this->assertSame([self::LESSON_UUID], array_column($body['lessons'], 'uuid'));
        $this->assertSame(1, $body['pagination']['count']);

        $lesson = $body['lessons'][0];
        $this->assertSame(self::TEACHER_UUID, $lesson['teacher']['uuid']);
        $this->assertSame(self::ADMIN_UUID, $lesson['teacher']['user']['uuid']);
        $this->assertSame('admin@example.test', $lesson['teacher']['user']['email']);
        $this->assertArrayNotHasKey('password', $lesson['teacher']['user']);
        $this->assertArrayNotHasKey('role', $lesson['teacher']['user']);
    }

    public function testNoIntegerIdsAreExposed(): void
    {
        $this->as('user@example.test');
        $this->get('/api/lessons');

        $lesson = $this->json()['lessons'][0];
        foreach (['id', 'teacher_id'] as $key) {
            $this->assertArrayNotHasKey($key, $lesson);
        }
        foreach (['id', 'user_id'] as $key) {
            $this->assertArrayNotHasKey($key, $lesson['teacher']);
        }
        $this->assertArrayNotHasKey('id', $lesson['teacher']['user']);
    }

    public function testLesson(): void
    {
        $this->as('user@example.test');
        $this->get('/api/lessons/' . self::LESSON_UUID);

        $this->assertResponseOk();
        $this->assertSame(self::LESSON_UUID, $this->json()['uuid']);
        $this->assertArrayNotHasKey('id', $this->json());

        // Upper case is accepted too.
        $this->as('user@example.test');
        $this->get('/api/lessons/' . strtoupper(self::LESSON_UUID));
        $this->assertResponseOk();

        $this->as('user@example.test');
        $this->get('/api/lessons/01a0b589-0000-7000-8000-00000000ffff');
        $this->assertResponseCode(404);
    }

    public function testIntegerIdsAreNotRoutable(): void
    {
        $this->as('user@example.test');
        $this->get('/api/lessons/1');
        $this->assertResponseCode(404);

        // Not even through the generic /{controller}/{action}/* fallback.
        $this->as('user@example.test');
        $this->get('/api/lesson/1');
        $this->assertResponseCode(404);
    }

    public function testAddLesson(): void
    {
        $this->as('user@example.test');
        $this->post('/api/lessons', json_encode([
            'teacher_uuid' => self::TEACHER_UUID,
            'start_time' => '2026-10-01 10:00:00',
            'end_time' => '2026-10-01 11:00:00',
            'description' => 'Via API',
            'title' => 'ignored',
        ]));

        $this->assertResponseCode(201);
        $body = $this->json();
        $this->assertSame(self::TEACHER_UUID, $body['teacher']['uuid']);
        $this->assertMatchesRegularExpression('/^' . Uuid::PATTERN . '$/', $body['uuid']);
        $this->assertStringEndsWith('with Plain User', $body['title']);
        $this->assertSame(2, $this->getTableLocator()->get('Lessons')->find()->count());
    }

    public function testAddLessonValidation(): void
    {
        $this->as('user@example.test');
        $this->post('/api/lessons', json_encode(['teacher_uuid' => self::TEACHER_UUID]));

        $this->assertResponseCode(422);
        $this->assertArrayHasKey('start_time', $this->json()['errors']);

        // Integer ids aren't accepted any more; neither are unknown UUIDs.
        foreach ([['teacher_id', 1], ['teacher_uuid', 1], ['teacher_uuid', self::LESSON_UUID]] as [$field, $value]) {
            $this->as('user@example.test');
            $this->post('/api/lessons', json_encode([$field => $value, 'start_time' => '2026-10-01 10:00:00']));
            $this->assertResponseCode(422);
            $this->assertArrayHasKey('teacher_uuid', $this->json()['errors']);
        }
    }

    public function testPostRequiresJson(): void
    {
        $this->configRequest([
            'headers' => ['Accept' => 'application/json'],
            'environment' => ['PHP_AUTH_USER' => 'user@example.test', 'PHP_AUTH_PW' => self::PASSWORD],
        ]);
        $this->post('/api/lessons', ['teacher_uuid' => self::TEACHER_UUID]);

        $this->assertResponseCode(415);
    }

    public function testTeachersAreScopedByRole(): void
    {
        $this->as('admin@example.test');
        $this->get('/api/teachers');
        $this->assertResponseOk();
        $this->assertSame([self::TEACHER_UUID], array_column($this->json()['teachers'], 'uuid'));

        $this->as('user@example.test');
        $this->get('/api/teachers');
        $this->assertResponseOk();
        $this->assertSame([], $this->json()['teachers']);
    }

    public function testTeacher(): void
    {
        $this->as('admin@example.test');
        $this->get('/api/teachers/' . self::TEACHER_UUID);
        $this->assertResponseOk();
        $this->assertSame(self::TEACHER_UUID, $this->json()['uuid']);

        $this->as('user@example.test');
        $this->get('/api/teachers/' . self::TEACHER_UUID);
        $this->assertResponseCode(403);
    }

    public function testAddTeacher(): void
    {
        $this->as('user@example.test');
        $this->post('/api/teachers', json_encode(['user_uuid' => self::USER_UUID, 'bio' => 'Hi']));
        $this->assertResponseCode(403);

        $this->as('admin@example.test');
        $this->post('/api/teachers', json_encode(['user_uuid' => self::USER_UUID, 'bio' => 'Hi', 'active' => true]));
        $this->assertResponseCode(201);
        $this->assertSame(self::USER_UUID, $this->json()['user']['uuid']);
        $this->assertMatchesRegularExpression('/^' . Uuid::PATTERN . '$/', $this->json()['uuid']);

        // Same user again: unique rule, reported under the field the client sent.
        $this->as('admin@example.test');
        $this->post('/api/teachers', json_encode(['user_uuid' => self::USER_UUID]));
        $this->assertResponseCode(422);
        $this->assertArrayHasKey('user_uuid', $this->json()['errors']);

        $this->as('admin@example.test');
        $this->post('/api/teachers', json_encode(['user_id' => 2]));
        $this->assertResponseCode(422);
        $this->assertArrayHasKey('user_uuid', $this->json()['errors']);
    }

    public function testUnroutedMethodIsNotFound(): void
    {
        // No DELETE route; the '/' scope's fallback must not reach ApiController either.
        $this->as('admin@example.test');
        $this->delete('/api/teachers/' . self::TEACHER_UUID);

        $this->assertResponseCode(404);
    }
}

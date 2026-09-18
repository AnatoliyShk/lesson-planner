<?php
declare(strict_types=1);

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Migrations\BaseSeed;

/**
 * Seeds 5 teachers, creating a backing user account for each one that
 * doesn't already exist (matched by email).
 */
class TeachersSeed extends BaseSeed
{
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

        $teachers = [
            [
                'email' => 'alice.novak@lessonplanner.test',
                'name' => 'Alice Novak',
                'bio' => 'Mathematics teacher specialising in algebra and calculus.',
            ],
            [
                'email' => 'ben.okafor@lessonplanner.test',
                'name' => 'Ben Okafor',
                'bio' => 'Physics teacher focused on mechanics and lab work.',
            ],
            [
                'email' => 'chiara.rossi@lessonplanner.test',
                'name' => 'Chiara Rossi',
                'bio' => 'English literature teacher and creative writing coach.',
            ],
            [
                'email' => 'daniel.kim@lessonplanner.test',
                'name' => 'Daniel Kim',
                'bio' => 'Music teacher covering theory, piano, and ensemble practice.',
            ],
            [
                'email' => 'elena.petrova@lessonplanner.test',
                'name' => 'Elena Petrova',
                'bio' => 'Art teacher specialising in drawing and painting fundamentals.',
            ],
        ];

        $usersTable = $this->table('users');
        foreach ($teachers as $teacher) {
            $usersTable->insertOrSkip([
                'email' => $teacher['email'],
                'password' => $password,
                'name' => $teacher['name'],
                'role' => 'user',
                'is_active' => true,
                'created' => $now,
                'modified' => $now,
            ]);
        }
        $usersTable->save();

        $teachersTable = $this->table('teachers');
        foreach ($teachers as $teacher) {
            $statement = $this->query(
                'SELECT id FROM users WHERE email = :email',
                ['email' => $teacher['email']],
            );
            $user = $statement->fetch('assoc');

            $teachersTable->insertOrSkip([
                'user_id' => (int)$user['id'],
                'bio' => $teacher['bio'],
                'active' => true,
                'created' => $now,
                'modified' => $now,
            ]);
        }
        $teachersTable->save();
    }
}

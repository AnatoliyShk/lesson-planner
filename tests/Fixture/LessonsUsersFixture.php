<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * LessonsUsersFixture
 */
class LessonsUsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'lesson_id' => 1,
                'user_id' => 1,
            ],
        ];
        parent::init();
    }
}

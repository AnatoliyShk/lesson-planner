<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'uuid' => '01a0b589-0000-7000-8000-000000000001',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'role' => 'Lorem ipsum dolor ',
                'is_active' => 1,
                'last_login' => '2026-09-17 00:28:41',
                'created' => '2026-09-17 00:28:41',
                'modified' => '2026-09-17 00:28:41',
            ],
        ];
        parent::init();
    }
}

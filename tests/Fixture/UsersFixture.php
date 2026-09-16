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
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'first_name' => 'Lorem ipsum dolor sit amet',
                'last_name' => 'Lorem ipsum dolor sit amet',
                'role' => 'Lorem ipsum dolor ',
                'timezone' => 'Lorem ipsum dolor sit amet',
                'locale' => 'Lorem ip',
                'active' => 1,
                'email_verified' => 1,
                'last_login' => '2026-09-16 15:30:43',
                'created' => '2026-09-16 15:30:43',
                'modified' => '2026-09-16 15:30:43',
            ],
        ];
        parent::init();
    }
}

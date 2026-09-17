<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PasswordResetTokensFixture
 */
class PasswordResetTokensFixture extends TestFixture
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
                'user_id' => 1,
                'token_hash' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                'expires' => '2026-09-17 00:36:09',
                'used_at' => null,
                'created' => '2026-09-17 00:36:09',
            ],
        ];
        parent::init();
    }
}

<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PasswordResetTokensTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PasswordResetTokensTable Test Case
 */
class PasswordResetTokensTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PasswordResetTokensTable
     */
    protected $PasswordResetTokens;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PasswordResetTokens',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PasswordResetTokens') ? [] : ['className' => PasswordResetTokensTable::class];
        $this->PasswordResetTokens = $this->getTableLocator()->get('PasswordResetTokens', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PasswordResetTokens);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PasswordResetTokensTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PasswordResetTokensTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

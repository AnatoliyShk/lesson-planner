<?php
// src/Model/Table/PasswordResetTokensTable.php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

class PasswordResetTokensTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('password_reset_tokens');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp', [
            'events' => ['Model.beforeSave' => ['created' => 'new']],
        ]);

        $this->belongsTo('Users', ['foreignKey' => 'user_id', 'joinType' => 'INNER']);
    }

    /**
     * Unused and unexpired only.
     */
    public function findUsable(SelectQuery $query): SelectQuery
    {
        return $query
            ->where([
                'PasswordResetTokens.used_at IS' => null,
                'PasswordResetTokens.expires >' => new DateTime(),
            ]);
    }

    /**
     * Issue a token. Returns the RAW token — the only time it exists in plaintext.
     */
    public function issueFor(int $userId, int $ttlMinutes = 60): string
    {
        // Invalidate any outstanding tokens: requesting a new link kills the old ones.
        $this->updateAll(
            ['used_at' => new DateTime()],
            ['user_id' => $userId, 'used_at IS' => null]
        );

        $raw = bin2hex(random_bytes(32));   // 64 hex chars, 256 bits

        $token = $this->newEntity([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $raw),
            'expires' => (new DateTime())->addMinutes($ttlMinutes),
        ]);
        $this->saveOrFail($token);

        return $raw;
    }

    /**
     * Look up a raw token. Null if unknown, used or expired.
     */
    public function findByRawToken(string $raw): ?object
    {
        if (strlen($raw) !== 64 || !ctype_xdigit($raw)) {
            return null;   // cheap rejection before touching the DB
        }

        return $this->find('usable')
            ->where(['PasswordResetTokens.token_hash' => hash('sha256', $raw)])
            ->contain(['Users'])
            ->first();
    }

    public function consume(object $token): void
    {
        $token->used_at = new DateTime();
        $this->saveOrFail($token);
    }

    /**
     * Housekeeping — call from cron.
     */
    public function purgeExpired(): int
    {
        return $this->deleteAll(['expires <' => (new DateTime())->subDays(7)]);
    }
}

<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Adds a public UUIDv7 identifier to users, teachers and lessons.
 *
 * Nullable so the migration can run on a populated database: new rows get a
 * UUID from UuidBehavior, existing rows are filled by `bin/cake backfill_uuids`.
 * The unique index still allows the (temporary) NULLs.
 */
class AddUuidToUsersTeachersLessons extends BaseMigration
{
    /**
     * @return void
     */
    public function change(): void
    {
        foreach (['users', 'teachers', 'lessons'] as $tableName) {
            $this->table($tableName)
                ->addColumn('uuid', 'uuid', [
                    'default' => null,
                    'null' => true,
                    'after' => 'id',
                ])
                ->addIndex(['uuid'], ['unique' => true, 'name' => "idx_{$tableName}_uuid"])
                ->update();
        }
    }
}

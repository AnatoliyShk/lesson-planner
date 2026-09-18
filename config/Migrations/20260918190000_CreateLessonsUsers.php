<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Join table for the Lessons <-> Users many-to-many relation: the users
 * (students) attending a lesson.
 */
class CreateLessonsUsers extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('lessons_users', ['signed' => false]);
        $table->addColumn('lesson_id', 'integer', [
            'default' => null,
            'signed' => false,
            'null' => false,
        ]);
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'signed' => false,
            'null' => false,
        ]);
        $table->addIndex(['lesson_id', 'user_id'], ['unique' => true, 'name' => 'uniq_lesson_user']);
        $table->addIndex(['user_id']);
        $table->addForeignKey('lesson_id', 'lessons', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'NO_ACTION',
        ]);
        $table->create();
    }
}

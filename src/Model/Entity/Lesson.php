<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Lesson Entity
 *
 * @property int $id
 * @property string|null $uuid Public UUIDv7 identifier (set on create; backfilled for older rows).
 * @property int $course_id
 * @property int $teacher_id
 * @property string $title
 * @property string|null $description
 * @property \Cake\I18n\DateTime $start_time
 * @property \Cake\I18n\DateTime $end_time
 * @property string|null $status
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Teacher $teacher
 * @property array<\App\Model\Entity\User> $students
 */
class Lesson extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'course_id' => true,
        'teacher_id' => true,
        'title' => true,
        'description' => true,
        'start_time' => true,
        'end_time' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'teacher' => true,
        'students' => true,
    ];
}

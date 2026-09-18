<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Lesson;
use App\Model\Entity\Teacher;
use App\Model\Entity\User;
use Cake\I18n\DateTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Lessons Model
 *
 * @property \App\Model\Table\TeachersTable&\Cake\ORM\Association\BelongsTo $Teachers
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Students
 *
 * @method \App\Model\Entity\Lesson newEmptyEntity()
 * @method \App\Model\Entity\Lesson newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Lesson> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Lesson get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Lesson findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Lesson patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Lesson> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Lesson|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Lesson saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Lesson>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lesson>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Lesson>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lesson> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Lesson>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lesson>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Lesson>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Lesson> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class LessonsTable extends Table
{
    /**
     * Lessons have a required `course_id` with no backing Courses feature yet;
     * reservations use this placeholder.
     */
    public const DEFAULT_COURSE_ID = 1;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('lessons');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Teachers', [
            'foreignKey' => 'teacher_id',
            'joinType' => 'INNER',
        ]);
        // Users attending the lesson. Aliased Students so it doesn't clash
        // with the teacher's user (Teachers.Users) when both are contained.
        $this->belongsToMany('Students', [
            'className' => 'Users',
            'foreignKey' => 'lesson_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'lessons_users',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('course_id')
            ->requirePresence('course_id', 'create')
            ->notEmptyString('course_id');

        $validator
            ->nonNegativeInteger('teacher_id')
            ->notEmptyString('teacher_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->dateTime('start_time')
            ->requirePresence('start_time', 'create')
            ->notEmptyDateTime('start_time');

        $validator
            ->dateTime('end_time')
            ->requirePresence('end_time', 'create')
            ->notEmptyDateTime('end_time');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->allowEmptyString('status');

        return $validator;
    }

    /**
     * Finds lessons the given user attends as a student.
     *
     * Filters through the join table rather than matching('Students'), so
     * the query can freely contain other associations.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query to modify.
     * @param int $userId Student's user id.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findAttendedBy(SelectQuery $query, int $userId): SelectQuery
    {
        $lessonIds = $this->Students->junction()->find()
            ->select(['lesson_id'])
            ->where(['user_id' => $userId]);

        return $query->where([$this->aliasField('id') . ' IN' => $lessonIds]);
    }

    /**
     * Find lessons related to a user: ones they attend as a student and
     * ones they teach.
     *
     * @param \Cake\ORM\Query\SelectQuery $query Query.
     * @param int $userId User id.
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findRelatedTo(SelectQuery $query, int $userId): SelectQuery
    {
        $attendedIds = $this->Students->junction()->find()
            ->select(['lesson_id'])
            ->where(['user_id' => $userId]);
        $taughtByIds = $this->Teachers->find()
            ->select(['id'])
            ->where(['user_id' => $userId]);

        return $query->where(['OR' => [
            $this->aliasField('id') . ' IN' => $attendedIds,
            $this->aliasField('teacher_id') . ' IN' => $taughtByIds,
        ]]);
    }

    /**
     * Reserve a lesson slot with a teacher.
     *
     * Builds and saves a new lesson entity for the given teacher from the
     * submitted reservation data (description, start_time, end_time) and
     * links the reserving user to it as a student. The title is generated:
     * "<teacher> <date> with <reserving user>".
     * On failure the returned entity carries the validation errors.
     *
     * @param \App\Model\Entity\Teacher $teacher Teacher to reserve with (with Users contained).
     * @param \App\Model\Entity\User $reservedBy User making the reservation.
     * @param array $data Reservation form data.
     * @return \App\Model\Entity\Lesson
     */
    public function reserve(Teacher $teacher, User $reservedBy, array $data): Lesson
    {
        $lesson = $this->newEntity([
            'course_id' => self::DEFAULT_COURSE_ID,
            'teacher_id' => $teacher->id,
            'description' => $data['description'] ?? null,
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'students' => ['_ids' => [$reservedBy->id]],
        ]);

        // Left empty when start_time is missing/invalid; validation reports that.
        if ($lesson->start_time instanceof DateTime) {
            $lesson->title = sprintf(
                '%s %s with %s',
                $teacher->user->name,
                $lesson->start_time->i18nFormat('d MMM yyyy, HH:mm'),
                $reservedBy->name,
            );
        }

        $this->save($lesson);

        return $lesson;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['teacher_id'], 'Teachers'), ['errorField' => 'teacher_id']);

        return $rules;
    }
}

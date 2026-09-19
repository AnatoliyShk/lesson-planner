<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\HttpException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Table;
use Cake\View\JsonView;
use SwaggerBake\Lib\Attribute\OpenApiForm;
use SwaggerBake\Lib\Attribute\OpenApiOperation;
use SwaggerBake\Lib\Attribute\OpenApiResponse;

/**
 * REST API for lessons and teachers.
 *
 * Mounted under `/api` (see config/routes.php). Clients authenticate with
 * HTTP Basic (email + password) on every request; there is no session, so
 * CSRF / form tampering protection is not used here. Every response is JSON.
 *
 * Records are identified by their public UUIDv7 (`uuid`), both in URLs and
 * in request bodies; integer primary/foreign keys never leave the app.
 */
class ApiController extends AppController
{
    /**
     * Only the public identity fields of a teacher's user are exposed
     * (`id` is needed for the join and is hidden on output).
     */
    protected const USER_FIELDS = ['Users.id', 'Users.uuid', 'Users.name', 'Users.email'];

    /**
     * Integer keys stripped from every entity in a response.
     */
    protected const INTERNAL_KEYS = ['id', 'teacher_id', 'user_id'];

    /**
     * Any RFC 9562 UUID, either case (records carry v7, but don't be picky on input).
     */
    public const UUID_PATTERN = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Stateless JSON API: no HTML forms, no flash messages.
        $this->components()->unload('FormProtection');
        $this->components()->unload('Flash');
    }

    /**
     * @return array<string>
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Force JSON output and require a JSON body on writes.
     *
     * Requiring `application/json` also means a cross-site HTML form can't
     * submit here, even if the browser has cached Basic credentials.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->viewBuilder()->setClassName('Json');

        // Only the named /api routes may reach this controller, never the
        // generic `/{controller}/{action}/*` fallback of the '/' scope.
        if (!str_starts_with((string)$this->request->getParam('_name'), 'api:')) {
            throw new NotFoundException();
        }

        $contentType = (string)$this->request->contentType();
        if ($this->request->is('post') && !str_starts_with(strtolower($contentType), 'application/json')) {
            throw new HttpException('Request body must be application/json.', 415);
        }
    }

    /**
     * GET /api/lessons
     *
     * Lists lessons (paginated), newest start time first. Like the web
     * Lessons pages, any authenticated user may read them.
     *
     * @return void
     */
    #[OpenApiOperation(summary: 'List lessons', tagNames: ['Lessons'])]
    #[OpenApiResponse(ref: '#/components/schemas/LessonList')]
    public function lessons(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->skipAuthorization();

        $lessons = $this->fetchTable('Lessons');
        $query = $lessons->find()
            ->contain(['Teachers' => ['Users' => ['fields' => self::USER_FIELDS]]])
            ->orderBy(['Lessons.start_time' => 'DESC']);

        $page = $this->paginate($query, ['limit' => 50, 'maxLimit' => 100]);
        array_map($this->hideInternalKeys(...), $page->toArray());
        $this->set('lessons', $page);
        $this->set('pagination', $this->paginationMeta($page));
        $this->viewBuilder()->setOption('serialize', ['lessons', 'pagination']);
    }

    /**
     * GET /api/lessons/{uuid}
     *
     * @param string $uuid Lesson UUID.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the lesson doesn't exist.
     */
    #[OpenApiOperation(summary: 'Get a lesson', tagNames: ['Lessons'])]
    #[OpenApiResponse(ref: '#/components/schemas/Lesson')]
    public function lesson(string $uuid): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->skipAuthorization();

        $lesson = $this->fetchTable('Lessons')->getByUuid($uuid, [
            'Teachers' => ['Users' => ['fields' => self::USER_FIELDS]],
        ]);

        $this->set('lesson', $this->hideInternalKeys($lesson));
        $this->viewBuilder()->setOption('serialize', 'lesson');
    }

    /**
     * POST /api/lessons
     *
     * Reserves a lesson with a teacher for the authenticated user, same as
     * the web "Reserve time" form: the title is generated.
     *
     * Body: {"teacher_uuid": "0192…", "start_time": "2026-09-20 10:00", "end_time": "2026-09-20 11:00", "description": "..."}
     *
     * @return void
     */
    #[OpenApiOperation(summary: 'Reserve a lesson', tagNames: ['Lessons'])]
    #[OpenApiForm(
        name: 'teacher_uuid',
        format: 'uuid',
        example: '01923f4e-8a7b-7c3d-9e1f-2a3b4c5d6e7f',
        isRequired: true,
    )]
    #[OpenApiForm(name: 'start_time', format: 'date-time', example: '2026-10-05 10:00', isRequired: true)]
    #[OpenApiForm(name: 'end_time', format: 'date-time', example: '2026-10-05 11:00', isRequired: true)]
    #[OpenApiForm(name: 'description', example: 'Chapter 4 review')]
    #[OpenApiResponse(statusCode: '201', ref: '#/components/schemas/Lesson')]
    #[OpenApiResponse(statusCode: '422', ref: '#/components/schemas/ValidationError')]
    public function addLesson(): void
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->skipAuthorization();

        $lessons = $this->fetchTable('Lessons');
        /** @var \App\Model\Entity\Teacher|null $teacher */
        $teacher = $this->findByUuidField($lessons->Teachers->getTarget(), 'teacher_uuid', ['Users']);
        if ($teacher === null) {
            $this->respondInvalid(['teacher_uuid' => ['exists' => 'A valid teacher_uuid is required.']]);

            return;
        }

        /** @var \App\Model\Entity\User $reservedBy */
        $reservedBy = $this->Authentication->getIdentity()->getOriginalData();
        $lesson = $lessons->reserve($teacher, $reservedBy, $this->request->getData());

        if ($lesson->isNew()) {
            $this->respondInvalid($lesson->getErrors());

            return;
        }

        $lesson = $lessons->get($lesson->id, contain: [
            'Teachers' => ['Users' => ['fields' => self::USER_FIELDS]],
        ]);

        $this->response = $this->response->withStatus(201);
        $this->set('lesson', $this->hideInternalKeys($lesson));
        $this->viewBuilder()->setOption('serialize', 'lesson');
    }

    /**
     * GET /api/teachers
     *
     * Lists teachers (paginated). Scoped by TeachersTablePolicy: admins see
     * every teacher, other users only their own teacher record.
     *
     * @return void
     */
    #[OpenApiOperation(summary: 'List teachers', tagNames: ['Teachers'])]
    #[OpenApiResponse(ref: '#/components/schemas/TeacherList')]
    public function teachers(): void
    {
        $this->request->allowMethod(['get']);

        $teachers = $this->fetchTable('Teachers');
        $query = $this->Authorization->applyScope($teachers->find(), 'index')
            ->contain(['Users' => ['fields' => self::USER_FIELDS]])
            ->orderBy(['Users.name' => 'ASC']);

        $page = $this->paginate($query, ['limit' => 50, 'maxLimit' => 100]);
        array_map($this->hideInternalKeys(...), $page->toArray());
        $this->set('teachers', $page);
        $this->set('pagination', $this->paginationMeta($page));
        $this->viewBuilder()->setOption('serialize', ['teachers', 'pagination']);
    }

    /**
     * GET /api/teachers/{uuid}
     *
     * @param string $uuid Teacher UUID.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the teacher doesn't exist.
     */
    #[OpenApiOperation(summary: 'Get a teacher', tagNames: ['Teachers'])]
    #[OpenApiResponse(ref: '#/components/schemas/Teacher')]
    public function teacher(string $uuid): void
    {
        $this->request->allowMethod(['get']);

        $teacher = $this->fetchTable('Teachers')->getByUuid($uuid, ['Users' => ['fields' => self::USER_FIELDS]]);
        $this->Authorization->authorize($teacher, 'view');

        $this->set('teacher', $this->hideInternalKeys($teacher));
        $this->viewBuilder()->setOption('serialize', 'teacher');
    }

    /**
     * POST /api/teachers
     *
     * Creates a teacher record for an existing user. Admins only
     * (TeacherPolicy::canAdd).
     *
     * Body: {"user_uuid": "0192…", "bio": "...", "active": true}
     *
     * @return void
     */
    #[OpenApiOperation(summary: 'Create a teacher (admin)', tagNames: ['Teachers'])]
    #[OpenApiForm(name: 'user_uuid', format: 'uuid', example: '01923f4e-8a7b-7c3d-9e1f-2a3b4c5d6e7f', isRequired: true)]
    #[OpenApiForm(name: 'bio', example: 'Maths teacher')]
    #[OpenApiForm(name: 'active', type: 'boolean', example: true)]
    #[OpenApiResponse(statusCode: '201', ref: '#/components/schemas/Teacher')]
    #[OpenApiResponse(statusCode: '422', ref: '#/components/schemas/ValidationError')]
    public function addTeacher(): void
    {
        $this->request->allowMethod(['post']);

        $teachers = $this->fetchTable('Teachers');
        $teacher = $teachers->newEmptyEntity();
        $this->Authorization->authorize($teacher, 'add');

        $user = $this->findByUuidField($teachers->Users->getTarget(), 'user_uuid');
        if ($user === null) {
            $this->respondInvalid(['user_uuid' => ['exists' => 'A valid user_uuid is required.']]);

            return;
        }

        $data = ['user_id' => $user->id] + $this->request->getData();
        $teacher = $teachers->patchEntity($teacher, $data, [
            'fields' => ['user_id', 'bio', 'active'],
        ]);
        if (!$teachers->save($teacher)) {
            // Report problems with the user under the field the client sent.
            $errors = $teacher->getErrors();
            if (isset($errors['user_id'])) {
                $errors['user_uuid'] = $errors['user_id'];
                unset($errors['user_id']);
            }
            $this->respondInvalid($errors);

            return;
        }

        $teacher = $teachers->get($teacher->id, contain: ['Users' => ['fields' => self::USER_FIELDS]]);

        $this->response = $this->response->withStatus(201);
        $this->set('teacher', $this->hideInternalKeys($teacher));
        $this->viewBuilder()->setOption('serialize', 'teacher');
    }

    /**
     * Look up the record whose UUID the client sent in a body field.
     *
     * @param \Cake\ORM\Table $table Table with UuidBehavior.
     * @param string $field Request body field holding the UUID.
     * @param array<array-key, mixed> $contain Associations to contain.
     * @return \Cake\Datasource\EntityInterface|null Null when missing, malformed or unknown.
     */
    protected function findByUuidField(Table $table, string $field, array $contain = []): ?EntityInterface
    {
        $uuid = $this->request->getData($field);
        if (!is_string($uuid) || preg_match('/^' . self::UUID_PATTERN . '$/D', $uuid) !== 1) {
            return null;
        }

        try {
            return $table->getByUuid($uuid, $contain);
        } catch (RecordNotFoundException) {
            return null;
        }
    }

    /**
     * Hide integer keys on an entity and everything nested in it.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to serialize.
     * @return \Cake\Datasource\EntityInterface
     */
    protected function hideInternalKeys(EntityInterface $entity): EntityInterface
    {
        $entity->setHidden(self::INTERNAL_KEYS, true);
        foreach ($entity->getVisible() as $field) {
            $nested = $entity->get($field);
            if ($nested instanceof EntityInterface) {
                $this->hideInternalKeys($nested);
            } elseif (is_array($nested)) {
                foreach ($nested as $item) {
                    if ($item instanceof EntityInterface) {
                        $this->hideInternalKeys($item);
                    }
                }
            }
        }

        return $entity;
    }

    /**
     * Respond 422 with the entity validation errors.
     *
     * @param array $errors Validation errors keyed by field.
     * @return void
     */
    protected function respondInvalid(array $errors): void
    {
        $this->response = $this->response->withStatus(422);
        $this->set([
            'message' => 'Validation failed.',
            'errors' => $errors,
        ]);
        $this->viewBuilder()->setOption('serialize', ['message', 'errors']);
    }

    /**
     * Paging info for list responses.
     *
     * @param \Cake\Datasource\Paging\PaginatedInterface $page Paginated results.
     * @return array<string, mixed>
     */
    protected function paginationMeta(PaginatedInterface $page): array
    {
        return [
            'page' => $page->currentPage(),
            'perPage' => $page->perPage(),
            'count' => $page->totalCount(),
            'pageCount' => $page->pageCount(),
        ];
    }
}

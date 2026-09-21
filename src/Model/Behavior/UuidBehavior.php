<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Utility\Uuid;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

/**
 * Gives every new row a UUIDv7 public identifier in the `uuid` column.
 *
 * The integer `id` stays the primary key for joins; `uuid` is what gets
 * exposed outside the app (the REST API). Rows created before the column
 * existed are filled in by `bin/cake backfill_uuids`.
 */
class UuidBehavior extends Behavior
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'field' => 'uuid',
        'implementedMethods' => [
            'getByUuid' => 'getByUuid',
        ],
    ];

    /**
     * Assign a UUID to new entities that don't have one yet.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Event.
     * @param \Cake\Datasource\EntityInterface $entity Entity being saved.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity): void
    {
        $field = $this->getConfig('field');
        if ($entity->isNew() && empty($entity->get($field))) {
            $entity->set($field, Uuid::v7());
        }
    }

    /**
     * Fetch one record by its UUID.
     *
     * @param string $uuid UUID.
     * @param array<array-key, mixed> $contain Associations to contain.
     * @return \Cake\Datasource\EntityInterface
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When no row has that UUID.
     */
    public function getByUuid(string $uuid, array $contain = []): EntityInterface
    {
        $table = $this->table();
        $entity = $table->find()
            ->where([$table->aliasField($this->getConfig('field')) => strtolower($uuid)])
            ->contain($contain)
            ->first();

        if (!$entity instanceof EntityInterface) {
            throw new RecordNotFoundException(sprintf('Record not found in table `%s`.', $table->getTable()));
        }

        return $entity;
    }
}

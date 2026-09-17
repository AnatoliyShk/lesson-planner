<?php

declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

class TeachersTablePolicy
{
    public function scopeIndex(IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        if ($identity->get('role') === 'admin') {
            return $query;
        }

        return $query->where(['Teachers.user_id' => $identity->getIdentifier()]);
    }
}

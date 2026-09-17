<?php

declare(strict_types=1);

namespace App\Policy;

use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

class UsersTablePolicy
{
    public function scopeIndex(IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        if ($identity->get('role') === 'admin') {
            return $query;
        }

        return $query->where(['Users.id' => $identity->getIdentifier()]);
    }
}

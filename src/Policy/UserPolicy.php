<?php

declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

class UserPolicy
{
    public function canView(IdentityInterface $identity, User $user): bool
    {
        return $this->isSelf($identity, $user) || $this->isAdmin($identity);
    }

    public function canEdit(IdentityInterface $identity, User $user): bool
    {
        return $this->isSelf($identity, $user) || $this->isAdmin($identity);
    }

    public function canDelete(IdentityInterface $identity, User $user): bool
    {
        // Admins only, and never yourself — prevents locking everyone out.
        return $this->isAdmin($identity) && !$this->isSelf($identity, $user);
    }

    public function canChangeRole(IdentityInterface $identity, User $user): bool
    {
        return $this->isAdmin($identity) && !$this->isSelf($identity, $user);
    }

    protected function isSelf(IdentityInterface $identity, User $user): bool
    {
        return (int)$identity->getIdentifier() === (int)$user->id;
    }

    protected function isAdmin(IdentityInterface $identity): bool
    {
        return $identity->get('role') === 'admin';
    }
}

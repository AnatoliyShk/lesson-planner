<?php

declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Teacher;
use Authorization\IdentityInterface;

class TeacherPolicy
{
    public function canAdd(IdentityInterface $identity, Teacher $teacher): bool
    {
        return $this->isAdmin($identity);
    }

    public function canView(IdentityInterface $identity, Teacher $teacher): bool
    {
        return $this->isSelf($identity, $teacher) || $this->isAdmin($identity);
    }

    public function canEdit(IdentityInterface $identity, Teacher $teacher): bool
    {
        return $this->isSelf($identity, $teacher) || $this->isAdmin($identity);
    }

    public function canDelete(IdentityInterface $identity, Teacher $teacher): bool
    {
        return $this->isAdmin($identity);
    }

    protected function isSelf(IdentityInterface $identity, Teacher $teacher): bool
    {
        return (int)$identity->getIdentifier() === (int)$teacher->user_id;
    }

    protected function isAdmin(IdentityInterface $identity): bool
    {
        return $identity->get('role') === 'admin';
    }
}

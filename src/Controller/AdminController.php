<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;

class AdminController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $identity = $this->request->getAttribute('identity');

        // Unauthenticated requests are handled by the Authentication component's
        // own identity check; here we only need to gate authenticated non-admins.
        if ($identity !== null && $identity->get('role') !== 'admin') {
            throw new ForbiddenException();
        }
    }

    public function index(): void
    {
        $this->Authorization->skipAuthorization();
    }
}

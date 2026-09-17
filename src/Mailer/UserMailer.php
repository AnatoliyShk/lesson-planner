<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\User;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;

class UserMailer extends Mailer
{
    public function resetPassword(User $user, string $rawToken): void
    {
        $url = Router::url([
            'controller' => 'Users',
            'action' => 'resetPassword',
            $rawToken,
        ], true);

        $this->setTo($user->email)
            ->setSubject('Reset your password')
            ->setEmailFormat('both')
            ->setViewVars([
                'name' => $user->name,
                'url' => $url,
                'ttlMinutes' => 60,
            ]);

        $this->viewBuilder()->setTemplate('reset_password');
    }

    public function welcome(User $user): void
    {
        $this->setTo($user->email)
            ->setSubject('Welcome to MySite')
            ->setEmailFormat('both')
            ->setViewVars(['name' => $user->name]);

        $this->viewBuilder()->setTemplate('welcome');
    }
}

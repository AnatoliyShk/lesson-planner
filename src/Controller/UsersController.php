<?php
// src/Controller/UsersController.php
declare(strict_types=1);

namespace App\Controller;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Mailer\MailerAwareTrait;

/**
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use MailerAwareTrait;

    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'login', 'register', 'forgotPassword', 'resetPassword',
        ]);
    }

    /**
     * GET  /login   — show form
     * POST /login   — the Form authenticator has already run in middleware
     */
    public function login(): ?Response
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('auth');

        $result = $this->Authentication->getResult();

        if ($result->isValid()) {
            // Session fixation defence: new session ID on privilege change.
            $this->request->getSession()->renew();

            $user = $this->Authentication->getIdentity()->getOriginalData();
            $this->Users->updateAll(['last_login' => new DateTime()], ['id' => $user->id]);

            return $this->redirect($this->Authentication->getLoginRedirect() ?? '/');
        }

        if ($this->request->is('post')) {
            // Deliberately identical for unknown email and wrong password,
            // so the form cannot be used to enumerate registered addresses.
            $this->Flash->error('Invalid email or password');
        }

        return null;
    }

    public function logout(): ?Response
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);   // GET logout is CSRF-abusable

        $this->Authentication->logout();
        $this->request->getSession()->destroy();
        $this->Flash->success('You have been logged out');

        return $this->redirect('/login');
    }

    public function register(): ?Response
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('auth');

        if ($this->Authentication->getIdentity()) {
            return $this->redirect('/');
        }

        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity(
                $user,
                $this->request->getData(),
                ['validate' => 'register']
            );

            if ($this->Users->save($user)) {
                $this->request->getSession()->renew();
                $this->Authentication->setIdentity($user);

                try {
                    $this->getMailer('User')->send('welcome', [$user]);
                } catch (\Throwable $e) {
                    // Never fail a registration because SMTP is down.
                    $this->log('Welcome email failed: ' . $e->getMessage(), 'warning');
                }

                $this->Flash->success('Welcome, ' . $user->name . '!');

                return $this->redirect('/');
            }

            $this->Flash->error('Please correct the errors below');
        }

        $this->set(compact('user'));

        return null;
    }

    public function forgotPassword(): ?Response
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('auth');

        if ($this->request->is('post')) {
            $email = trim((string)$this->request->getData('email'));

            $user = $this->Users->find('active')
                ->where(['Users.email' => $email])
                ->first();

            if ($user) {
                $tokens = $this->fetchTable('PasswordResetTokens');
                $raw = $tokens->issueFor($user->id, 60);

                try {
                    $this->getMailer('User')->send('resetPassword', [$user, $raw]);
                } catch (\Throwable $e) {
                    $this->log('Reset email failed: ' . $e->getMessage(), 'error');
                }
            }

            // SAME message whether or not the account exists.
            $this->Flash->success(
                'If that address is registered, a reset link is on its way.'
            );

            return $this->redirect('/login');
        }

        return null;
    }

    public function resetPassword(string $token): ?Response
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['get', 'post']);
        $this->viewBuilder()->setLayout('auth');

        $tokens = $this->fetchTable('PasswordResetTokens');
        $record = $tokens->findByRawToken($token);

        if (!$record) {
            $this->Flash->error('That reset link is invalid or has expired.');

            return $this->redirect('/forgot-password');
        }

        $user = $record->user;

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity(
                $user,
                $this->request->getData(),
                ['validate' => 'resetPassword']
            );

            if ($this->Users->save($user)) {
                $tokens->consume($record);

                // Any other session using this account is now stale.
                $this->request->getSession()->renew();
                $this->Flash->success('Your password has been changed. Please log in.');

                return $this->redirect('/login');
            }

            $this->Flash->error('Please correct the errors below');
        }

        $this->set(compact('user', 'token'));

        return null;
    }

    public function profile(): ?Response
    {
        $identity = $this->Authentication->getIdentity();
        $user = $this->Users->get($identity->getIdentifier());

        $this->Authorization->authorize($user, 'edit');

        if ($this->request->is(['patch', 'post', 'put'])) {
            // Only these two fields, whatever else was posted.
            $user = $this->Users->patchEntity($user, [
                'name' => $this->request->getData('name'),
                'email' => $this->request->getData('email'),
            ]);

            if ($this->Users->save($user)) {
                $this->Flash->success('Profile updated');

                return $this->redirect('/profile');
            }

            $this->Flash->error('Please correct the errors below');
        }

        $upcomingLessons = $this->Users->Lessons->find('attendedBy', userId: $user->id)
            ->contain(['Teachers' => ['Users']])
            ->where(['Lessons.end_time >=' => DateTime::now()])
            ->orderBy(['Lessons.start_time' => 'ASC'])
            ->all();

        $this->set(compact('user', 'upcomingLessons'));

        return null;
    }

    public function changePassword(): ?Response
    {
        $identity = $this->Authentication->getIdentity();
        $user = $this->Users->get($identity->getIdentifier());

        $this->Authorization->authorize($user, 'edit');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $current = (string)$this->request->getData('current_password');

            if (!(new DefaultPasswordHasher())->check($current, $user->password)) {
                $this->Flash->error('Your current password is incorrect');
                $this->set(compact('user'));

                return null;
            }

            $user = $this->Users->patchEntity(
                $user,
                $this->request->getData(),
                ['validate' => 'changePassword']
            );

            if ($this->Users->save($user)) {
                $this->request->getSession()->renew();
                $this->Flash->success('Password changed');

                return $this->redirect('/profile');
            }

            $this->Flash->error('Please correct the errors below');
        }

        $this->set(compact('user'));

        return null;
    }
}

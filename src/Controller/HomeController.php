<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Home Controller
 *
 */
class HomeController extends AppController
{
    /**
     * The home page is public.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['index']);
    }

    public function index()
    {
        $this->Authorization->skipAuthorization();

        $this->set([
            'recent'   => $this->fetchTable('Lessons')->find()
                ->contain(['Teachers' => ['Users']])
                ->orderBy(['Lessons.created' => 'DESC'])
                ->limit(4)->all(),
        ]);
    }
}

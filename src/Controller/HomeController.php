<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Routing\Router;

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

        $lessons = $this->fetchTable('Lessons')->find()
            ->contain(['Teachers' => ['Users']])
            ->orderBy(['Lessons.start_time' => 'ASC'])
            ->limit(200)
            ->all();

        $calendarEvents = [];
        foreach ($lessons as $lesson) {
            $title = $lesson->title;
            if ($lesson->teacher && $lesson->teacher->user) {
                $title .= ' — ' . $lesson->teacher->user->name;
            }

            $calendarEvents[] = [
                'title' => $title,
                'start' => $lesson->start_time->toIso8601String(),
                'end' => $lesson->end_time->toIso8601String(),
                'url' => Router::url(['controller' => 'Lessons', 'action' => 'view', $lesson->id]),
            ];
        }

        $this->set([
            'recent' => $this->fetchTable('Lessons')->find()
                ->contain(['Teachers' => ['Users']])
                ->orderBy(['Lessons.created' => 'DESC'])
                ->limit(4)->all(),
            'calendarEvents' => $calendarEvents,
        ]);
    }
}

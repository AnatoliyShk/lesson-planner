<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Lesson;
use Cake\Collection\CollectionInterface;
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

        $calendarEvents = $this->fetchTable('Lessons')->find()
            ->contain(['Teachers' => ['Users']])
            ->orderBy(['Lessons.start_time' => 'ASC'])
            ->limit(200)
            ->formatResults(function (CollectionInterface $lessons) {
                return $lessons->map(function (Lesson $lesson) {
                    $title = $lesson->title;
                    if ($lesson->teacher && $lesson->teacher->user) {
                        $title .= ' — ' . $lesson->teacher->user->name;
                    }

                    return [
                        'title' => $title,
                        'start' => $lesson->start_time->toIso8601String(),
                        'end' => $lesson->end_time->toIso8601String(),
                        'url' => Router::url(['controller' => 'Lessons', 'action' => 'view', $lesson->id]),
                    ];
                });
            })
            ->all()
            ->toArray();

        $teachers = $this->fetchTable('Teachers')->find()
            ->contain(['Users'])
            ->where(['Teachers.active' => true])
            ->orderBy(['Users.name' => 'ASC'])
            ->all();

        $this->set([
            'recent' => $this->fetchTable('Lessons')->find()
                ->contain(['Teachers' => ['Users']])
                ->orderBy(['Lessons.created' => 'DESC'])
                ->limit(4)->all(),
            'teachers' => $teachers,
            'calendarEvents' => $calendarEvents,
        ]);
    }
}

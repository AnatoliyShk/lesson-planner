<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Home Controller
 *
 */
class HomeController extends AppController
{
    public function index()
    {
        $this->set([
            'recent'   => $this->fetchTable('Lessons')->find()
                ->contain(['Teachers' => ['Users']])
                ->orderBy(['Lessons.created' => 'DESC'])
                ->limit(4)->all(),
        ]);
    }
}

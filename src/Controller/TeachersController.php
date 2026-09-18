<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Collection\CollectionInterface;

/**
 * Teachers Controller
 *
 * @property \App\Model\Table\TeachersTable $Teachers
 */
class TeachersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Authorization->applyScope($this->Teachers->find())
            ->contain(['Users']);
        $teachers = $this->paginate($query);

        $this->set(compact('teachers'));
    }

    /**
     * View method
     *
     * @param string|null $id Teacher id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $teacher = $this->Teachers->get($id, contain: ['Users', 'Lessons']);
        $this->Authorization->authorize($teacher);
        $this->set(compact('teacher'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $teacher = $this->Teachers->newEmptyEntity();
        $this->Authorization->authorize($teacher);
        if ($this->request->is('post')) {
            $teacher = $this->Teachers->patchEntity($teacher, $this->request->getData());
            if ($this->Teachers->save($teacher)) {
                $this->Flash->success(__('The teacher has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The teacher could not be saved. Please, try again.'));
        }
        $users = $this->Teachers->Users->find('list', limit: 200)->all();
        $this->set(compact('teacher', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Teacher id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $teacher = $this->Teachers->get($id, contain: []);
        $this->Authorization->authorize($teacher);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $teacher = $this->Teachers->patchEntity($teacher, $this->request->getData());
            if ($this->Teachers->save($teacher)) {
                $this->Flash->success(__('The teacher has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The teacher could not be saved. Please, try again.'));
        }
        $users = $this->Teachers->Users->find('list', limit: 200)->all();
        $this->set(compact('teacher', 'users'));
    }

    /**
     * Reserve method
     *
     * Lets a signed-in user pick a time slot on this teacher's calendar and
     * book a new lesson with them. Open to any authenticated user, not
     * gated by TeacherPolicy.
     *
     * @param string|null $id Teacher id.
     * @return \Cake\Http\Response|null|void Redirects on successful reservation, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When the teacher is not found.
     */
    public function reserve($id = null)
    {
        $this->Authorization->skipAuthorization();

        $teacher = $this->Teachers->get($id, contain: ['Users']);
        $lesson = $this->Teachers->Lessons->newEmptyEntity();

        if ($this->request->is('post')) {
            /** @var \App\Model\Entity\User $reservedBy */
            $reservedBy = $this->Authentication->getIdentity()->getOriginalData();
            $lesson = $this->Teachers->Lessons->reserve($teacher, $reservedBy, $this->request->getData());

            if (!$lesson->isNew()) {
                $this->Flash->success(__('Lesson reserved with {0}.', $teacher->user->name));

                return $this->redirect(['controller' => 'Home', 'action' => 'index']);
            }
            $this->Flash->error(__('Could not reserve that time. Please, try again.'));
        }

        $busyEvents = $this->Teachers->Lessons->find()
            ->where(['teacher_id' => $teacher->id])
            ->formatResults(function (CollectionInterface $lessons) {
                return $lessons->map(fn($lesson) => [
                    'title' => 'Booked',
                    'start' => $lesson->start_time->toIso8601String(),
                    'end' => $lesson->end_time->toIso8601String(),
                    'display' => 'background',
                ]);
            })
            ->all()
            ->toArray();

        $this->set(compact('teacher', 'lesson', 'busyEvents'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Teacher id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $teacher = $this->Teachers->get($id);
        $this->Authorization->authorize($teacher);
        if ($this->Teachers->delete($teacher)) {
            $this->Flash->success(__('The teacher has been deleted.'));
        } else {
            $this->Flash->error(__('The teacher could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}

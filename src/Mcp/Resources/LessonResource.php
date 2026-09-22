<?php
declare(strict_types=1);

namespace App\Mcp\Resources;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Crustum\Mcp\Request;
use Crustum\Mcp\Response;
use Crustum\Mcp\Server\Attributes\Description;
use Crustum\Mcp\Server\Attributes\MimeType;
use Crustum\Mcp\Server\Contracts\HasUriTemplate;
use Crustum\Mcp\Server\Resource;
use Crustum\Mcp\Support\UriTemplate;

#[Description('A single lesson: schedule, status, teacher, and enrolled students.')]
#[MimeType('application/json')]
class LessonResource extends Resource implements HasUriTemplate
{
    /**
     * Get the URI template for this resource.
     *
     * @return \Crustum\Mcp\Support\UriTemplate
     */
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('lesson://{uuid}');
    }

    /**
     * Handle the resource request.
     *
     * @param \Crustum\Mcp\Request $request MCP request
     * @return \Crustum\Mcp\Response
     */
    public function handle(Request $request): Response
    {
        $lessons = TableRegistry::getTableLocator()->get('Lessons');

        try {
            $lesson = $lessons->getByUuid((string)$request->get('uuid'), [
                'Teachers' => ['Users' => ['fields' => ['id', 'uuid', 'name', 'email']]],
                'Students',
            ]);
        } catch (RecordNotFoundException) {
            return Response::error('No lesson found for that uuid.');
        }

        return Response::json([
            'uuid' => $lesson->uuid,
            'title' => $lesson->title,
            'description' => $lesson->description,
            'start_time' => $lesson->start_time->toIso8601String(),
            'end_time' => $lesson->end_time->toIso8601String(),
            'status' => $lesson->status,
            'teacher' => [
                'uuid' => $lesson->teacher->uuid,
                'name' => $lesson->teacher->user->name,
            ],
            'students' => array_map(fn($student) => [
                'uuid' => $student->uuid,
                'name' => $student->name,
            ], $lesson->students),
        ]);
    }
}

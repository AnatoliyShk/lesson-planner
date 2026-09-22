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

#[Description("A teacher's public profile: bio, active status, and the lessons they teach.")]
#[MimeType('application/json')]
class TeacherResource extends Resource implements HasUriTemplate
{
    /**
     * Get the URI template for this resource.
     *
     * @return \Crustum\Mcp\Support\UriTemplate
     */
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('teacher://{uuid}');
    }

    /**
     * Handle the resource request.
     *
     * @param \Crustum\Mcp\Request $request MCP request
     * @return \Crustum\Mcp\Response
     */
    public function handle(Request $request): Response
    {
        $teachers = TableRegistry::getTableLocator()->get('Teachers');

        try {
            $teacher = $teachers->getByUuid((string)$request->get('uuid'), [
                'Users' => ['fields' => ['id', 'uuid', 'name', 'email']],
                'Lessons',
            ]);
        } catch (RecordNotFoundException) {
            return Response::error('No teacher found for that uuid.');
        }

        return Response::json([
            'uuid' => $teacher->uuid,
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'bio' => $teacher->bio,
            'active' => $teacher->active,
            'lessons' => array_map(fn($lesson) => [
                'uuid' => $lesson->uuid,
                'title' => $lesson->title,
                'start_time' => $lesson->start_time->toIso8601String(),
                'end_time' => $lesson->end_time->toIso8601String(),
                'status' => $lesson->status,
            ], $teacher->lessons),
        ]);
    }
}

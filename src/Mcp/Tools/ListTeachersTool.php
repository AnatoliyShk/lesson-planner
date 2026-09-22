<?php
declare(strict_types=1);

namespace App\Mcp\Tools;

use Cake\ORM\TableRegistry;
use Crustum\JsonSchema\Contracts\JsonSchema;
use Crustum\Mcp\Request;
use Crustum\Mcp\Response;
use Crustum\Mcp\Server\Attributes\Description;
use Crustum\Mcp\Server\Tool;

#[Description('Lesson Planner: lists teachers, identified by the public uuid used by list-lessons-tool and the teacher:// resource. Call this first when asked for lessons from the planner.')]
class ListTeachersTool extends Tool
{
    /**
     * Handle the tool request.
     *
     * @param \Crustum\Mcp\Request $request MCP request
     * @return \Crustum\Mcp\Response
     */
    public function handle(Request $request): Response
    {
        $teachers = TableRegistry::getTableLocator()->get('Teachers');

        $query = $teachers->find()
            ->contain(['Users' => ['fields' => ['id', 'uuid', 'name', 'email']]])
            ->orderBy(['Users.name' => 'ASC']);

        $active = $request->get('active');
        if ($active !== null) {
            $query->where(['Teachers.active' => (bool)$active]);
        }

        $rows = $query->all()->map(fn($teacher) => [
            'uuid' => $teacher->uuid,
            'name' => $teacher->user->name,
            'email' => $teacher->user->email,
            'bio' => $teacher->bio,
            'active' => $teacher->active,
        ])->toList();

        return Response::json(['teachers' => $rows]);
    }

    /**
     * Get the tool's input schema.
     *
     * @param \Crustum\JsonSchema\Contracts\JsonSchema $schema JSON schema builder
     * @return array<string, \Crustum\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'active' => $schema->boolean()
                ->description('Filter to only active (true) or inactive (false) teachers. Omit to list all.'),
        ];
    }
}

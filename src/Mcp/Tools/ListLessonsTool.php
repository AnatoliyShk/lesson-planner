<?php
declare(strict_types=1);

namespace App\Mcp\Tools;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Crustum\JsonSchema\Contracts\JsonSchema;
use Crustum\Mcp\Request;
use Crustum\Mcp\Response;
use Crustum\Mcp\Server\Attributes\Description;
use Crustum\Mcp\Server\Tool;

#[Description('Lists all lessons taught by a given teacher, identified by the teacher\'s public uuid.')]
class ListLessonsTool extends Tool
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

        try {
            $teacher = $teachers->getByUuid((string)$request->get('teacher_uuid'));
        } catch (RecordNotFoundException) {
            return Response::error('No teacher found for that teacher_uuid.');
        }

        $lessons = TableRegistry::getTableLocator()->get('Lessons')->find()
            ->where(['Lessons.teacher_id' => $teacher->id])
            ->orderBy(['Lessons.start_time' => 'DESC']);

        $rows = $lessons->all()->map(fn($lesson) => [
            'uuid' => $lesson->uuid,
            'title' => $lesson->title,
            'description' => $lesson->description,
            'start_time' => $lesson->start_time->toIso8601String(),
            'end_time' => $lesson->end_time->toIso8601String(),
            'status' => $lesson->status,
        ])->toList();

        return Response::json(['lessons' => $rows]);
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
            'teacher_uuid' => $schema->string()
                ->description("The teacher's public uuid (see the list-teachers tool or the teacher:// resource).")
                ->required(),
        ];
    }
}

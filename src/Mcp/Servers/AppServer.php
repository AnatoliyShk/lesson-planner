<?php
declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Resources\LessonResource;
use App\Mcp\Resources\TeacherResource;
use App\Mcp\Tools\ListLessonsTool;
use App\Mcp\Tools\ListTeachersTool;
use Crustum\Mcp\Server;
use Crustum\Mcp\Server\Attributes\Instructions;
use Crustum\Mcp\Server\Attributes\Name;
use Crustum\Mcp\Server\Attributes\Version;

#[Name('Lesson Planner')]
#[Version('0.0.1')]
#[Instructions('Lesson Planner: the tutoring schedule of one-to-one lessons booked between teachers and students, each with a date, time, student and topic. Use these tools whenever the user mentions the planner, the lesson planner, the schedule, teachers or booked sessions, for example "lessons list from planner". These are not BalkanBuddy course lessons. To list every lesson, call list-teachers-tool, then list-lessons-tool once per teacher uuid.')]
class AppServer extends Server
{
    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Crustum\Mcp\Server\Tool>|\Crustum\Mcp\Server\Tool>
     */
    protected array $tools = [
        ListLessonsTool::class,
        ListTeachersTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Crustum\Mcp\Server\Resource>|\Crustum\Mcp\Server\Resource>
     */
    protected array $resources = [
        LessonResource::class,
        TeacherResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Crustum\Mcp\Server\Prompt>|\Crustum\Mcp\Server\Prompt>
     */
    protected array $prompts = [
    ];
}

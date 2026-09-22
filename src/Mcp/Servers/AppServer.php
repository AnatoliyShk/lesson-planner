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

#[Name('App Server')]
#[Version('0.0.1')]
#[Instructions('Instructions describing how to use the server and its features.')]
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

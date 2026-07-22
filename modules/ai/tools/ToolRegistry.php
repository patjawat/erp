<?php

declare(strict_types=1);

namespace app\modules\ai\tools;

use Throwable;

class ToolRegistry
{
    /**
     * @var array<string, AiToolInterface>
     */
    private array $tools = [];

    /**
     * @param array<int, AiToolInterface>|null $tools
     */
    public function __construct(?array $tools = null)
    {
        foreach ($tools ?: [new QueryDatasetTool(), new ExportExcelTool()] as $tool) {
            $this->tools[$tool->getName()] = $tool;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function definitions(): array
    {
        $definitions = [];
        foreach ($this->tools as $tool) {
            $definitions[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters' => $tool->getJsonSchema(),
                ],
            ];
        }

        return $definitions;
    }

    /**
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function execute(string $toolName, array $arguments, ?string $conversationId = null, ?string $provider = null): array
    {
        if (!isset($this->tools[$toolName])) {
            return [
                'success' => false,
                'tool' => $toolName,
                'error' => "Tool '{$toolName}' is not registered.",
            ];
        }

        try {
            return $this->tools[$toolName]->execute($arguments, $conversationId, $provider);
        } catch (Throwable $exception) {
            return [
                'success' => false,
                'tool' => $toolName,
                'error' => $exception->getMessage(),
            ];
        }
    }
}

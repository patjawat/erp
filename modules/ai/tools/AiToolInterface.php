<?php

declare(strict_types=1);

namespace app\modules\ai\tools;

interface AiToolInterface
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * @return array<string, mixed>
     */
    public function getJsonSchema(): array;

    /**
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function execute(array $arguments, ?string $conversationId = null, ?string $provider = null): array;
}

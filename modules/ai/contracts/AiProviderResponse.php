<?php

declare(strict_types=1);

namespace app\modules\ai\contracts;

final class AiProviderResponse
{
    /**
     * @param array<int, array{id?: string, name: string, arguments: array<string, mixed>}> $toolCalls
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $content = '',
        private array $toolCalls = [],
        private array $metadata = [],
        private ?string $finishReason = null
    ) {
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array<int, array{id?: string, name: string, arguments: array<string, mixed>}>
     */
    public function getToolCalls(): array
    {
        return $this->toolCalls;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getFinishReason(): ?string
    {
        return $this->finishReason;
    }

    public function hasToolCalls(): bool
    {
        return $this->toolCalls !== [];
    }
}

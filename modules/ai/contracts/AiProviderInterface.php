<?php

declare(strict_types=1);

namespace app\modules\ai\contracts;

interface AiProviderInterface
{
    public function getCode(): string;

    public function supportsStreaming(): bool;

    /**
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @param array<string, mixed> $options
     */
    public function chat(array $messages, array $tools = [], array $options = []): AiProviderResponse;

    /**
     * Streams text deltas when the provider supports it. Providers that do not
     * support streaming should emit a single delta from chat().
     *
     * @param array<int, array<string, mixed>> $messages
     * @param array<int, array<string, mixed>> $tools
     * @param array<string, mixed> $options
     */
    public function stream(array $messages, callable $onDelta, array $tools = [], array $options = []): AiProviderResponse;
}

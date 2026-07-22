<?php

declare(strict_types=1);

namespace app\modules\ai\providers;

use app\modules\ai\contracts\AiProviderInterface;
use app\modules\ai\contracts\AiProviderResponse;
use RuntimeException;

class ClaudeProvider extends AbstractHttpProvider implements AiProviderInterface
{
    public string $apiKey = '';

    public string $model = 'claude-3-5-sonnet-latest';

    public string $endpoint = 'https://api.anthropic.com/v1/messages';

    public string $anthropicVersion = '2023-06-01';

    public int $maxTokens = 2048;

    public function getCode(): string
    {
        return 'claude';
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function chat(array $messages, array $tools = [], array $options = []): AiProviderResponse
    {
        $this->assertConfigured();

        [$system, $claudeMessages] = $this->normalizeMessages($messages);
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? 0.2,
            'messages' => $claudeMessages,
        ];

        if ($system !== '') {
            $payload['system'] = $system;
        }

        if ($tools !== []) {
            $payload['tools'] = $this->normalizeTools($tools);
        }

        $data = $this->postJson($this->endpoint, $payload, $this->headers());

        $content = '';
        $toolCalls = [];
        foreach (($data['content'] ?? []) as $block) {
            if (($block['type'] ?? '') === 'text') {
                $content .= (string) ($block['text'] ?? '');
            }

            if (($block['type'] ?? '') === 'tool_use') {
                $toolCalls[] = [
                    'id' => $block['id'] ?? null,
                    'name' => $block['name'] ?? '',
                    'arguments' => $this->decodeArguments($block['input'] ?? []),
                ];
            }
        }

        return new AiProviderResponse(
            $content,
            array_values(array_filter($toolCalls, static fn (array $call): bool => $call['name'] !== '')),
            [
                'model' => $data['model'] ?? $payload['model'],
                'usage' => $data['usage'] ?? null,
            ],
            $data['stop_reason'] ?? null
        );
    }

    public function stream(array $messages, callable $onDelta, array $tools = [], array $options = []): AiProviderResponse
    {
        $this->assertConfigured();

        [$system, $claudeMessages] = $this->normalizeMessages($messages);
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? 0.2,
            'messages' => $claudeMessages,
            'stream' => true,
        ];

        if ($system !== '') {
            $payload['system'] = $system;
        }

        if ($tools !== []) {
            $payload['tools'] = $this->normalizeTools($tools);
        }

        $content = '';
        $this->postStream($this->endpoint, $payload, static function (string $line) use (&$content, $onDelta): void {
            if (!str_starts_with($line, 'data:')) {
                return;
            }

            $data = json_decode(trim(substr($line, 5)), true);
            if (!is_array($data)) {
                return;
            }

            $delta = (string) ($data['delta']['text'] ?? '');
            if ($delta !== '') {
                $content .= $delta;
                $onDelta($delta);
            }
        }, $this->headers());

        return new AiProviderResponse($content, [], ['model' => $payload['model']]);
    }

    /**
     * @param array<int, array<string, mixed>> $messages
     * @return array{0: string, 1: array<int, array<string, mixed>>}
     */
    private function normalizeMessages(array $messages): array
    {
        $system = [];
        $normalized = [];

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $content = (string) ($message['content'] ?? '');

            if ($role === 'system') {
                $system[] = $content;
                continue;
            }

            if ($role === 'tool') {
                $role = 'user';
                $content = 'Tool result: ' . $content;
            }

            $normalized[] = [
                'role' => $role === 'assistant' ? 'assistant' : 'user',
                'content' => $content,
            ];
        }

        return [implode("\n\n", array_filter($system)), $normalized];
    }

    /**
     * @param array<int, array<string, mixed>> $tools
     * @return array<int, array<string, mixed>>
     */
    private function normalizeTools(array $tools): array
    {
        $normalized = [];
        foreach ($tools as $tool) {
            $function = $tool['function'] ?? [];
            $normalized[] = [
                'name' => $function['name'] ?? $tool['name'] ?? '',
                'description' => $function['description'] ?? $tool['description'] ?? '',
                'input_schema' => $function['parameters'] ?? $tool['input_schema'] ?? [
                    'type' => 'object',
                    'properties' => [],
                ],
            ];
        }

        return array_values(array_filter($normalized, static fn (array $tool): bool => $tool['name'] !== ''));
    }

    /**
     * @return array<int, string>
     */
    private function headers(): array
    {
        return [
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: ' . $this->anthropicVersion,
        ];
    }

    private function assertConfigured(): void
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('Claude provider is not configured. Set ANTHROPIC_API_KEY.');
        }
    }
}

<?php

declare(strict_types=1);

namespace app\modules\ai\providers;

use app\modules\ai\contracts\AiProviderInterface;
use app\modules\ai\contracts\AiProviderResponse;
use RuntimeException;

class OpenAiProvider extends AbstractHttpProvider implements AiProviderInterface
{
    public string $apiKey = '';

    public string $model = 'gpt-4o-mini';

    public string $endpoint = 'https://api.openai.com/v1/chat/completions';

    public function getCode(): string
    {
        return 'openai';
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function chat(array $messages, array $tools = [], array $options = []): AiProviderResponse
    {
        $this->assertConfigured();

        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.2,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
        }

        $data = $this->postJson($this->endpoint, $payload, [
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        $message = $data['choices'][0]['message'] ?? [];
        $toolCalls = [];
        foreach (($message['tool_calls'] ?? []) as $toolCall) {
            $toolCalls[] = [
                'id' => $toolCall['id'] ?? null,
                'name' => $toolCall['function']['name'] ?? '',
                'arguments' => $this->decodeArguments($toolCall['function']['arguments'] ?? []),
            ];
        }

        return new AiProviderResponse(
            (string) ($message['content'] ?? ''),
            array_values(array_filter($toolCalls, static fn (array $call): bool => $call['name'] !== '')),
            [
                'model' => $data['model'] ?? $payload['model'],
                'usage' => $data['usage'] ?? null,
            ],
            $data['choices'][0]['finish_reason'] ?? null
        );
    }

    public function stream(array $messages, callable $onDelta, array $tools = [], array $options = []): AiProviderResponse
    {
        $this->assertConfigured();

        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.2,
            'stream' => true,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
        }

        $content = '';
        $finishReason = null;
        $this->postStream($this->endpoint, $payload, static function (string $line) use (&$content, &$finishReason, $onDelta): void {
            if (!str_starts_with($line, 'data:')) {
                return;
            }

            $json = trim(substr($line, 5));
            if ($json === '[DONE]') {
                return;
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                return;
            }

            $delta = (string) ($data['choices'][0]['delta']['content'] ?? '');
            $finishReason = $data['choices'][0]['finish_reason'] ?? $finishReason;
            if ($delta !== '') {
                $content .= $delta;
                $onDelta($delta);
            }
        }, [
            'Authorization: Bearer ' . $this->apiKey,
        ]);

        return new AiProviderResponse($content, [], ['model' => $payload['model']], $finishReason);
    }

    private function assertConfigured(): void
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('OpenAI provider is not configured. Set OPENAI_API_KEY.');
        }
    }
}

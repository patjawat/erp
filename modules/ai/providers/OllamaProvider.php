<?php

declare(strict_types=1);

namespace app\modules\ai\providers;

use app\modules\ai\contracts\AiProviderInterface;
use app\modules\ai\contracts\AiProviderResponse;

class OllamaProvider extends AbstractHttpProvider implements AiProviderInterface
{
    public string $endpoint = 'http://127.0.0.1:11434';

    public string $model = 'llama3.1';

    public function getCode(): string
    {
        return 'ollama';
    }

    public function supportsStreaming(): bool
    {
        return true;
    }

    public function chat(array $messages, array $tools = [], array $options = []): AiProviderResponse
    {
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'stream' => false,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
        }

        $data = $this->postJson(rtrim($this->endpoint, '/') . '/api/chat', $payload);
        $message = $data['message'] ?? [];
        $toolCalls = [];

        foreach (($message['tool_calls'] ?? []) as $toolCall) {
            $function = $toolCall['function'] ?? [];
            $toolCalls[] = [
                'id' => $toolCall['id'] ?? null,
                'name' => $function['name'] ?? '',
                'arguments' => $this->decodeArguments($function['arguments'] ?? []),
            ];
        }

        return new AiProviderResponse(
            (string) ($message['content'] ?? ''),
            array_values(array_filter($toolCalls, static fn (array $call): bool => $call['name'] !== '')),
            [
                'model' => $data['model'] ?? $payload['model'],
                'total_duration' => $data['total_duration'] ?? null,
            ],
            isset($data['done']) && $data['done'] ? 'stop' : null
        );
    }

    public function stream(array $messages, callable $onDelta, array $tools = [], array $options = []): AiProviderResponse
    {
        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'stream' => true,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
        }

        $content = '';
        $this->postStream(rtrim($this->endpoint, '/') . '/api/chat', $payload, static function (string $line) use (&$content, $onDelta): void {
            $data = json_decode($line, true);
            if (!is_array($data)) {
                return;
            }

            $delta = (string) ($data['message']['content'] ?? '');
            if ($delta !== '') {
                $content .= $delta;
                $onDelta($delta);
            }
        });

        return new AiProviderResponse($content, [], ['model' => $payload['model']]);
    }
}

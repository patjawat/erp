<?php

declare(strict_types=1);

namespace app\modules\ai\providers;

use app\modules\ai\contracts\AiProviderInterface;
use app\modules\ai\contracts\AiProviderResponse;
use RuntimeException;

class OpenRouterProvider extends AbstractHttpProvider implements AiProviderInterface
{
    private const FREE_FALLBACK_MODEL = 'openrouter/free';

    public string $apiKey = '';

    public string $model = 'openai/gpt-5.2';

    public int $maxTokens = 2048;

    public string $endpoint = 'https://openrouter.ai/api/v1/chat/completions';

    public string $modelsEndpoint = 'https://openrouter.ai/api/v1/models';

    public ?string $siteUrl = null;

    public ?string $siteName = 'ERP Platform AI';

    public function getCode(): string
    {
        return 'openrouter';
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
            'max_tokens' => max(1, (int) ($options['max_tokens'] ?? $this->maxTokens)),
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
        }

        $fallbackFrom = null;
        try {
            $data = $this->postJson($this->endpoint, $payload, $this->headers());
        } catch (RuntimeException $exception) {
            $fallbackModel = $this->rateLimitFallback($payload['model'], $exception);
            if ($fallbackModel === null) {
                throw $exception;
            }

            $fallbackFrom = $payload['model'];
            $payload['model'] = $fallbackModel;
            $data = $this->postJson($this->endpoint, $payload, $this->headers());
        }
        $message = $data['choices'][0]['message'] ?? [];
        $toolCalls = [];

        foreach (($message['tool_calls'] ?? []) as $toolCall) {
            $toolCalls[] = [
                'id' => $toolCall['id'] ?? null,
                'name' => $toolCall['function']['name'] ?? '',
                'arguments' => $this->decodeArguments($toolCall['function']['arguments'] ?? []),
            ];
        }

        $metadata = [
            'model' => $data['model'] ?? $payload['model'],
            'usage' => $data['usage'] ?? null,
            'provider' => 'openrouter',
        ];
        if ($fallbackFrom !== null) {
            $metadata['fallback_from'] = $fallbackFrom;
        }

        return new AiProviderResponse(
            (string) ($message['content'] ?? ''),
            array_values(array_filter($toolCalls, static fn (array $call): bool => $call['name'] !== '')),
            $metadata,
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
            'max_tokens' => max(1, (int) ($options['max_tokens'] ?? $this->maxTokens)),
            'stream' => true,
        ];

        if ($tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
        }

        $content = '';
        $finishReason = null;
        $consumeLine = static function (string $line) use (&$content, &$finishReason, $onDelta): void {
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
        };

        $fallbackFrom = null;
        try {
            $this->postStream($this->endpoint, $payload, $consumeLine, $this->headers());
        } catch (RuntimeException $exception) {
            $fallbackModel = $this->rateLimitFallback($payload['model'], $exception);
            if ($fallbackModel === null) {
                throw $exception;
            }

            $fallbackFrom = $payload['model'];
            $payload['model'] = $fallbackModel;
            $content = '';
            $finishReason = null;
            $this->postStream($this->endpoint, $payload, $consumeLine, $this->headers());
        }

        $metadata = ['model' => $payload['model'], 'provider' => 'openrouter'];
        if ($fallbackFrom !== null) {
            $metadata['fallback_from'] = $fallbackFrom;
        }

        return new AiProviderResponse($content, [], $metadata, $finishReason);
    }

    public function validateConnection(): void
    {
        $this->assertConfigured();
        $separator = str_contains($this->modelsEndpoint, '?') ? '&' : '?';
        $this->getJson($this->modelsEndpoint . $separator . 'limit=1', $this->headers());
    }

    /**
     * @return array<int, array{id: string, name: string, context_length: int|null, is_free: bool}>
     */
    public function listModels(): array
    {
        $this->assertConfigured();
        $response = $this->getJson($this->modelsEndpoint, $this->headers());
        $models = [];

        foreach (($response['data'] ?? []) as $model) {
            if (!is_array($model)) {
                continue;
            }

            $id = trim((string) ($model['id'] ?? ''));
            if ($id === '') {
                continue;
            }

            $name = trim((string) ($model['name'] ?? ''));
            $models[] = [
                'id' => $id,
                'name' => $name !== '' ? $name : $id,
                'context_length' => isset($model['context_length']) && is_numeric($model['context_length'])
                    ? (int) $model['context_length']
                    : null,
                'is_free' => $this->isFreeModel($model, $id),
            ];
        }

        usort($models, static function (array $left, array $right): int {
            return strnatcasecmp($left['name'], $right['name']) ?: strnatcasecmp($left['id'], $right['id']);
        });

        return $models;
    }

    /**
     * @param array<string, mixed> $model
     */
    private function isFreeModel(array $model, string $id): bool
    {
        if (str_ends_with(strtolower($id), ':free')) {
            return true;
        }

        $pricing = $model['pricing'] ?? null;
        if (!is_array($pricing)) {
            return false;
        }

        $prompt = $pricing['prompt'] ?? null;
        $completion = $pricing['completion'] ?? null;

        return is_numeric($prompt)
            && is_numeric($completion)
            && (float) $prompt === 0.0
            && (float) $completion === 0.0;
    }

    private function rateLimitFallback(string $model, RuntimeException $exception): ?string
    {
        if ($model === self::FREE_FALLBACK_MODEL || !str_ends_with(strtolower($model), ':free')) {
            return null;
        }

        return preg_match('/HTTP status 429\b/i', $exception->getMessage()) === 1
            ? self::FREE_FALLBACK_MODEL
            : null;
    }

    /**
     * @return array<int, string>
     */
    private function headers(): array
    {
        $headers = ['Authorization: Bearer ' . $this->apiKey];

        if ($this->siteUrl !== null && trim($this->siteUrl) !== '') {
            $headers[] = 'HTTP-Referer: ' . trim($this->siteUrl);
        }

        if ($this->siteName !== null && trim($this->siteName) !== '') {
            $headers[] = 'X-OpenRouter-Title: ' . trim($this->siteName);
        }

        return $headers;
    }

    private function assertConfigured(): void
    {
        if (trim($this->apiKey) === '') {
            throw new RuntimeException('OpenRouter provider is not configured. Paste an API key or set OPENROUTER_API_KEY.');
        }
    }
}

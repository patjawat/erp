<?php

declare(strict_types=1);

namespace tests\unit\modules\ai;

use app\modules\ai\providers\OpenRouterProvider;
use Codeception\Test\Unit;
use RuntimeException;

class OpenRouterProviderTest extends Unit
{
    public function testBuildsOpenRouterChatRequest(): void
    {
        $provider = new TestableOpenRouterProvider();
        $provider->apiKey = 'sk-or-v1-test';
        $provider->siteUrl = 'https://erp.example.test';
        $provider->siteName = 'ERP Test';

        $response = $provider->chat(
            [['role' => 'user', 'content' => 'hello']],
            [[
                'type' => 'function',
                'function' => [
                    'name' => 'query_dataset',
                    'parameters' => ['type' => 'object'],
                ],
            ]],
            ['temperature' => 0.4]
        );

        $this->assertSame('openrouter', $provider->getCode());
        $this->assertSame('https://openrouter.ai/api/v1/chat/completions', $provider->lastUrl);
        $this->assertSame('openai/gpt-5.2', $provider->lastPayload['model']);
        $this->assertSame(0.4, $provider->lastPayload['temperature']);
        $this->assertSame(2048, $provider->lastPayload['max_tokens']);
        $this->assertSame('auto', $provider->lastPayload['tool_choice']);
        $this->assertContains('Authorization: Bearer sk-or-v1-test', $provider->lastHeaders);
        $this->assertContains('HTTP-Referer: https://erp.example.test', $provider->lastHeaders);
        $this->assertContains('X-OpenRouter-Title: ERP Test', $provider->lastHeaders);
        $this->assertSame('ok', $response->getContent());
        $this->assertSame('openrouter', $response->getMetadata()['provider']);
    }

    public function testRequiresApiKey(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OpenRouter provider is not configured.');

        (new TestableOpenRouterProvider())->chat([['role' => 'user', 'content' => 'hello']]);
    }

    public function testListsAndSortsModelsAvailableToApiKey(): void
    {
        $provider = new TestableOpenRouterProvider();
        $provider->apiKey = 'sk-or-v1-test';

        $models = $provider->listModels();

        $this->assertSame('https://openrouter.ai/api/v1/models', $provider->lastGetUrl);
        $this->assertContains('Authorization: Bearer sk-or-v1-test', $provider->lastGetHeaders);
        $this->assertSame(['alpha/model', 'gamma/model:free', 'zeta/model'], array_column($models, 'id'));
        $this->assertSame(32768, $models[0]['context_length']);
        $this->assertSame([true, true, false], array_column($models, 'is_free'));
        $this->assertNull($models[2]['context_length']);
    }

    public function testStreamingFallsBackWhenAFreeModelIsRateLimited(): void
    {
        $provider = new RateLimitedStreamOpenRouterProvider();
        $provider->apiKey = 'sk-or-v1-test';
        $provider->model = 'google/example:free';
        $deltas = [];

        $response = $provider->stream(
            [['role' => 'user', 'content' => 'hello']],
            static function (string $delta) use (&$deltas): void {
                $deltas[] = $delta;
            }
        );

        $this->assertSame(['google/example:free', 'openrouter/free'], $provider->attemptedModels);
        $this->assertSame(['ok'], $deltas);
        $this->assertSame('ok', $response->getContent());
        $this->assertSame('openrouter/free', $response->getMetadata()['model']);
        $this->assertSame('google/example:free', $response->getMetadata()['fallback_from']);
    }
}

class TestableOpenRouterProvider extends OpenRouterProvider
{
    public string $lastUrl = '';

    /** @var array<string, mixed> */
    public array $lastPayload = [];

    /** @var array<int, string> */
    public array $lastHeaders = [];

    public string $lastGetUrl = '';

    /** @var array<int, string> */
    public array $lastGetHeaders = [];

    protected function postJson(string $url, array $payload, array $headers = []): array
    {
        $this->lastUrl = $url;
        $this->lastPayload = $payload;
        $this->lastHeaders = $headers;

        return [
            'model' => $payload['model'],
            'choices' => [
                [
                    'message' => ['content' => 'ok'],
                    'finish_reason' => 'stop',
                ],
            ],
            'usage' => ['total_tokens' => 3],
        ];
    }

    protected function getJson(string $url, array $headers = []): array
    {
        $this->lastGetUrl = $url;
        $this->lastGetHeaders = $headers;

        return [
            'data' => [
                [
                    'id' => 'zeta/model',
                    'name' => 'Zeta',
                    'context_length' => null,
                    'pricing' => ['prompt' => '0.000001', 'completion' => '0.000002'],
                ],
                ['id' => '', 'name' => 'Invalid'],
                [
                    'id' => 'alpha/model',
                    'name' => 'Alpha',
                    'context_length' => 32768,
                    'pricing' => ['prompt' => '0', 'completion' => '0.0'],
                ],
                ['id' => 'gamma/model:free', 'name' => 'Gamma', 'context_length' => 65536],
            ],
        ];
    }
}

class RateLimitedStreamOpenRouterProvider extends OpenRouterProvider
{
    /** @var array<int, string> */
    public array $attemptedModels = [];

    protected function postStream(
        string $url,
        array $payload,
        callable $onLine,
        array $headers = []
    ): void {
        $this->attemptedModels[] = $payload['model'];
        if (count($this->attemptedModels) === 1) {
            throw new RuntimeException('AI provider stream failed with HTTP status 429.');
        }

        $onLine('data: {"choices":[{"delta":{"content":"ok"},"finish_reason":"stop"}]}');
        $onLine('data: [DONE]');
    }
}

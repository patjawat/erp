<?php

declare(strict_types=1);

namespace app\modules\ai;

use app\modules\ai\providers\ClaudeProvider;
use app\modules\ai\providers\OllamaProvider;
use app\modules\ai\providers\OpenAiProvider;
use Yii;
use yii\base\BootstrapInterface;

/**
 * Internal AI assistant module.
 *
 * The module is intentionally read-only toward ERP domain data. Runtime reads
 * must go through configured AI views and the QueryGateway.
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'app\modules\ai\controllers';

    public $defaultRoute = 'chat/index';

    public string $defaultProvider = 'openai';

    /**
     * Yii DB component used for AI registry/audit tables.
     */
    public string $db = 'db';

    /**
     * Optional read-only DB component for AI view queries. Falls back to $db.
     */
    public ?string $readDb = null;

    public int $defaultMaxRows = 100;

    public int $absoluteMaxRows = 1000;

    public int $queryTimeoutSeconds = 30;

    public bool $allowStreaming = true;

    /**
     * Provider configuration indexed by provider code.
     *
     * @var array<string, array<string, mixed>>
     */
    public array $providers = [];

    public function init(): void
    {
        parent::init();

        if ($this->providers === []) {
            $this->providers = $this->defaultProviders();
        }
    }

    public function bootstrap($app): void
    {
        Yii::setAlias('@aiModule', __DIR__);
    }

    public function getDb()
    {
        return Yii::$app->get($this->db);
    }

    public function getReadDb()
    {
        return Yii::$app->get($this->readDb ?: $this->db);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function defaultProviders(): array
    {
        return [
            'openai' => [
                'class' => OpenAiProvider::class,
                'apiKey' => $this->env('OPENAI_API_KEY', ''),
                'model' => $this->env('OPENAI_MODEL', 'gpt-4o-mini'),
            ],
            'claude' => [
                'class' => ClaudeProvider::class,
                'apiKey' => $this->env('ANTHROPIC_API_KEY', ''),
                'model' => $this->env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-latest'),
            ],
            'ollama' => [
                'class' => OllamaProvider::class,
                'endpoint' => $this->env('OLLAMA_ENDPOINT', 'http://127.0.0.1:11434'),
                'model' => $this->env('OLLAMA_MODEL', 'llama3.1'),
            ],
        ];
    }

    private function env(string $key, ?string $default = null): ?string
    {
        if (function_exists('env')) {
            $value = env($key);
            return $value === false || $value === null || $value === '' ? $default : (string) $value;
        }

        $value = getenv($key);
        return $value === false || $value === '' ? $default : (string) $value;
    }
}

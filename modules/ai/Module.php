<?php

declare(strict_types=1);

namespace app\modules\ai;

use app\modules\ai\providers\OpenRouterProvider;
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

    public string $defaultProvider = 'openrouter';

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
            'openrouter' => [
                'class' => OpenRouterProvider::class,
                'apiKey' => $this->env('OPENROUTER_API_KEY', ''),
                'model' => $this->env('OPENROUTER_MODEL', 'openai/gpt-5.2'),
                'endpoint' => $this->env('OPENROUTER_CHAT_ENDPOINT', 'https://openrouter.ai/api/v1/chat/completions'),
                'modelsEndpoint' => $this->env('OPENROUTER_MODELS_ENDPOINT', 'https://openrouter.ai/api/v1/models'),
                'siteUrl' => $this->env('OPENROUTER_SITE_URL'),
                'siteName' => $this->env('OPENROUTER_SITE_NAME', 'ERP Platform AI'),
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

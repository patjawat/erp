<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\contracts\AiProviderInterface;
use app\modules\ai\Module;
use InvalidArgumentException;
use Yii;

class AiProviderFactory
{
    public function __construct(private ?Module $module = null)
    {
        $this->module = $module ?: Yii::$app->getModule('ai');
    }

    public function create(?string $providerCode = null): AiProviderInterface
    {
        $providerCode = $providerCode ?: $this->module->defaultProvider;
        $config = $this->module->providers[$providerCode] ?? null;

        if ($config === null) {
            throw new InvalidArgumentException("AI provider '{$providerCode}' is not configured.");
        }

        $provider = Yii::createObject($config);
        if (!$provider instanceof AiProviderInterface) {
            throw new InvalidArgumentException("AI provider '{$providerCode}' must implement AiProviderInterface.");
        }

        return $provider;
    }

    /**
     * @return array<int, string>
     */
    public function getProviderCodes(): array
    {
        return array_keys($this->module->providers);
    }
}

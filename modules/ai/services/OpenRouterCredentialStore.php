<?php

declare(strict_types=1);

namespace app\modules\ai\services;

use app\modules\ai\Module;
use Yii;

class OpenRouterCredentialStore
{
    private const SESSION_KEY = 'ai.openrouter.apiKey';
    private const SESSION_MODEL_KEY = 'ai.openrouter.model';
    private const SESSION_WIDGET_ENABLED_KEY = 'ai.assistant.widgetEnabled';

    public function __construct(private ?Module $module = null)
    {
        $this->module = $module ?: Yii::$app->getModule('ai');
    }

    public function saveApiKey(string $apiKey): void
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            return;
        }

        $this->session()->set(self::SESSION_KEY, $apiKey);
    }

    public function clearApiKey(): void
    {
        if (!$this->hasSession()) {
            return;
        }

        $this->session()->remove(self::SESSION_KEY);
        $this->session()->remove(self::SESSION_MODEL_KEY);
    }

    public function getApiKey(): ?string
    {
        if ($this->hasSession()) {
            $sessionKey = trim((string) $this->session()->get(self::SESSION_KEY, ''));
            if ($sessionKey !== '') {
                return $sessionKey;
            }
        }

        $configuredKey = $this->module->providers['openrouter']['apiKey'] ?? null;
        if (!is_string($configuredKey)) {
            return null;
        }

        $configuredKey = trim($configuredKey);
        return $configuredKey === '' ? null : $configuredKey;
    }

    public function saveSelectedModel(string $model): void
    {
        $model = trim($model);
        if ($model === '' || !$this->hasSession()) {
            return;
        }

        $this->session()->set(self::SESSION_MODEL_KEY, $model);
    }

    public function getSelectedModel(): ?string
    {
        if ($this->hasSession()) {
            $selectedModel = trim((string) $this->session()->get(self::SESSION_MODEL_KEY, ''));
            if ($selectedModel !== '') {
                return $selectedModel;
            }
        }

        $configuredModel = $this->module->providers['openrouter']['model'] ?? null;
        if (!is_string($configuredModel)) {
            return null;
        }

        $configuredModel = trim($configuredModel);
        return $configuredModel === '' ? null : $configuredModel;
    }

    public function saveAssistantWidgetEnabled(bool $enabled): void
    {
        if (!$this->hasSession()) {
            return;
        }

        $this->session()->set($this->widgetEnabledSessionKey(), $enabled);
    }

    public function isAssistantWidgetEnabled(): bool
    {
        if (!$this->hasSession()) {
            return true;
        }

        return (bool) $this->session()->get($this->widgetEnabledSessionKey(), true);
    }

    /**
     * @return array{connected: bool, source: string|null, masked: string|null, selected_model: string|null, assistant_widget_enabled: bool}
     */
    public function status(): array
    {
        $apiKey = $this->getApiKey();

        return [
            'connected' => $apiKey !== null,
            'source' => $apiKey === null ? null : ($this->hasSessionKey() ? 'session' : 'environment'),
            'masked' => $apiKey === null ? null : $this->mask($apiKey),
            'selected_model' => $this->getSelectedModel(),
            'assistant_widget_enabled' => $this->isAssistantWidgetEnabled(),
        ];
    }

    private function widgetEnabledSessionKey(): string
    {
        $userId = Yii::$app !== null && Yii::$app->has('user') && !Yii::$app->user->isGuest
            ? (int) Yii::$app->user->id
            : 0;

        return self::SESSION_WIDGET_ENABLED_KEY . '.' . $userId;
    }

    private function hasSessionKey(): bool
    {
        if (!$this->hasSession()) {
            return false;
        }

        return trim((string) $this->session()->get(self::SESSION_KEY, '')) !== '';
    }

    private function hasSession(): bool
    {
        return Yii::$app !== null && Yii::$app->has('session');
    }

    private function session()
    {
        return Yii::$app->session;
    }

    private function mask(string $apiKey): string
    {
        if (strlen($apiKey) <= 12) {
            return substr($apiKey, 0, 2) . '...' . substr($apiKey, -2);
        }

        return substr($apiKey, 0, 8) . '...' . substr($apiKey, -4);
    }
}

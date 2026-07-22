<?php

declare(strict_types=1);

namespace app\modules\ai\widgets;

use app\modules\ai\services\OpenRouterCredentialStore;
use Yii;
use yii\base\Widget;

final class AssistantWidget extends Widget
{
    public function run(): string
    {
        if (!Yii::$app->has('user') || Yii::$app->user->isGuest || !Yii::$app->user->can('ai.chat.use')) {
            return '';
        }

        $connection = (new OpenRouterCredentialStore())->status();

        return $this->render('assistant', [
            'connected' => (bool) ($connection['connected'] ?? false),
            'selectedModel' => (string) ($connection['selected_model'] ?? ''),
            'enabled' => (bool) ($connection['assistant_widget_enabled'] ?? true),
            'userId' => (int) Yii::$app->user->id,
        ]);
    }
}

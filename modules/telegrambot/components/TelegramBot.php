<?php
namespace app\modules\telegrambot\components;

use app\models\Categorise;

class TelegramBot
{
    protected $botToken;
    protected $apiUrl;

    public function __construct($botToken = null)
    {
        $this->botToken = trim((string) ($botToken ?: $this->resolveBotToken()));
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}/";
    }

    public function sendMessage($chatId, $text)
    {
        $url = $this->apiUrl . 'sendMessage?chat_id=' . urlencode(trim((string) $chatId)) . '&text=' . urlencode($text);
        return file_get_contents($url);
    }

    protected function resolveBotToken()
    {
        $setting = Categorise::findOne(['name' => 'telegram_setting']);
        $data = $this->normalizeDataJson($setting->data_json ?? null);
        if (!empty($data['bot_token'])) {
            return trim((string) $data['bot_token']);
        }

        if (!empty($data['token'])) {
            return trim((string) $data['token']);
        }

        return null;
    }

    protected function normalizeDataJson($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            return json_decode($data, true) ?: [];
        }

        return [];
    }
}

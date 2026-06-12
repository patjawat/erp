<?php
namespace app\modules\telegrambot\components;

use app\models\Categorise;
use Yii;
use yii\httpclient\Client;

class TelegramBot
{
    protected $botToken;
    protected $apiUrl;
    protected $requestTimeout = 5;

    public function __construct($botToken = null)
    {
        $this->botToken = trim((string) ($botToken ?: $this->resolveBotToken()));
        $this->apiUrl = $this->botToken !== '' ? "https://api.telegram.org/bot{$this->botToken}/" : '';
    }

    public function sendMessage($chatId, $text, array $options = [])
    {
        $payload = array_merge([
            'chat_id' => trim((string) $chatId),
            'text' => $text,
            'disable_web_page_preview' => true,
        ], $options);

        return $this->dispatchRequest('sendMessage', $payload, ['reply_markup']);
    }

    public function getMe()
    {
        return $this->dispatchRequest('getMe', [], []);
    }

    public function getChat($chatId)
    {
        return $this->dispatchRequest('getChat', [
            'chat_id' => trim((string) $chatId),
        ], []);
    }

    public function getChatMemberCount($chatId)
    {
        return $this->dispatchRequest('getChatMemberCount', [
            'chat_id' => trim((string) $chatId),
        ], []);
    }

    public function setChatMenuButton(array $menuButton)
    {
        return $this->dispatchRequest('setChatMenuButton', [
            'menu_button' => $menuButton,
        ], ['menu_button']);
    }

    protected function resolveBotToken()
    {
        $setting = Categorise::findOne(['name' => 'telegram_setting']);
        if (!$setting) {
            return null;
        }

        $data = $this->normalizeDataJson($setting->data_json ?? null);
        if (!empty($data['bot_token'])) {
            return trim((string) $data['bot_token']);
        }

        if (!empty($data['token'])) {
            return trim((string) $data['token']);
        }

        return null;
    }

    protected function dispatchRequest(string $endpoint, array $payload = [], array $jsonFields = [])
    {
        if ($this->apiUrl === '') {
            Yii::error('Telegram bot token is not configured', __METHOD__);
            return false;
        }

        foreach ($jsonFields as $field) {
            if (isset($payload[$field]) && is_array($payload[$field])) {
                $payload[$field] = json_encode($payload[$field], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        try {
            $client = new Client(['baseUrl' => $this->apiUrl]);
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($endpoint)
                ->setFormat(Client::FORMAT_URLENCODED)
                ->setOptions([
                    'timeout' => $this->requestTimeout,
                ])
                ->setData($payload)
                ->send();

            if (!$response->isOk || !($response->data['ok'] ?? false)) {
                $description = $response->data['description'] ?? null;
                $errorCode = $response->data['error_code'] ?? null;
                Yii::error(
                    $this->buildErrorMessage($endpoint, $errorCode, $description) . ' | ' . json_encode($response->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    __METHOD__
                );
                return false;
            }

            return $response->data;
        } catch (\Throwable $e) {
            Yii::error('Telegram request failed for ' . $endpoint . ': ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    protected function buildErrorMessage(string $endpoint, $errorCode, $description): string
    {
        $message = 'Telegram request failed for ' . $endpoint;
        if ($errorCode !== null && $errorCode !== '') {
            $message .= ' (' . $errorCode . ')';
        }
        if ($description) {
            $message .= ': ' . $description;
        }
        return $message;
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

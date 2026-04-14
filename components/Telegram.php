<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use app\models\Categorise as TelegramChannel;

class Telegram extends Component
{
    private $lastError = null;

    public function sendMessage($groupCode, $message, array $options = [])
    {
        $this->lastError = null;
        $channel = TelegramChannel::findOne(['name' => 'telegram', 'code' => $groupCode]);
        if (!$channel) {
            $this->lastError = "Telegram channel '{$groupCode}' not found";
            Yii::error($this->lastError, __METHOD__);
            return false;
        }

        $channelData = $this->normalizeDataJson($channel->data_json ?? null);
        $botToken = trim((string) ($channelData['token'] ?? ''));
        $chatId = trim((string) ($channelData['chat_id'] ?? ''));
        if ($botToken === '' || $chatId === '') {
            $this->lastError = "Missing token or chat_id for group '{$groupCode}'";
            Yii::error($this->lastError, __METHOD__);
            return false;
        }

        return $this->dispatchMessage($botToken, $chatId, $message, $options);
    }

    public function sendDirectMessage($chatId, $message, array $options = [])
    {
        $this->lastError = null;
        $botToken = trim((string) $this->resolveDefaultBotToken());
        $chatId = trim((string) $chatId);
        if ($botToken === '' || $chatId === '') {
            $this->lastError = 'Missing bot token or chat id for direct Telegram message';
            Yii::error($this->lastError, __METHOD__);
            return false;
        }

        return $this->dispatchMessage($botToken, $chatId, $message, $options);
    }

    protected function dispatchMessage($botToken, $chatId, $message, array $options = [])
    {
        $botToken = trim((string) $botToken);
        $chatId = trim((string) $chatId);
        $client = new Client([
            'baseUrl' => "https://api.telegram.org/bot{$botToken}/",
        ]);

        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ], $options);

        if (isset($payload['reply_markup']) && is_array($payload['reply_markup'])) {
            $payload['reply_markup'] = json_encode($payload['reply_markup'], JSON_UNESCAPED_UNICODE);
        }

        try {
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl('sendMessage')
                ->setFormat(Client::FORMAT_URLENCODED)
                ->setData($payload)
                ->send();

            if (!$response->isOk || !($response->data['ok'] ?? false)) {
                $description = $response->data['description'] ?? null;
                $errorCode = $response->data['error_code'] ?? null;
                $this->lastError = $this->interpretTelegramError($errorCode, $description, $chatId);
                Yii::error($this->lastError . ' | ' . json_encode($response->data), __METHOD__);
                return false;
            }

            return $response->data;
        } catch (\Throwable $e) {
            $this->lastError = 'Telegram Exception: ' . $e->getMessage();
            Yii::error($this->lastError, __METHOD__);
            return false;
        }
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    protected function resolveDefaultBotToken()
    {
        $setting = TelegramChannel::findOne(['name' => 'telegram_setting']);
        $settingData = $this->normalizeDataJson($setting->data_json ?? null);
        if (!empty($settingData['bot_token'])) {
            return trim((string) $settingData['bot_token']);
        }
        if (!empty($settingData['token'])) {
            return trim((string) $settingData['token']);
        }

        $channel = TelegramChannel::find()->where(['name' => 'telegram'])->orderBy(['id' => SORT_ASC])->one();
        $channelData = $this->normalizeDataJson($channel->data_json ?? null);
        return !empty($channelData['token']) ? trim((string) $channelData['token']) : null;
    }

    protected function resolveDefaultBotUsername()
    {
        $setting = TelegramChannel::findOne(['name' => 'telegram_setting']);
        $settingData = $this->normalizeDataJson($setting->data_json ?? null);
        if (!empty($settingData['bot_username'])) {
            return ltrim((string) $settingData['bot_username'], '@');
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

    protected function interpretTelegramError($errorCode, $description, $chatId)
    {
        $baseError = trim('Telegram send failed'
            . ($errorCode ? " ({$errorCode})" : '')
            . ($description ? ": {$description}" : ''));

        if ((int) $errorCode === 400 && stripos((string) $description, 'chat not found') !== false) {
            $botUsername = $this->resolveDefaultBotUsername();
            $hint = 'ตรวจสอบว่า Telegram ID `' . $chatId . '` เคยกด Start กับ bot ปัจจุบันแล้ว';
            if ($botUsername) {
                $hint .= " (@{$botUsername})";
            }
            $hint .= ' และเป็นการ bind จาก bot ตัวเดียวกับ token ที่ตั้งค่าอยู่';

            return $baseError . ' | ' . $hint;
        }

        return $baseError;
    }
}

<?php

namespace app\modules\telegrambot\controllers;

use app\models\Categorise;
use app\modules\telegrambot\components\TelegramBot;
use app\modules\usermanager\models\User;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class WebhookController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $update = json_decode(Yii::$app->request->rawBody, true);
        if (!is_array($update)) {
            Yii::warning('Telegram webhook received invalid payload', __METHOD__);
            return ['ok' => true];
        }

        $message = $update['message'] ?? null;
        if (!is_array($message)) {
            return ['ok' => true];
        }

        $chat = $message['chat'] ?? [];
        $chatId = trim((string) ($chat['id'] ?? ''));
        $chatType = trim((string) ($chat['type'] ?? ''));
        $text = trim((string) ($message['text'] ?? ''));
        if ($chatId === '' || $chatType !== 'private' || strpos($text, '/start') !== 0) {
            return ['ok' => true];
        }

        $settings = $this->resolveSettings();
        $botToken = trim((string) ($settings['bot_token'] ?? $settings['token'] ?? ''));
        if ($botToken === '') {
            Yii::warning('Telegram webhook cannot reply because bot token is missing', __METHOD__);
            return ['ok' => true];
        }

        $telegram = new TelegramBot($botToken);
        $user = User::findOne(['telegram_id' => $chatId]);
        $lines = [
            'ยินดีต้อนรับสู่ ERP Mobile',
            $user ? 'บัญชี Telegram นี้ผูกกับระบบแล้ว' : 'กดปุ่มด้านล่างเพื่อเข้าสู่ระบบและผูก Telegram กับบัญชีพนักงาน',
        ];

        $options = [];
        $miniAppUrl = trim((string) ($settings['mini_app_base_url'] ?? $settings['mini_app'] ?? ''));
        if ((string) ($settings['enable_mini_app'] ?? '0') === '1' && $this->isValidPublicHttpsUrl($miniAppUrl)) {
            $options['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'เปิด ERP Mobile',
                            'web_app' => [
                                'url' => rtrim($miniAppUrl, '/'),
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            $lines[] = 'ขณะนี้ Mini App ยังไม่ได้เปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ';
        }

        $sent = $telegram->sendMessage($chatId, implode("\n", $lines), $options);
        if (!$sent) {
            Yii::warning('Telegram webhook reply failed: ' . ($telegram->getLastError() ?: 'unknown error'), __METHOD__);
        }

        return ['ok' => true];
    }

    protected function resolveSettings(): array
    {
        $setting = Categorise::findOne(['name' => 'telegram_setting']);
        if (!$setting) {
            return [];
        }

        $data = $this->normalizeDataJson($setting->data_json ?? null);
        $baseUrl = trim((string) ($data['mini_app_base_url'] ?? $data['mini_app'] ?? ''));
        $data['mini_app_base_url'] = $baseUrl;
        $data['mini_app'] = $baseUrl;

        return $data;
    }

    protected function isValidPublicHttpsUrl(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return true;
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

<?php

namespace app\modules\telegrambot\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\telegrambot\components\TelegramBot;

/**
 * Default controller for the `telegrambot` module
 */
class HomeController extends Controller
{
    public function actionSetMenu()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = $this->resolveBotToken(trim((string) Yii::$app->request->post('bot_token', '')));
        if ($botToken === '') {
            return [
                'status' => 'error',
                'message' => 'ไม่พบ bot token ใน Telegram Settings',
            ];
        }

        $webAppUrl = trim((string) Yii::$app->request->post('mini_app_url', ''));
        if ($webAppUrl === '') {
            $setting = \app\models\Categorise::findOne(['name' => 'telegram_setting']);
            $data = $setting ? $this->normalizeDataJson($setting->data_json) : [];
            $webAppUrl = trim((string) ($data['mini_app'] ?? ''));
        }

        if (!$this->isValidTelegramWebAppUrl($webAppUrl)) {
            return [
                'status' => 'error',
                'message' => 'Mini App URL ต้องเป็น https ที่เข้าถึงได้จากภายนอก',
            ];
        }

        $telegram = new TelegramBot($botToken);
        $result = $telegram->setChatMenuButton([
            'type' => 'web_app',
            'text' => 'เปิดระบบ ERP Mobile',
            'web_app' => [
                'url' => $webAppUrl,
            ],
        ]);

        if (!$result) {
            return [
                'status' => 'error',
                'message' => $telegram->getLastError() ?: 'ตั้งค่าเมนู Web App ไม่สำเร็จ',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'ตั้งค่าเมนู Web App สำเร็จ',
            'web_app_url' => $webAppUrl,
        ];
    }

    protected function resolveBotToken(string $override = ''): string
    {
        if (trim($override) !== '') {
            return trim($override);
        }

        $setting = \app\models\Categorise::findOne(['name' => 'telegram_setting']);
        $data = $setting ? $this->normalizeDataJson($setting->data_json) : [];
        return trim((string) ($data['bot_token'] ?? $data['token'] ?? ''));
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

    protected function isValidTelegramWebAppUrl(?string $url): bool
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
}

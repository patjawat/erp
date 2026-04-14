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
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    

    public function actionSetMenu()
    {
        $setting = \app\models\Categorise::findOne(['name' => 'telegram_setting']);
        $botToken = $setting->data_json['bot_token'] ?? $setting->data_json['token'] ?? null;
        if (empty($botToken)) {
            throw new \RuntimeException('ไม่พบ bot token ใน Telegram Settings');
        }

        $data = [
            'menu_button' => [
                'type' => 'web_app',
                'text' => '🧾 เปิดระบบ Web App',
                'web_app' => [
                    'url' => 'https://ee4d-2001-fb1-119-77fb-709a-8667-26bf-3ee.ngrok-free.app/telegrambot/home'  // ลิงก์เว็บของคุณ (HTTPS เท่านั้น)
                ]
            ]
        ];

        file_get_contents("https://api.telegram.org/bot{$botToken}/setChatMenuButton?" . http_build_query($data));
    }
}

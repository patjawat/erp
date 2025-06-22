<?php

namespace app\modules\telegrambot\controllers;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\modules\telegrambot\components\TelegramBot;

/**
 * Default controller for the `telegrambot` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

     public $enableCsrfValidation = false; // ปิด CSRF สำหรับ webhook

    public function actionWebhook()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = Yii::$app->getModule('telegrambot')->botToken;
        $telegram = new TelegramBot($botToken);

        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['message'])) {
            $chatId = $input['message']['chat']['id'];
            $text = $input['message']['text'];

            // ตอบกลับข้อความ
            $telegram->sendMessage($chatId, "คุณส่งข้อความว่า: " . $text);

            return ['status' => 'ok'];
        }

        return ['status' => 'no_message'];
    }

}


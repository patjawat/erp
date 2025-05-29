<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use app\models\Categorise as TelegramChannel;

class Telegram extends Component
{
    
public function sendMessage($groupCode,$message)
{
    // ดึงข้อมูลจากฐานข้อมูลตาม groupCode
    $channel = TelegramChannel::findOne(['name' => 'telegram', 'code' => $groupCode]);

    if (!$channel) {
        Yii::error("Telegram channel '{$groupCode}' not found", __METHOD__);
        return false;
    }

    // ดึง token และ chat_id จาก json
    $botToken = $channel->data_json['token'] ?? null;
    $chatId = $channel->data_json['chat_id'] ?? null;

    // ตรวจสอบว่า token หรือ chat_id หาย
    if (empty($botToken) || empty($chatId)) {
        Yii::error("Missing token or chat_id for group '{$groupCode}'", __METHOD__);
        return false;
    }

    // สร้าง HTTP Client
    $client = new Client([
        'baseUrl' => "https://api.telegram.org/bot{$botToken}/",
    ]);

    try {
        // ส่งข้อความ
        $response = $client->createRequest()
            ->setMethod('POST')
            ->setUrl('sendMessage')
            ->setData([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ])
            ->send();
            return $response;

        // ตรวจสอบการตอบกลับ
        if (!$response->isOk || !$response->data['ok']) {
            Yii::error("Telegram send failed: " . json_encode($response->data), __METHOD__);
            return false;
        }

        return true;

    } catch (\Throwable $e) {
        Yii::error("Telegram Exception: " . $e->getMessage(), __METHOD__);
        return false;
    }
    }
}

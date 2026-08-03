<?php

namespace app\modules\jd\services;

use app\modules\approveV2\models\Approve;
use app\modules\hr\models\Employees;
use app\modules\jd\models\JdEmployee;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แจ้งเตือนผู้ลงนาม JD ผ่าน Telegram เมื่อถึงคิวลงนามของตนเอง
 */
class JdTelegramService
{
    /**
     * ส่งข้อความแจ้งเตือนผู้ลงนามตามแถว approve ที่มีสถานะ Pending
     */
    public static function notifyPendingSigner(JdEmployee $jd, Approve $row): bool
    {
        try {
            $signer = Employees::findOne((int) $row->emp_id);
            $chatId = trim((string) ($signer?->user?->telegram_id ?? ''));
            if ($chatId === '') {
                Yii::info('JD signer has no Telegram ID: emp_id=' . $row->emp_id, __METHOD__);
                return false;
            }

            $data = (array) $row->data_json;
            $role = trim((string) ($data['role'] ?? 'ผู้ลงนาม'));
            $position = trim((string) $jd->position_title) ?: '-';

            $lines = [
                '✍️ <b>มี JD รอให้คุณลงนาม</b>',
                '',
                'บทบาท: <b>' . Html::encode($role) . '</b>',
                'ตำแหน่ง: ' . Html::encode($position),
                'ฉบับที่ (Revision): ' . (int) $jd->revision_no,
                '',
                'กรุณาตรวจสอบและลงนามตามลำดับ',
            ];

            $signUrl = Url::to(['/jd/employee-jd/inbox'], true);

            return (bool) Yii::$app->telegram->sendDirectMessage(
                $chatId,
                implode("\n", $lines),
                [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '📝 เปิดรายการรอลงนาม', 'url' => $signUrl],
                            ],
                        ],
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Yii::error('JD Telegram notification failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

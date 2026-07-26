<?php

namespace app\modules\appreciation\services;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\appreciation\models\Appreciation;

class AppreciationTelegramService
{
    public static function sendToRecipient(Appreciation $appreciation): bool
    {
        try {
            $recipient = $appreciation->toEmp;
            $chatId = trim((string) ($recipient?->user?->telegram_id ?? ''));
            if ($chatId === '') {
                Yii::info('Appreciation recipient has no Telegram ID: ' . $appreciation->to_emp_id, __METHOD__);
                return false;
            }

            $senderName = Html::encode($appreciation->fromEmp?->fullname() ?: 'เพื่อนร่วมงาน');
            $message = trim((string) $appreciation->message);
            if (mb_strlen($message, 'UTF-8') > 800) {
                $message = mb_substr($message, 0, 797, 'UTF-8') . '...';
            }

            $lines = [
                '💖 <b>คุณได้รับคำขอบคุณใหม่</b>',
                '',
                'จาก <b>' . $senderName . '</b>',
            ];

            $badgeLabels = Appreciation::badgeLabels();
            if ($appreciation->badge_type && isset($badgeLabels[$appreciation->badge_type])) {
                $badgeEmojis = Appreciation::badgeEmojis();
                $lines[] = ($badgeEmojis[$appreciation->badge_type] ?? '✨')
                    . ' ' . Html::encode($badgeLabels[$appreciation->badge_type]);
            }

            $lines[] = '';
            $lines[] = '“' . Html::encode($message) . '”';
            $lines[] = '';
            $lines[] = '⭐ ได้รับ +' . (int) $appreciation->points_given . ' คะแนนกำลังใจ';

            $feedUrl = Url::to(['/appreciation/default/index'], true);

            return (bool) Yii::$app->telegram->sendDirectMessage(
                $chatId,
                implode("\n", $lines),
                [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '💞 เปิดฟีดคำขอบคุณ', 'url' => $feedUrl],
                            ],
                        ],
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Yii::error('Appreciation Telegram notification failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

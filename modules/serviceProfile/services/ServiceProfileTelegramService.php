<?php

namespace app\modules\serviceProfile\services;

use app\modules\hr\models\Employees;
use app\modules\serviceProfile\models\ServiceProfile;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class ServiceProfileTelegramService
{
    public static function notify(Employees $employee, ServiceProfile $profile, string $title, string $detail): bool
    {
        try {
            $detail = mb_substr(trim($detail), 0, 1200);
            $chatId = trim((string) ($employee->user?->telegram_id ?? ''));
            if ($chatId === '' || !Yii::$app->has('telegram')) return false;
            $lines = [
                '📘 <b>' . Html::encode($title) . '</b>', '',
                'หน่วยงาน: ' . Html::encode($profile->owner_name_snapshot),
                'ปีงบประมาณ: ' . (int) $profile->fiscal_year . ' · Revision ' . (int) $profile->revision_no,
                Html::encode($detail),
            ];
            return (bool) Yii::$app->telegram->sendDirectMessage($chatId, implode("\n", $lines), [
                'reply_markup' => ['inline_keyboard' => [[[
                    'text' => 'เปิด Service Profile',
                    'url' => Url::to(['/service-profile/default/view', 'id' => $profile->id], true),
                ]]]],
            ]);
        } catch (\Throwable $e) {
            Yii::error('Service Profile Telegram notification failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    public static function notifyMany(array $employees, ServiceProfile $profile, string $title, string $detail): void
    {
        $sent = [];
        foreach ($employees as $employee) {
            if (!$employee instanceof Employees || isset($sent[$employee->id])) continue;
            $sent[$employee->id] = true;
            self::notify($employee, $profile, $title, $detail);
        }
    }
}

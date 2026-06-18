<?php

namespace app\modules\telegrambot\components;

use Yii;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\hr\models\AuthAssignment;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;

/**
 * แจ้งเตือน Telegram สำหรับงานซ่อม (helpdesk)
 * - INSERT: แจ้งทีมช่างตาม repair_group → role
 * - UPDATE status: แจ้งผู้แจ้ง (emp_id) + ทีมช่าง
 *
 * Mapping repair_group → auth_assignment.item_name
 *   1 → technician
 *   2 → computer
 *   3 → medical
 */
class HelpdeskTelegramNotify
{
    private const OPT = [
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];

    private const GROUP_ROLE_MAP = [
        1 => 'technician',
        2 => 'computer',
        3 => 'medical',
    ];

    public static function notifyNewRepair(Helpdesk $model): void
    {
        $role = self::resolveRole($model->repair_group);
        if ($role === null) {
            return;
        }
        $message = self::buildNewRepairMessage($model);
        self::sendToRole($role, $message);
    }

    public static function notifyStatusChanged(Helpdesk $model, ?string $previousStatus): void
    {
        $role = self::resolveRole($model->repair_group);
        if ($role === null) {
            return;
        }
        $message = self::buildStatusChangedMessage($model, $previousStatus);
        self::sendToRole($role, $message);
        self::sendToRequester($model, $message);
    }

    private static function resolveRole($repairGroup): ?string
    {
        $key = (int) $repairGroup;
        return self::GROUP_ROLE_MAP[$key] ?? null;
    }

    private static function sendToRole(string $role, string $messageHtml): void
    {
        try {
            $userIds = AuthAssignment::find()
                ->select('user_id')
                ->where(['item_name' => $role])
                ->distinct()
                ->column();
            if ($userIds === []) {
                return;
            }
            $users = User::find()
                ->where(['id' => $userIds, 'status' => User::STATUS_ACTIVE])
                ->andWhere(['IS NOT', 'telegram_id', null])
                ->andWhere(['<>', 'telegram_id', ''])
                ->all();
            foreach ($users as $user) {
                $chatId = trim((string) ($user->telegram_id ?? ''));
                if ($chatId === '') {
                    continue;
                }
                Yii::$app->telegram->sendDirectMessage($chatId, $messageHtml, self::OPT);
            }
        } catch (\Throwable $e) {
            Yii::error('HelpdeskTelegramNotify sendToRole failed: ' . $e->getMessage(), __METHOD__);
        }
    }

    private static function sendToRequester(Helpdesk $model, string $messageHtml): void
    {
        try {
            $empId = (int) ($model->emp_id ?? 0);
            if ($empId <= 0) {
                return;
            }
            $emp = Employees::findOne($empId);
            if (!$emp || !$emp->user) {
                return;
            }
            $chatId = trim((string) ($emp->user->telegram_id ?? ''));
            if ($chatId === '') {
                return;
            }
            Yii::$app->telegram->sendDirectMessage($chatId, $messageHtml, self::OPT);
        } catch (\Throwable $e) {
            Yii::error('HelpdeskTelegramNotify sendToRequester failed: ' . $e->getMessage(), __METHOD__);
        }
    }

    private static function buildNewRepairMessage(Helpdesk $model): string
    {
        $lines = [
            '🛠️ <b>แจ้งงานซ่อมใหม่</b>',
            '🏷️ รหัส: ' . self::esc(self::repairNumber($model)),
            '📌 หัวข้อ: ' . self::esc((string) $model->title),
        ];
        if (($groupName = self::groupName($model)) !== '') {
            $lines[] = '🏢 กลุ่มงาน: ' . self::esc($groupName);
        }
        if (($urgency = self::urgency($model)) !== '') {
            $lines[] = '⚡ ความเร่งด่วน: ' . self::esc($urgency);
        }
        if (($location = self::location($model)) !== '') {
            $lines[] = '📍 สถานที่: ' . self::esc($location);
        }
        if (($requester = self::requesterName($model)) !== '') {
            $lines[] = '👤 ผู้แจ้ง: ' . self::esc($requester);
        }
        if (($note = self::repairNote($model)) !== '') {
            $lines[] = '📝 อาการ: ' . self::esc($note);
        }
        return implode("\n", $lines);
    }

    private static function buildStatusChangedMessage(Helpdesk $model, ?string $previousStatus): string
    {
        $lines = [
            '🔄 <b>อัปเดตสถานะงานซ่อม</b>',
            '🏷️ รหัส: ' . self::esc(self::repairNumber($model)),
            '📌 หัวข้อ: ' . self::esc((string) $model->title),
            '📊 สถานะ: ' . self::esc(self::statusLabel((string) $previousStatus))
                . ' → <b>' . self::esc(self::statusLabel((string) $model->status)) . '</b>',
        ];
        return implode("\n", $lines);
    }

    private static function repairNumber(Helpdesk $model): string
    {
        return (string) ($model->repair_number ?: $model->id);
    }

    private static function groupName(Helpdesk $model): string
    {
        try {
            $cat = \app\models\Categorise::findOne(['name' => 'repair_group', 'code' => $model->repair_group]);
            return (string) ($cat->title ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function urgency(Helpdesk $model): string
    {
        $data = is_array($model->data_json) ? $model->data_json : [];
        return (string) ($data['urgency'] ?? '');
    }

    private static function location(Helpdesk $model): string
    {
        $data = is_array($model->data_json) ? $model->data_json : [];
        return (string) ($data['location'] ?? '');
    }

    private static function repairNote(Helpdesk $model): string
    {
        $data = is_array($model->data_json) ? $model->data_json : [];
        return (string) ($data['repair_note'] ?? $data['note'] ?? '');
    }

    private static function requesterName(Helpdesk $model): string
    {
        try {
            $empId = (int) ($model->emp_id ?? 0);
            if ($empId <= 0) {
                return '';
            }
            $emp = Employees::findOne($empId);
            return (string) ($emp->fullname ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function statusLabel(string $code): string
    {
        if ($code === '') {
            return '-';
        }
        try {
            $cat = \app\models\Categorise::findOne(['name' => 'repair_status', 'code' => $code]);
            $label = (string) ($cat->title ?? '');
            return $label !== '' ? $label : $code;
        } catch (\Throwable $e) {
            return $code;
        }
    }

    private static function esc(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

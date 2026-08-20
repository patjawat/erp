<?php

namespace app\modules\booking\components;

use Yii;
use app\modules\booking\models\Vehicle;
use app\modules\booking\models\VehicleDetail;
use app\modules\hr\models\AuthAssignment;
use app\modules\hr\models\Employees;
use app\modules\usermanager\models\User;

/**
 * Telegram สำหรับระบบจองรถ: ผู้มีสิทธิ์ vehicle, พนักงานขับ, ผู้ขอหลังจัดสรร
 */
class VehicleTelegramNotify
{
    private const OPT = [
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];

    private static function esc(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function broadcastToVehicleRole(string $messageHtml): void
    {
        try {
            $userIds = AuthAssignment::find()
                ->select('user_id')
                ->where(['item_name' => 'vehicle'])
                ->distinct()
                ->column();
            if ($userIds === []) {
                return;
            }
            $users = User::find()
                ->where(['id' => $userIds, 'status' => User::STATUS_ACTIVE])
                ->all();
            foreach ($users as $user) {
                $cid = trim((string) ($user->telegram_id ?? ''));
                if ($cid !== '') {
                    Yii::$app->telegram->sendDirectMessage($cid, $messageHtml, self::OPT);
                }
            }
        } catch (\Throwable $e) {
        }
    }

    public static function sendVehicleChannel(string $messageHtml): void
    {
        try {
            Yii::$app->telegram->sendMessage('vehicle', $messageHtml, self::OPT);
        } catch (\Throwable $e) {
        }
    }

    public static function notifyEmployeeById(?string $employeeId, string $messageHtml, array $extraOptions = []): bool
    {
        $employeeId = trim((string) $employeeId);
        if ($employeeId === '') {
            return false;
        }
        try {
            $emp = Employees::findOne($employeeId);
            if (!$emp || !$emp->user) {
                return false;
            }
            $cid = trim((string) ($emp->user->telegram_id ?? ''));
            if ($cid === '') {
                return false;
            }

            return (bool) Yii::$app->telegram->sendDirectMessage($cid, $messageHtml, array_merge(self::OPT, $extraOptions));
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function buildDefaultBookingMessage(Vehicle $v): string
    {
        return "
        🚗 <b>แจ้งเตือนการจองรถ</b>
        👤 <b>ผู้จอง:</b> " . self::esc($v->userRequest()['fullname'] ?? '-') . "
        📝 <b>วัตถุประสงค์:</b> " . self::esc($v->reason) . "
        📍 <b>สถานที่:</b> " . self::esc($v->locationOrg?->title ?? '-') . "
        📅 <b>วันที่เดินทาง:</b> " . self::esc($v->showDateRange()) . "
        🕘 <b>เวลา:</b> " . self::esc($v->viewTime()['full'] ?? '') . "
        📋 <b>รหัสคำขอ:</b> " . self::esc($v->code) . "
        ";
    }

    public static function notifyDriverIfSelected(Vehicle $v): void
    {
        $did = trim((string) ($v->driver_id ?? ''));
        if ($did === '') {
            return;
        }
        $msg = "🚙 <b>แจ้งพนักงานขับรถ</b>\n"
            . 'มีคำขอใช้รถที่ระบุคุณเป็นผู้ขับ — รหัส ' . self::esc($v->code) . "\n"
            . '📍 ' . self::esc($v->locationOrg?->title ?? '-') . "\n"
            . '📅 ' . self::esc($v->showDateRange()) . ' ' . self::esc($v->viewTime()['full'] ?? '') . "\n"
            . '👤 ผู้จอง: ' . self::esc($v->userRequest()['fullname'] ?? '-');
        self::notifyEmployeeById($did, $msg);
    }

    public static function notifyDriverChanged(Vehicle $v, ?string $previousDriverId): void
    {
        $prev = trim((string) ($previousDriverId ?? ''));
        $cur = trim((string) ($v->driver_id ?? ''));
        if ($cur === '' || $cur === $prev) {
            return;
        }
        $msg = "🚙 <b>อัปเดตพนักงานขับ</b>\n"
            . 'คุณถูกระบุเป็นผู้ขับสำหรับคำขอ ' . self::esc($v->code) . "\n"
            . '📍 ' . self::esc($v->locationOrg?->title ?? '-') . "\n"
            . '📅 ' . self::esc($v->showDateRange()) . ' ' . self::esc($v->viewTime()['full'] ?? '');
        self::notifyEmployeeById($cur, $msg);
    }

    public static function notifyVehicleDetailDriverChanged(Vehicle $vehicle, VehicleDetail $detail, ?string $previousDriverId): void
    {
        $prev = trim((string) ($previousDriverId ?? ''));
        $cur = trim((string) ($detail->driver_id ?? ''));
        if ($cur === '' || $cur === $prev) {
            return;
        }
        try {
            $dateLabel = Yii::$app->thaiFormatter->asDate($detail->date_start, 'medium');
            if ($detail->date_end && $detail->date_end !== $detail->date_start) {
                $dateLabel .= ' – ' . Yii::$app->thaiFormatter->asDate($detail->date_end, 'medium');
            }
        } catch (\Throwable $e) {
            $dateLabel = (string) $detail->date_start;
        }
        $msg = "🚙 <b>มอบหมายขับรถ</b>\n"
            . 'รหัสคำขอ ' . self::esc($vehicle->code) . "\n"
            . '📅 วันที่ (รายวัน): ' . self::esc($dateLabel) . "\n"
            . '🚗 ทะเบียน: ' . self::esc($detail->license_plate ?? '-') . "\n"
            . '📍 ' . self::esc($vehicle->locationOrg?->title ?? '-') . "\n"
            . '👤 ผู้จอง: ' . self::esc($vehicle->userRequest()['fullname'] ?? '-');
        self::notifyEmployeeById($cur, $msg);
    }

    public static function notifyRequesterAllocated(Vehicle $vehicle): void
    {
        $vehicle->refresh();
        $lines = [
            '✅ <b>การจัดสรรยานพาหนะ</b>',
            'คำขอ ' . self::esc($vehicle->code) . ' ได้รับการจัดสรรแล้ว',
            '📝 วัตถุประสงค์: ' . self::esc($vehicle->reason),
            '📍 สถานที่: ' . self::esc($vehicle->locationOrg?->title ?? '-'),
            '📅 ช่วงเดินทาง: ' . self::esc($vehicle->showDateRange()),
            '🕘 เวลา: ' . self::esc($vehicle->viewTime()['full'] ?? ''),
        ];
        $details = VehicleDetail::find()->where(['vehicle_id' => $vehicle->id])->orderBy(['date_start' => SORT_ASC])->all();
        if ($details !== []) {
            $lines[] = '';
            $lines[] = '<b>รายละเอียดรายวัน / พขร. / ทะเบียน</b>';
            foreach ($details as $d) {
                if (!$d instanceof VehicleDetail) {
                    continue;
                }
                try {
                    $ds = Yii::$app->thaiFormatter->asDate($d->date_start, 'medium');
                } catch (\Throwable $e) {
                    $ds = (string) $d->date_start;
                }
                $driverName = '-';
                if ($d->driver_id && ($dr = Employees::findOne($d->driver_id))) {
                    $driverName = (string) ($dr->fullname ?? '-');
                }
                $lines[] = '• ' . self::esc($ds) . ' | พขร. ' . self::esc($driverName) . ' | ทะเบียน ' . self::esc($d->license_plate ?? '-');
            }
        }
        self::notifyEmployeeById((string) $vehicle->emp_id, implode("\n", $lines));
    }

    /**
     * ภารกิจเสร็จสิ้น → ส่งข้อความสรุป + ลิงก์แบบประเมินความพึงพอใจให้ผู้ขอ
     * ใช้ token ใน vehicle_detail.data_json.survey_token จึงเปิดจาก Telegram ได้โดยไม่ต้องล็อกอิน
     */
    public static function notifyRequesterSurvey(Vehicle $vehicle, VehicleDetail $detail): bool
    {
        try {
            if (!$detail->canSurvey()) {
                return false;
            }
            $url = $detail->surveyUrl();
            if ($url === '') {
                return false;
            }

            try {
                $dateLabel = Yii::$app->thaiFormatter->asDate($detail->date_start, 'medium');
            } catch (\Throwable $e) {
                $dateLabel = (string) $detail->date_start;
            }
            $driverName = '-';
            if ($detail->driver_id && ($dr = Employees::findOne($detail->driver_id))) {
                $driverName = (string) ($dr->fullname ?? '-');
            }

            $lines = [
                '🏁 <b>เสร็จสิ้นภารกิจการใช้รถ</b>',
                'คำขอ ' . self::esc($vehicle->code),
                '📅 วันที่: ' . self::esc($dateLabel),
                '📍 สถานที่: ' . self::esc($vehicle->locationOrg?->title ?? '-'),
                '🚗 ทะเบียน: ' . self::esc($detail->license_plate ?? '-'),
                '👨‍✈️ พขร.: ' . self::esc($driverName),
                '',
                'ขอความกรุณาประเมินความพึงพอใจในการใช้รถ เพื่อนำไปพัฒนาการให้บริการ',
                '⭐ <a href="' . self::esc($url) . '">คลิกที่นี่เพื่อทำแบบประเมิน</a>',
            ];

            $options = [];
            if (self::isPublicHttpsUrl($url)) {
                $options['reply_markup'] = [
                    'inline_keyboard' => [
                        [['text' => '⭐ ประเมินความพึงพอใจ', 'url' => $url]],
                    ],
                ];
            }

            $sent = self::notifyEmployeeById((string) $vehicle->emp_id, implode("\n", $lines), $options);
            if ($sent) {
                $detail->markSurveySent();
            }

            return $sent;
        } catch (\Throwable $e) {
            Yii::error('notifyRequesterSurvey failed: ' . $e->getMessage(), __METHOD__);

            return false;
        }
    }

    /** Telegram ปฏิเสธ inline button ที่ไม่ใช่ URL สาธารณะ — ถ้าไม่เข้าเกณฑ์ให้ใช้ลิงก์ในข้อความแทน */
    private static function isPublicHttpsUrl(string $url): bool
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        return !in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            && !str_ends_with($host, '.localhost')
            && !str_ends_with($host, '.local');
    }
}

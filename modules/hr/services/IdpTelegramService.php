<?php

namespace app\modules\hr\services;

use app\modules\hr\models\Employees;
use app\modules\hr\models\IdpPlan;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * แจ้งเตือน Telegram ตามการเปลี่ยนสถานะของแผน IDP
 * (กระดิ๋งในระบบใช้ ApproveHelper::Idp() แยกต่างหาก)
 */
class IdpTelegramService
{
    /** เจ้าหน้าที่ส่งแผน → แจ้งหัวหน้าให้เห็นชอบ */
    public static function notifySubmitted(IdpPlan $plan): void
    {
        $emp = $plan->employee ?: Employees::findOne($plan->emp_id);
        $lines = [
            '🎯 <b>มีแผน IDP รอให้คุณเห็นชอบ</b>',
            '',
            'ผู้จัดทำ: ' . Html::encode($emp?->fullname ?? '-'),
            'รอบ: ' . Html::encode($plan->cycle?->title ?? '-'),
            'เป้าหมาย: ' . count($plan->goals) . ' รายการ',
            '',
            'กรุณาตรวจสอบและพิจารณาเห็นชอบ',
        ];
        self::sendToEmp((int) $plan->supervisor_emp_id, implode("\n", $lines), '📋 เปิดแผน IDP', Url::to(['/hr/idp/employee', 'emp_id' => $plan->emp_id], true));
    }

    /** หัวหน้าเห็นชอบ → แจ้งเจ้าหน้าที่ + แจ้ง HR ให้เปิดบันทึก */
    public static function notifyApproved(IdpPlan $plan): void
    {
        $lines = [
            '✅ <b>หัวหน้าเห็นชอบแผน IDP แล้ว</b>',
            '',
            'รอบ: ' . Html::encode($plan->cycle?->title ?? '-'),
            'สถานะ: รอ HR ตรวจสอบและเปิดให้บันทึกผล',
        ];
        self::sendToEmp((int) $plan->emp_id, implode("\n", $lines), '🎯 ดูแผน IDP', Url::to(['/profile', 'name' => 'idp'], true));
        self::notifyHr($plan);
    }

    /** ส่งกลับให้ปรับปรุง → แจ้งเจ้าหน้าที่ */
    public static function notifyReturned(IdpPlan $plan): void
    {
        $lines = [
            '📝 <b>แผน IDP ถูกส่งกลับให้ปรับปรุง</b>',
            '',
            'รอบ: ' . Html::encode($plan->cycle?->title ?? '-'),
        ];
        if (trim((string) $plan->supervisor_comment) !== '') {
            $lines[] = 'ความคิดเห็น: ' . Html::encode($plan->supervisor_comment);
        }
        self::sendToEmp((int) $plan->emp_id, implode("\n", $lines), '✏️ แก้ไขแผน IDP', Url::to(['/profile', 'name' => 'idp'], true));
    }

    /** HR เปิดให้บันทึกผล → แจ้งเจ้าหน้าที่ */
    public static function notifyOpened(IdpPlan $plan): void
    {
        $lines = [
            '🚀 <b>เปิดให้บันทึกผลการดำเนินการ IDP แล้ว</b>',
            '',
            'รอบ: ' . Html::encode($plan->cycle?->title ?? '-'),
            'คุณสามารถบันทึกความก้าวหน้าของแต่ละกิจกรรมได้แล้ว',
        ];
        self::sendToEmp((int) $plan->emp_id, implode("\n", $lines), '📈 บันทึกผล IDP', Url::to(['/profile', 'name' => 'idp'], true));
    }

    /** HRD ปิดรอบ → แจ้งเจ้าหน้าที่ */
    public static function notifyClosed(IdpPlan $plan): void
    {
        $lines = [
            '🏁 <b>ปิดรอบ IDP ประจำปีแล้ว</b>',
            '',
            'รอบ: ' . Html::encode($plan->cycle?->title ?? '-'),
            'ความก้าวหน้ารวม: ' . (int) $plan->progress_percent . '%',
        ];
        self::sendToEmp((int) $plan->emp_id, implode("\n", $lines), '🎯 ดูแผน IDP', Url::to(['/profile', 'name' => 'idp'], true));
    }

    /** แจ้งผู้ใช้ role hr ทุกคนที่ผูก Telegram ให้มาเปิดบันทึก */
    protected static function notifyHr(IdpPlan $plan): void
    {
        try {
            $emp = $plan->employee ?: Employees::findOne($plan->emp_id);
            $text = implode("\n", [
                '📋 <b>มีแผน IDP รอ HR เปิดให้บันทึกผล</b>',
                '',
                'ผู้จัดทำ: ' . Html::encode($emp?->fullname ?? '-'),
                'รอบ: ' . Html::encode($plan->cycle?->title ?? '-'),
                'หัวหน้าเห็นชอบแล้ว รอเปิดให้บันทึกผล',
            ]);
            $url = Url::to(['/hr/idp/employee', 'emp_id' => $plan->emp_id], true);
            $userIds = Yii::$app->authManager->getUserIdsByRole('hr');
            foreach (array_unique($userIds) as $uid) {
                $hr = Employees::findOne(['user_id' => $uid]);
                if ($hr) {
                    self::sendToEmp((int) $hr->id, $text, '🗂️ เปิดให้บันทึกผล', $url);
                }
            }
        } catch (\Throwable $e) {
            Yii::error('IDP HR telegram failed: ' . $e->getMessage(), __METHOD__);
        }
    }

    protected static function sendToEmp(int $empId, string $text, string $btnText, string $url): bool
    {
        try {
            if ($empId <= 0) {
                return false;
            }
            $emp = Employees::findOne($empId);
            $chatId = trim((string) ($emp?->user?->telegram_id ?? ''));
            if ($chatId === '') {
                Yii::info('IDP telegram: emp has no telegram_id, emp_id=' . $empId, __METHOD__);
                return false;
            }
            return (bool) Yii::$app->telegram->sendDirectMessage($chatId, $text, [
                'reply_markup' => ['inline_keyboard' => [[['text' => $btnText, 'url' => $url]]]],
            ]);
        } catch (\Throwable $e) {
            Yii::error('IDP telegram send failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

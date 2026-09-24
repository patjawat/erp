<?php

namespace app\modules\purchase\components;

use Yii;
use app\modules\hr\models\Employees;
use app\modules\approve\models\Approve;
use app\modules\purchase\models\Order;

/**
 * แจ้งเตือนผู้อนุมัติใบขอซื้อผ่าน Telegram (ส่งตรงถึง user.telegram_id แบบเดียวกับ LeaveTelegramService)
 * ใช้กับใบนอกแผน: ส่งคำขอ → หัวหน้า (level 1) ; พัสดุตรวจสอบแล้ว → ผอ. (level 3)
 * ส่งไม่สำเร็จ/ไม่มี telegram_id = คืน false เงียบ ๆ ไม่ขัดการบันทึก
 */
class PurchaseTelegramService
{
    public function notifyPendingApprove(Order $order, Approve $approve): bool
    {
        try {
            if ($approve->status !== 'Pending' || empty($approve->emp_id)) {
                return false;
            }
            $employee = Employees::findOne($approve->emp_id);
            $chatId = ($employee && $employee->user) ? $employee->user->telegram_id : null;
            if (empty($chatId)) {
                return false;
            }

            $requester = Employees::findOne(['user_id' => $order->created_by]);
            $dj = is_array($order->data_json) ? $order->data_json : [];
            $check = $dj['plan_check']['message'] ?? '';
            $amount = PurchasePlanControl::orderAmount($order);
            $step = $approve->title ?: (($approve->data_json['label'] ?? '') ?: 'ผู้อนุมัติ');

            $e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES);
            $lines = [
                '🛒 <b>ขออนุมัติจัดซื้อจัดจ้าง' . ($order->request_type === 'unplanned' ? ' (นอกแผน)' : '') . '</b>',
                '',
                'เลขที่: ' . $e($order->pr_number ?: '-'),
                'ผู้ขอ: ' . $e($requester ? $requester->fullname : '-'),
                'ยอดเงิน: ' . number_format($amount, 2) . ' บาท',
            ];
            if (!empty($dj['comment'])) {
                $lines[] = 'เหตุผล: ' . $e(mb_substr(strip_tags($dj['comment']), 0, 100));
            }
            if ($check !== '') {
                $lines[] = 'ผลตรวจแผน: ' . $e(mb_substr($check, 0, 200));
            }
            $lines[] = '';
            $lines[] = 'ขั้นตอน: ' . $e($step);
            $lines[] = 'เข้าระบบ ERP เพื่อพิจารณา';

            return (bool) Yii::$app->telegram->sendDirectMessage($chatId, implode("\n", $lines));
        } catch (\Throwable $e) {
            Yii::warning('Purchase telegram notify failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /** แจ้งผู้อนุมัติขั้นที่ระบุของใบ (ถ้าสถานะ Pending) */
    public function notifyLevel(Order $order, int $level): bool
    {
        $approve = Approve::findOne(['from_id' => $order->id, 'name' => 'purchase', 'level' => $level]);
        return $approve ? $this->notifyPendingApprove($order, $approve) : false;
    }
}

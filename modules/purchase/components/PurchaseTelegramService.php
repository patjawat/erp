<?php

namespace app\modules\purchase\components;

use Yii;
use app\models\Categorise;
use app\modules\hr\models\Employees;
use app\modules\approve\models\Approve;
use app\modules\purchase\models\Order;
use app\modules\usermanager\models\User;
use app\modules\telegrambot\components\TelegramBot;

/**
 * อนุมัติใบขอซื้อผ่าน Telegram (ส่งตรงถึง user.telegram_id แบบ LeaveTelegramService)
 *
 * ใบนอกแผน (ปีผูกแผน): ส่งคำขอ → หัวหน้า (level 1) ; พัสดุตรวจสอบแล้ว → ผอ. (level 3)
 * ข้อความมีปุ่ม ✅ อนุมัติ / ❌ ไม่อนุมัติ → Telegram ส่ง callback_query เข้า /telegrambot/webhook
 * → handleCallback() ตรวจลายเซ็น + ผู้กดต้องเป็นผู้อนุมัติขั้นนั้นจริง + ยัง Pending แล้วค่อยเดินขั้น
 * การเดินขั้นเหมือนหน้าอนุมัติบนเว็บ (approve/approveV2 PurchaseController):
 *   Pass: level 3 → ใบ status 2 ; ขั้นอื่น → ขั้นถัดไป Pending
 *   Reject: หยุดที่ขั้นนั้น (ไม่เปิดขั้นถัดไป) + แจ้งผู้ขอ
 * ไม่อนุมัติต้องกดยืนยันซ้ำ (กันกดพลาด)
 *
 * ⚠️ callback ไปที่ webhook ที่ตั้งไว้กับบอท (เครื่อง production) — ทดสอบปุ่มได้หลัง deploy
 */
class PurchaseTelegramService
{
    const PREFIX = 'pa';

    /** ส่งแจ้งผู้อนุมัติขั้นนี้ (Pending) พร้อมปุ่มอนุมัติ */
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

            $options = [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => '✅ ' . $this->actionLabel($approve), 'callback_data' => $this->callbackData($approve->id, 'A')],
                        ['text' => '❌ ไม่' . $this->actionLabel($approve), 'callback_data' => $this->callbackData($approve->id, 'R')],
                    ]],
                ],
            ];

            return (bool) Yii::$app->telegram->sendDirectMessage($chatId, $this->buildMessage($order, $approve), $options);
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

    /** callback_query ของปุ่มงานจัดซื้อไหม (ให้ webhook ส่งต่อมาที่นี่) */
    public static function isOwnCallback(?string $data): bool
    {
        return is_string($data) && strpos($data, self::PREFIX . ':') === 0;
    }

    /**
     * จัดการการกดปุ่มจาก Telegram
     * @param array $cq update['callback_query']
     */
    public function handleCallback(array $cq, TelegramBot $bot): void
    {
        $cqId = $cq['id'] ?? '';
        $fromId = (string) ($cq['from']['id'] ?? '');
        $chatId = (string) ($cq['message']['chat']['id'] ?? '');
        $messageId = $cq['message']['message_id'] ?? null;
        $originalText = (string) ($cq['message']['text'] ?? '');

        $parsed = $this->parseCallback((string) ($cq['data'] ?? ''));
        if (!$parsed) {
            $bot->answerCallbackQuery($cqId, 'ปุ่มนี้ไม่ถูกต้องหรือหมดอายุ', true);
            return;
        }
        [$approveId, $act] = $parsed;

        $approve = Approve::findOne(['id' => $approveId, 'name' => 'purchase']);
        $order = $approve ? Order::findOne($approve->from_id) : null;
        if (!$approve || !$order) {
            $bot->answerCallbackQuery($cqId, 'ไม่พบใบขอซื้อ', true);
            return;
        }

        // ผู้กดต้องเป็นผู้อนุมัติขั้นนี้จริง (ผูก Telegram กับบัญชีพนักงานคนนั้น)
        $user = $fromId !== '' ? User::findOne(['telegram_id' => $fromId]) : null;
        $employee = $user ? Employees::findOne(['user_id' => $user->id]) : null;
        if (!$employee || (string) $employee->id !== (string) $approve->emp_id) {
            $bot->answerCallbackQuery($cqId, 'คุณไม่ใช่ผู้อนุมัติของขั้นนี้', true);
            return;
        }

        if ($approve->status !== 'Pending') {
            $bot->answerCallbackQuery($cqId, 'รายการนี้ดำเนินการไปแล้ว (' . $approve->status . ')', true);
            $this->closeMessage($bot, $chatId, $messageId, $originalText, 'ℹ️ ดำเนินการไปแล้ว');
            return;
        }

        switch ($act) {
            case 'R': // ถามยืนยันก่อนไม่อนุมัติ
                $bot->answerCallbackQuery($cqId, 'กดยืนยันอีกครั้งเพื่อไม่' . $this->actionLabel($approve));
                $bot->editMessageText($chatId, $messageId, htmlspecialchars($originalText, ENT_QUOTES) . "\n\n⚠️ <b>ยืนยันไม่" . $this->actionLabel($approve) . '?</b>', [
                    'reply_markup' => ['inline_keyboard' => [[
                        ['text' => '❌ ยืนยันไม่' . $this->actionLabel($approve), 'callback_data' => $this->callbackData($approve->id, 'X')],
                        ['text' => '↩️ ย้อนกลับ', 'callback_data' => $this->callbackData($approve->id, 'B')],
                    ]]],
                ]);
                return;

            case 'B': // กลับไปปุ่มเดิม
                $bot->answerCallbackQuery($cqId);
                $bot->editMessageText($chatId, $messageId, $this->buildMessage($order, $approve), [
                    'reply_markup' => ['inline_keyboard' => [[
                        ['text' => '✅ ' . $this->actionLabel($approve), 'callback_data' => $this->callbackData($approve->id, 'A')],
                        ['text' => '❌ ไม่' . $this->actionLabel($approve), 'callback_data' => $this->callbackData($approve->id, 'R')],
                    ]]],
                ]);
                return;

            case 'A':
            case 'X':
                $ok = $act === 'A' ? $this->approve($order, $approve, $employee) : $this->reject($order, $approve, $employee);
                if (!$ok) {
                    $bot->answerCallbackQuery($cqId, 'บันทึกไม่สำเร็จ กรุณาทำรายการในระบบ ERP', true);
                    return;
                }
                $result = $act === 'A'
                    ? '✅ <b>' . $this->actionLabel($approve) . 'แล้ว</b>'
                    : '❌ <b>ไม่' . $this->actionLabel($approve) . '</b>';
                $bot->answerCallbackQuery($cqId, $act === 'A' ? 'บันทึกแล้ว' : 'บันทึกไม่อนุมัติแล้ว');
                $this->closeMessage($bot, $chatId, $messageId, $originalText,
                    $result . ' โดย ' . htmlspecialchars($employee->fullname, ENT_QUOTES) . ' '
                    . date('d/m/') . ((int) date('Y') + 543) . date(' H:i') . ' (ผ่าน Telegram)');
                return;
        }

        $bot->answerCallbackQuery($cqId);
    }

    /** อนุมัติขั้นนี้ แล้วเดินขั้นต่อแบบหน้าอนุมัติบนเว็บ */
    protected function approve(Order $order, Approve $approve, Employees $by): bool
    {
        $tx = Yii::$app->db->beginTransaction();
        try {
            $this->stamp($approve, 'Pass', $by);
            if ((int) $approve->level === 3) {
                $order->status = 2;
                $order->save(false);
            } else {
                $next = Approve::findOne(['from_id' => $approve->from_id, 'name' => 'purchase', 'level' => (int) $approve->level + 1]);
                if ($next && $next->status !== 'Pass') {
                    $next->status = 'Pending';
                    $next->save(false);
                }
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            Yii::error('Purchase telegram approve failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }

        if ((int) $approve->level === 3) {
            $this->notifyRequester($order, '✅ ใบขอซื้อ ' . ($order->pr_number ?: '') . ' ได้รับการอนุมัติแล้ว — พัสดุลงทะเบียนคุมต่อได้');
        }
        return true;
    }

    /** ไม่อนุมัติ: หยุดที่ขั้นนี้ + แจ้งผู้ขอ */
    protected function reject(Order $order, Approve $approve, Employees $by): bool
    {
        try {
            $this->stamp($approve, 'Reject', $by);
        } catch (\Throwable $e) {
            Yii::error('Purchase telegram reject failed: ' . $e->getMessage(), __METHOD__);
            return false;
        }
        $this->notifyRequester($order, '❌ ใบขอซื้อ ' . ($order->pr_number ?: '') . ' ไม่ผ่านขั้น "' . ($approve->title ?: 'อนุมัติ')
            . '" โดย ' . $by->fullname);
        return true;
    }

    protected function stamp(Approve $approve, string $status, Employees $by): void
    {
        $dj = is_array($approve->data_json) ? $approve->data_json : (json_decode((string) $approve->data_json, true) ?: []);
        $dj['approve_date'] = date('Y-m-d H:i:s');
        $dj['via'] = 'telegram';
        $approve->data_json = $dj;
        $approve->emp_id = $by->id;
        $approve->status = $status;
        $approve->save(false);
    }

    protected function notifyRequester(Order $order, string $text): void
    {
        try {
            $user = User::findOne($order->created_by);
            if ($user && !empty($user->telegram_id)) {
                Yii::$app->telegram->sendDirectMessage($user->telegram_id, htmlspecialchars($text, ENT_QUOTES));
            }
        } catch (\Throwable $e) {
            Yii::warning('Purchase telegram requester notify failed: ' . $e->getMessage(), __METHOD__);
        }
    }

    protected function closeMessage(TelegramBot $bot, string $chatId, $messageId, string $originalText, string $resultLine): void
    {
        if ($chatId === '' || !$messageId) {
            return;
        }
        // ไม่ส่ง reply_markup = ปุ่มหาย กดซ้ำไม่ได้
        $bot->editMessageText($chatId, $messageId, htmlspecialchars($originalText, ENT_QUOTES) . "\n\n" . $resultLine);
    }

    protected function buildMessage(Order $order, Approve $approve): string
    {
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
        $items = $order->ListOrderItems();
        if ($items) {
            $names = array_map(fn($i) => $i->product->title ?? ($i->data_json['asset_name'] ?? '-'), array_slice($items, 0, 3));
            $lines[] = 'รายการ: ' . $e(implode(', ', $names)) . (count($items) > 3 ? ' และอีก ' . (count($items) - 3) . ' รายการ' : '');
        }
        if (!empty($dj['comment'])) {
            $lines[] = 'เหตุผล: ' . $e(mb_substr(strip_tags($dj['comment']), 0, 100));
        }
        if ($check !== '') {
            $lines[] = 'ผลตรวจแผน: ' . $e(mb_substr($check, 0, 200));
        }
        $lines[] = '';
        $lines[] = 'ขั้นตอน: ' . $e($step);
        return implode("\n", $lines);
    }

    /** level 1 = เห็นชอบ ; อื่น ๆ = อนุมัติ */
    protected function actionLabel(Approve $approve): string
    {
        return (int) $approve->level === 1 ? 'เห็นชอบ' : 'อนุมัติ';
    }

    /** callback_data ≤ 64 bytes: pa:{approveId}:{A|R|X|B}:{sig16} — ลงชื่อด้วย HMAC กันปลอมปุ่ม */
    protected function callbackData(int $approveId, string $act): string
    {
        return self::PREFIX . ':' . $approveId . ':' . $act . ':' . $this->sign($approveId . ':' . $act);
    }

    /** @return array{0:int,1:string}|null */
    protected function parseCallback(string $data): ?array
    {
        $parts = explode(':', $data);
        if (count($parts) !== 4 || $parts[0] !== self::PREFIX || !ctype_digit($parts[1]) || !in_array($parts[2], ['A', 'R', 'X', 'B'], true)) {
            return null;
        }
        if (!hash_equals($this->sign($parts[1] . ':' . $parts[2]), $parts[3])) {
            return null;
        }
        return [(int) $parts[1], $parts[2]];
    }

    protected function sign(string $payload): string
    {
        return substr(hash_hmac('sha256', self::PREFIX . ':' . $payload, $this->secret()), 0, 16);
    }

    /** กุญแจลงชื่อ = bot token (ความลับที่มีทั้งฝั่งส่งและฝั่ง webhook) */
    protected function secret(): string
    {
        $setting = Categorise::findOne(['name' => 'telegram_setting']);
        $dj = $setting ? (is_array($setting->data_json) ? $setting->data_json : (json_decode((string) $setting->data_json, true) ?: [])) : [];
        $key = (string) ($dj['bot_token'] ?? $dj['token'] ?? '');
        if ($key === '') {
            throw new \RuntimeException('Telegram bot token not configured');
        }
        return $key;
    }
}

<?php

namespace app\modules\telegrambot\controllers;

use app\models\Categorise;
use app\modules\usermanager\models\User;
use app\modules\telegrambot\components\TelegramBot;
use app\modules\approveV2\models\Approve;
use app\modules\leave\components\LeaveApprovalService;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
        $model = Categorise::findOne(['name' => 'telegram_setting']);
        if (!$model) {
            $model = new Categorise();
            $model->name = 'telegram_setting';
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->data_json = json_encode($model->data_json);
            $model->save(false);

            Yii::$app->session->setFlash('success', 'บันทึกสำเร็จ');
            return $this->refresh();
        }

        $model->data_json = json_decode((string) ($model->data_json ?? ''), true);

        $bindings = User::find()
            ->alias('u')
            ->joinWith(['employee e'])
            ->andWhere(['IS NOT', 'u.telegram_id', null])
            ->andWhere(['<>', 'u.telegram_id', ''])
            ->orderBy(['e.department' => SORT_ASC, 'e.fname' => SORT_ASC, 'e.lname' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'model' => $model,
            'bindings' => $bindings,
        ]);
    }

    public function actionTestUser($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = User::find()
            ->with(['employee'])
            ->andWhere(['id' => (int) $id])
            ->one();
        if (!$user) {
            throw new NotFoundHttpException('ไม่พบผู้ใช้งาน');
        }

        if (empty($user->telegram_id)) {
            return [
                'status' => 'error',
                'message' => 'ผู้ใช้นี้ยังไม่ได้ผูก Telegram ID',
            ];
        }

        $employee = $user->employee;
        $messageLines = [
            'ทดสอบส่งข้อความจากระบบ ERP',
            'ชื่อ: ' . trim((string) ($employee->fullname ?? $user->fullname ?? $user->username)),
        ];
        if ($employee) {
            $messageLines[] = 'แผนก: ' . ($employee->departmentName() ?: '-');
            $messageLines[] = 'ตำแหน่ง: ' . ($employee->positionName() ?: '-');
        }
        $messageLines[] = 'Telegram ID: ' . $user->telegram_id;
        $messageLines[] = 'เวลา: ' . date('d/m/Y H:i:s');

        $telegram = Yii::$app->telegram;
        $sent = $telegram->sendDirectMessage($user->telegram_id, implode("\n", $messageLines));
        if (!$sent) {
            return [
                'status' => 'error',
                'message' => $telegram->getLastError() ?: 'ส่งข้อความไม่สำเร็จ กรุณาตรวจสอบ bot token หรือ Telegram ID',
            ];
        }

        return [
            'status' => 'success',
            'message' => 'ส่งข้อความทดสอบไปยัง Telegram สำเร็จ',
        ];
    }

     public $enableCsrfValidation = false; // ปิด CSRF สำหรับ webhook

    public function actionWebhook()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            return ['status' => 'invalid_input'];
        }

        // ── callback_query (ปุ่ม inline keyboard) ─────────────────
        if (isset($input['callback_query'])) {
            $this->handleCallbackQuery($input['callback_query']);
            return ['status' => 'ok'];
        }

        // ── ข้อความปกติ ───────────────────────────────────────────
        if (isset($input['message'])) {
            $chatId = $input['message']['chat']['id'] ?? null;
            $text   = $input['message']['text'] ?? '';
            if ($chatId) {
                $telegram = new TelegramBot();
                $telegram->sendMessage($chatId, 'คุณส่งข้อความว่า: ' . $text);
            }
            return ['status' => 'ok'];
        }

        return ['status' => 'no_message'];
    }

    /**
     * จัดการ callback จากปุ่ม inline keyboard
     * รูปแบบ callback_data: leave_approve:{approve_id}:{Pass|Reject}
     */
    protected function handleCallbackQuery(array $query): void
    {
        $callbackQueryId = $query['id'] ?? null;
        $chatId          = $query['message']['chat']['id'] ?? null;
        $messageId       = $query['message']['message_id'] ?? null;
        // telegram_id จาก Telegram เป็น integer — แปลงเป็น string เพื่อเปรียบเทียบ
        $fromTelegramId  = (string) ($query['from']['id'] ?? '');
        $data            = $query['data'] ?? '';

        $telegram = Yii::$app->telegram;

        // ── answer callback ก่อนเสมอ เพื่อปิด loading icon บนปุ่ม
        $this->answerCallbackQuery($callbackQueryId);

        if (!$chatId || $fromTelegramId === '' || $data === '') {
            return;
        }

        // ── ตรวจ pattern: leave_approve:{id}:{Pass|Reject} ─────────
        if (!preg_match('/^leave_approve:(\d+):(Pass|Reject)$/', $data, $m)) {
            return;
        }

        $approveId = (int) $m[1];
        $action    = $m[2];

        // ── หา approve record ──────────────────────────────────────
        $approve = Approve::find()
            ->where(['id' => $approveId, 'name' => 'leave'])
            ->with(['employee.user'])
            ->one();

        if (!$approve) {
            $telegram->sendDirectMessage($chatId, '❌ ไม่พบรายการอนุมัติ');
            return;
        }

        // ── ตรวจสิทธิ์: เทียบ telegram_id (string) ─────────────────
        $employee           = $approve->employee;
        $user               = $employee ? $employee->user : null;
        $approverTelegramId = $user ? (string) ($user->telegram_id ?? '') : '';

        if ($approverTelegramId === '' || $approverTelegramId !== $fromTelegramId) {
            // แจ้งเตือนสั้น ๆ ผ่าน answerCallbackQuery (popup) แทน sendMessage
            $this->answerCallbackQuery($callbackQueryId, '⛔ คุณไม่มีสิทธิ์อนุมัติรายการนี้');
            return;
        }

        // ── ตรวจสถานะ ─────────────────────────────────────────────
        if ($approve->status !== 'Pending') {
            $done = match ($approve->status) {
                'Pass'   => 'เห็นชอบ/อนุมัติแล้ว',
                'Reject' => 'ไม่เห็นชอบ/ไม่อนุมัติแล้ว',
                default  => 'ดำเนินการแล้ว (' . $approve->status . ')',
            };
            $this->answerCallbackQuery($callbackQueryId, 'ℹ️ รายการนี้' . $done);
            return;
        }

        // ── label ตามระดับ ─────────────────────────────────────────
        $isFinal     = (bool) $approve->maxLevel();
        $passLabel   = $isFinal ? 'อนุมัติ'    : 'เห็นชอบ';
        $rejectLabel = $isFinal ? 'ไม่อนุมัติ' : 'ไม่เห็นชอบ';
        $actLabel    = $action === 'Pass' ? $passLabel : $rejectLabel;

        // ── ประมวลผลการอนุมัติ ─────────────────────────────────────
        $actorEmpId = $employee ? $employee->id : null;
        $result     = (new LeaveApprovalService())->process($approve, $action, $actorEmpId);

        if (!$result['ok']) {
            $telegram->sendDirectMessage($chatId, '❌ เกิดข้อผิดพลาด: ' . ($result['message'] ?? ''));
            return;
        }

        $leave         = $result['leave'] ?? null;
        $requesterName = $leave ? ($leave->employee->fullname ?? '-') : '-';
        $leaveType     = $leave ? ($leave->leaveType->title ?? 'ใบลา') : 'ใบลา';
        $levelLabel    = is_array($approve->data_json)
            ? ($approve->data_json['label'] ?? $approve->title ?? '')
            : ($approve->title ?? '');

        // ── แก้ข้อความเดิม: แสดงผลลัพธ์ + ลบปุ่ม ─────────────────
        if ($messageId) {
            $icon    = $action === 'Pass' ? '✅' : '❌';
            $newText = $icon . ' <b>' . $actLabel . 'แล้ว</b>' . "\n\n"
                     . '👤 ผู้ขอ: '   . htmlspecialchars($requesterName, ENT_QUOTES) . "\n"
                     . '📌 ประเภท: '  . htmlspecialchars($leaveType, ENT_QUOTES)     . "\n"
                     . '🔖 ขั้นตอน: ' . htmlspecialchars($levelLabel, ENT_QUOTES)    . "\n"
                     . '🕐 เมื่อ: '   . date('d/m/Y H:i');
            $this->editMessageText($chatId, $messageId, $newText);
        }

        // ── popup แจ้งยืนยันบนปุ่มที่กด ───────────────────────────
        $icon = $action === 'Pass' ? '✅' : '❌';
        $this->answerCallbackQuery($callbackQueryId, $icon . ' ' . $actLabel . 'แล้ว');
    }

    /**
     * ตอบกลับ callback_query — ปิด loading icon บนปุ่ม
     * ถ้ามี $text จะแสดงเป็น popup toast บนปุ่ม
     */
    protected function answerCallbackQuery(?string $callbackQueryId, string $text = ''): void
    {
        if (!$callbackQueryId) return;

        $token = $this->resolveBotToken();
        if ($token === '') return;

        try {
            $payload = ['callback_query_id' => $callbackQueryId];
            if ($text !== '') {
                $payload['text']       = $text;
                $payload['show_alert'] = false; // true = modal, false = toast บนปุ่ม
            }
            $client = new \yii\httpclient\Client(['baseUrl' => "https://api.telegram.org/bot{$token}/"]);
            $client->createRequest()
                ->setMethod('POST')
                ->setUrl('answerCallbackQuery')
                ->setFormat(\yii\httpclient\Client::FORMAT_URLENCODED)
                ->setData($payload)
                ->send();
        } catch (\Throwable $e) {
            Yii::warning('answerCallbackQuery failed: ' . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * ดึง bot token จาก telegram_setting
     */
    protected function resolveBotToken(): string
    {
        $setting = Categorise::findOne(['name' => 'telegram_setting']);
        $data    = is_array($setting->data_json ?? null) ? $setting->data_json
                 : (is_string($setting->data_json ?? null) ? json_decode($setting->data_json, true) : []);
        return trim((string) ($data['bot_token'] ?? $data['token'] ?? ''));
    }

    /**
     * แก้ไขข้อความเดิมใน chat (ลบปุ่ม inline keyboard ออก หลังดำเนินการแล้ว)
     */
    protected function editMessageText(string $chatId, int $messageId, string $text): void
    {
        $token = $this->resolveBotToken();
        if ($token === '') return;

        try {
            $client = new \yii\httpclient\Client(['baseUrl' => "https://api.telegram.org/bot{$token}/"]);
            $client->createRequest()
                ->setMethod('POST')
                ->setUrl('editMessageText')
                ->setFormat(\yii\httpclient\Client::FORMAT_URLENCODED)
                ->setData([
                    'chat_id'      => $chatId,
                    'message_id'   => $messageId,
                    'text'         => $text,
                    'parse_mode'   => 'HTML',
                    'reply_markup' => json_encode(['inline_keyboard' => []], JSON_UNESCAPED_UNICODE),
                ])
                ->send();
        } catch (\Throwable $e) {
            Yii::warning('editMessageText failed: ' . $e->getMessage(), __METHOD__);
        }
    }

}


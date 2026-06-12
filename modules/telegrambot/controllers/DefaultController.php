<?php

namespace app\modules\telegrambot\controllers;

use app\models\Categorise;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\filemanager\models\Uploads;
use app\modules\usermanager\models\User;
use app\modules\telegrambot\components\TelegramBot;
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
            $postedData = is_array($model->data_json ?? null) ? $model->data_json : [];
            $existingData = $this->normalizeDataJson($model->getOldAttribute('data_json'));
            $model->data_json = json_encode(
                array_merge($existingData, $postedData),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่า Telegram สำเร็จ');
                return $this->refresh();
            }

            Yii::$app->session->setFlash('error', 'บันทึกการตั้งค่า Telegram ไม่สำเร็จ');
        }

        $model->code = $model->code ?: 'telegram_setting';
        $model->title = $model->title ?: 'Telegram Notification';
        $model->data_json = $this->normalizeDataJson($model->data_json ?? null);
        $qrcodeUploadRef = 'telegrambot_group_qrcode';
        $qrcodeUploadName = 'group_qrcode';
        $qrcodeUpload = null;
        $qrcodeUploadId = (int) ($model->data_json['group_qrcode_id'] ?? 0);
        if ($qrcodeUploadId > 0) {
            $qrcodeUpload = Uploads::findOne($qrcodeUploadId);
        }
        if (!$qrcodeUpload) {
            $qrcodeUpload = Uploads::find()
                ->where(['ref' => $qrcodeUploadRef, 'name' => $qrcodeUploadName])
                ->orderBy(['id' => SORT_DESC])
                ->one();
        }
        $qrcodePreviewUrl = $qrcodeUpload ? FileManagerHelper::getImg($qrcodeUpload->id) : '';

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
            'qrcodeUploadRef' => $qrcodeUploadRef,
            'qrcodeUploadName' => $qrcodeUploadName,
            'qrcodeUploadId' => $qrcodeUpload ? (int) $qrcodeUpload->id : null,
            'qrcodePreviewUrl' => $qrcodePreviewUrl,
        ]);
    }

    public function actionTestBot()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = trim((string) Yii::$app->request->post('bot_token', ''));
        $telegram = new TelegramBot($botToken !== '' ? $botToken : null);
        $result = $telegram->getMe();
        if (!$result) {
            $errorMessage = $telegram->getLastError() ?: 'ไม่สามารถตรวจสอบการเชื่อมต่อ Telegram ได้';
            $errorType = preg_match('/(unauthorized|401|token)/i', $errorMessage) ? 'invalid_token' : 'disconnected';
            return [
                'status' => 'error',
                'error_type' => $errorType,
                'message' => $errorMessage,
            ];
        }

        $bot = $result['result'] ?? [];
        $username = trim((string) ($bot['username'] ?? ''));
        $name = trim((string) (($bot['first_name'] ?? '') . ' ' . ($bot['last_name'] ?? '')));
        $details = [];
        if ($name !== '') {
            $details[] = $name;
        }
        if ($username !== '') {
            $details[] = '@' . ltrim($username, '@');
        }

        return [
            'status' => 'success',
            'message' => 'เชื่อมต่อกับ Telegram ได้แล้ว' . ($details ? ' (' . implode(' ', $details) . ')' : ''),
            'bot_name' => $name,
            'bot_username' => $username,
        ];
    }

    public function actionTestGroup()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $mode = trim((string) Yii::$app->request->post('mode', 'check'));
        $botToken = trim((string) Yii::$app->request->post('bot_token', ''));
        $groupChatId = trim((string) Yii::$app->request->post('group_chat_id', ''));

        if ($botToken === '') {
            return [
                'status' => 'error',
                'error_type' => 'invalid_token',
                'message' => 'ไม่พบ bot token ใน Telegram Settings',
            ];
        }

        if ($groupChatId === '') {
            return [
                'status' => 'error',
                'error_type' => 'disconnected',
                'message' => 'กรุณาระบุ Chat ID ของกลุ่ม Telegram ก่อน',
            ];
        }

        $telegram = new TelegramBot($botToken);

        if ($mode === 'send') {
            $sent = $telegram->sendMessage($groupChatId, "ทดสอบการแจ้งเตือนจากระบบ ERP\nเวลา: " . date('d/m/Y H:i:s'));
            if (!$sent) {
                $errorMessage = $telegram->getLastError() ?: 'ส่งข้อความทดสอบไปยังกลุ่มไม่สำเร็จ';
                $errorType = preg_match('/(unauthorized|401|token)/i', $errorMessage) ? 'invalid_token' : 'disconnected';
                return [
                    'status' => 'error',
                    'error_type' => $errorType,
                    'message' => $errorMessage,
                ];
            }

            return [
                'status' => 'success',
                'message' => 'ส่งข้อความทดสอบไปยังกลุ่ม Telegram สำเร็จ',
            ];
        }

        $chatResult = $telegram->getChat($groupChatId);
        if (!$chatResult) {
            $errorMessage = $telegram->getLastError() ?: 'ตรวจสอบกลุ่ม Telegram ไม่สำเร็จ';
            $errorType = preg_match('/(unauthorized|401|token)/i', $errorMessage) ? 'invalid_token' : 'disconnected';
            return [
                'status' => 'error',
                'error_type' => $errorType,
                'message' => $errorMessage,
            ];
        }

        $chat = $chatResult['result'] ?? [];
        $groupTitle = trim((string) ($chat['title'] ?? $chat['first_name'] ?? $chat['username'] ?? $groupChatId));
        $chatType = trim((string) ($chat['type'] ?? ''));
        $groupUsername = trim((string) ($chat['username'] ?? ''));
        $groupLink = trim((string) ($chat['invite_link'] ?? ''));
        if ($groupLink === '' && $groupUsername !== '') {
            $groupLink = 'https://t.me/' . ltrim($groupUsername, '@');
        }
        $memberCount = null;
        $memberCountResult = $telegram->getChatMemberCount($groupChatId);
        if ($memberCountResult && isset($memberCountResult['result'])) {
            $memberCount = (int) $memberCountResult['result'];
        }

        $message = 'เชื่อมต่อกับกลุ่ม Telegram ได้แล้ว';
        if ($groupTitle !== '') {
            $message .= ' (' . $groupTitle . ')';
        }

        return [
            'status' => 'success',
            'message' => $message,
            'group_title' => $groupTitle,
            'group_type' => $chatType,
            'group_username' => $groupUsername,
            'group_link' => $groupLink,
            'member_count' => $memberCount,
            'chat_id' => $groupChatId,
        ];
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
        $employeeName = $employee ? ($employee->fullname ?? '') : '';
        $messageLines = [
            'ทดสอบส่งข้อความจากระบบ ERP',
            'ชื่อ: ' . htmlspecialchars(trim((string) ($employeeName !== '' ? $employeeName : ($user->fullname ?? $user->username))), ENT_QUOTES),
        ];
        if ($employee) {
            $messageLines[] = 'แผนก: ' . htmlspecialchars($employee->departmentName() ?: '-', ENT_QUOTES);
            $messageLines[] = 'ตำแหน่ง: ' . htmlspecialchars($employee->positionName() ?: '-', ENT_QUOTES);
        }
        $messageLines[] = 'Telegram ID: ' . htmlspecialchars((string) $user->telegram_id, ENT_QUOTES);
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

    protected function normalizeDataJson($data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            return json_decode($data, true) ?: [];
        }

        return [];
    }

}

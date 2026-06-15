<?php

namespace app\modules\telegrambot\controllers;

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
        return $this->redirect(['/telegrambot/settings/index']);
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

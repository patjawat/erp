<?php

namespace app\modules\telegrambot\controllers;

use app\models\Categorise;
use app\modules\telegrambot\components\TelegramBot;
use app\modules\usermanager\models\User;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\web\Response;

class SettingsController extends Controller
{
    public function actionIndex()
    {
        $model = $this->findSettingsModel();

        if ($model->load(Yii::$app->request->post())) {
            $postedData = is_array($model->data_json ?? null) ? $model->data_json : [];
            $existingData = $this->normalizeDataJson($model->getOldAttribute('data_json'));
            $mergedData = $this->normalizeSettingsData(array_merge($existingData, $postedData));
            $uploadResult = $this->applyBotQrImageUpload($mergedData, $existingData);

            if (!$uploadResult['success']) {
                Yii::$app->session->setFlash('error', $uploadResult['message']);
                $model->data_json = $mergedData;
            } else {
                $mergedData = $uploadResult['data'];

                $model->name = 'telegram_setting';
                $model->code = $model->code ?: 'telegram_setting';
                $model->title = $model->title ?: 'Telegram Personal Notification';
                $model->data_json = json_encode($mergedData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                if ($model->save(false)) {
                    foreach ($uploadResult['delete_after_save'] as $path) {
                        $this->deleteBotQrImageFile($path);
                    }
                    Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่า Telegram สำเร็จ');
                    return $this->refresh();
                }

                foreach ($uploadResult['delete_on_failure'] as $path) {
                    $this->deleteBotQrImageFile($path);
                }
                Yii::$app->session->setFlash('error', 'บันทึกการตั้งค่า Telegram ไม่สำเร็จ');
            }
        }

        $data = $this->normalizeSettingsData($this->normalizeDataJson($model->data_json ?? null));
        $model->code = $model->code ?: 'telegram_setting';
        $model->title = $model->title ?: 'Telegram Personal Notification';
        $model->data_json = $data;

        $bindings = $this->findLinkedUsers();
        $activeUserCount = User::find()->andWhere(['status' => User::STATUS_ACTIVE])->count();

        return $this->render('index', [
            'model' => $model,
            'data' => $data,
            'bindings' => $bindings,
            'activeUserCount' => (int) $activeUserCount,
            'linkedUserCount' => count($bindings),
            'defaultWebhookUrl' => Url::to(['/telegrambot/webhook/index'], true),
        ]);
    }

    public function actionTestBot()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = trim((string) Yii::$app->request->post('bot_token', ''));
        $telegram = new TelegramBot($botToken !== '' ? $botToken : null);
        $result = $telegram->getMe();
        if (!$result) {
            return $this->jsonError($telegram->getLastError() ?: 'ไม่สามารถตรวจสอบการเชื่อมต่อ Telegram ได้');
        }

        $bot = $result['result'] ?? [];
        $username = trim((string) ($bot['username'] ?? ''));
        $name = trim((string) (($bot['first_name'] ?? '') . ' ' . ($bot['last_name'] ?? '')));

        return [
            'status' => 'success',
            'message' => 'เชื่อมต่อกับ Telegram Bot สำเร็จ' . ($username !== '' ? ' (@' . ltrim($username, '@') . ')' : ''),
            'bot_name' => $name,
            'bot_username' => $username,
        ];
    }

    public function actionSetWebhook()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = trim((string) Yii::$app->request->post('bot_token', ''));
        $webhookUrl = trim((string) Yii::$app->request->post('webhook_url', ''));
        if ($botToken === '') {
            return $this->jsonError('กรุณาระบุ Bot Token ก่อนตั้งค่า Webhook', 'invalid_token');
        }
        if (!$this->isValidPublicHttpsUrl($webhookUrl)) {
            return $this->jsonError('Webhook URL ต้องเป็น https ที่เข้าถึงได้จากภายนอก', 'invalid_url');
        }

        $telegram = new TelegramBot($botToken);
        $result = $telegram->setWebhook($webhookUrl);
        if (!$result) {
            return $this->jsonError($telegram->getLastError() ?: 'ตั้งค่า Webhook ไม่สำเร็จ');
        }

        return [
            'status' => 'success',
            'message' => 'ตั้งค่า Webhook สำเร็จ',
            'webhook_url' => $webhookUrl,
        ];
    }

    public function actionTestMessage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = trim((string) Yii::$app->request->post('bot_token', ''));
        try {
            $user = $this->findTestUser((int) Yii::$app->request->post('user_id', 0));
        } catch (NotFoundHttpException $e) {
            return $this->jsonError($e->getMessage(), 'user_not_linked');
        }

        $telegram = new TelegramBot($botToken !== '' ? $botToken : null);
        $sent = $telegram->sendMessage($user->telegram_id, implode("\n", [
            'ทดสอบแจ้งเตือนรายบุคคลจากระบบ ERP',
            'ผู้รับ: ' . $this->resolveUserDisplayName($user),
            'เวลา: ' . date('d/m/Y H:i:s'),
        ]), [
            'parse_mode' => 'HTML',
        ]);

        if (!$sent) {
            return $this->jsonError($telegram->getLastError() ?: 'ส่งข้อความทดสอบไม่สำเร็จ');
        }

        return [
            'status' => 'success',
            'message' => 'ส่งข้อความทดสอบรายบุคคลสำเร็จ',
        ];
    }

    public function actionTestMiniApp()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = trim((string) Yii::$app->request->post('bot_token', ''));
        $miniAppBaseUrl = trim((string) Yii::$app->request->post('mini_app_base_url', ''));
        if (!$this->isValidPublicHttpsUrl($miniAppBaseUrl)) {
            return $this->jsonError('Mini App Base URL ต้องเป็น https ที่เข้าถึงได้จากภายนอก', 'invalid_url');
        }

        try {
            $user = $this->findTestUser((int) Yii::$app->request->post('user_id', 0));
        } catch (NotFoundHttpException $e) {
            return $this->jsonError($e->getMessage(), 'user_not_linked');
        }

        $telegram = new TelegramBot($botToken !== '' ? $botToken : null);
        $sent = $telegram->sendMessage($user->telegram_id, 'ทดสอบปุ่มเปิด ERP Mobile ผ่าน Telegram Mini App', [
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'เปิด ERP Mobile',
                            'web_app' => [
                                'url' => rtrim($miniAppBaseUrl, '/'),
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        if (!$sent) {
            return $this->jsonError($telegram->getLastError() ?: 'ส่งปุ่ม Mini App ไม่สำเร็จ');
        }

        return [
            'status' => 'success',
            'message' => 'ส่งปุ่ม Mini App ไปยังผู้ใช้สำเร็จ',
        ];
    }

    public function actionBotQr(int $download = 0)
    {
        $settings = $this->normalizeSettingsData($this->normalizeDataJson($this->findSettingsModel()->data_json ?? null));
        $imagePath = $this->normalizeBotQrImagePath($settings['bot_qr_image'] ?? '');
        $fullPath = $this->resolveBotQrImageFullPath($imagePath);

        if ($fullPath === null || !is_file($fullPath)) {
            Yii::$app->response->format = Response::FORMAT_RAW;
            Yii::$app->response->statusCode = 404;
            Yii::$app->response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
            return 'ไม่พบรูป QR Code ของ Bot';
        }

        $mimeType = FileHelper::getMimeType($fullPath) ?: 'application/octet-stream';
        return Yii::$app->response->sendFile($fullPath, basename($fullPath), [
            'mimeType' => $mimeType,
            'inline' => !$download,
        ]);
    }

    protected function findSettingsModel(): Categorise
    {
        $model = Categorise::findOne(['name' => 'telegram_setting']);
        if ($model) {
            return $model;
        }

        $model = new Categorise();
        $model->name = 'telegram_setting';
        $model->code = 'telegram_setting';
        $model->title = 'Telegram Personal Notification';
        return $model;
    }

    protected function findLinkedUsers(): array
    {
        return User::find()
            ->alias('u')
            ->joinWith(['employee e'])
            ->andWhere(['IS NOT', 'u.telegram_id', null])
            ->andWhere(['<>', 'u.telegram_id', ''])
            ->orderBy(['e.department' => SORT_ASC, 'e.fname' => SORT_ASC, 'e.lname' => SORT_ASC])
            ->all();
    }

    protected function findTestUser(int $userId): User
    {
        $user = User::find()
            ->with(['employee'])
            ->andWhere(['id' => $userId])
            ->one();
        if (!$user) {
            throw new NotFoundHttpException('ไม่พบผู้ใช้งานสำหรับทดสอบ');
        }
        if (trim((string) $user->telegram_id) === '') {
            throw new NotFoundHttpException('ผู้ใช้นี้ยังไม่ได้ผูก Telegram');
        }

        return $user;
    }

    protected function resolveUserDisplayName(User $user): string
    {
        $employee = $user->employee;
        $employeeName = $employee ? trim((string) ($employee->fullname ?? '')) : '';
        $displayName = trim((string) ($employeeName !== '' ? $employeeName : ($user->fullname ?? $user->username)));

        return Html::encode($displayName !== '' ? $displayName : 'ผู้ใช้งาน #' . $user->id);
    }

    protected function jsonError(string $message, string $errorType = 'telegram_api'): array
    {
        return [
            'status' => 'error',
            'error_type' => $errorType,
            'message' => $message,
        ];
    }

    protected function normalizeSettingsData(array $data): array
    {
        $baseUrl = trim((string) ($data['mini_app_base_url'] ?? $data['mini_app'] ?? ''));

        return array_merge($data, [
            'bot_token' => trim((string) ($data['bot_token'] ?? $data['token'] ?? '')),
            'bot_username' => $this->normalizeBotUsername($data['bot_username'] ?? ''),
            'webhook_url' => trim((string) ($data['webhook_url'] ?? Url::to(['/telegrambot/webhook/index'], true))),
            'mini_app_base_url' => $baseUrl,
            'mini_app' => $baseUrl,
            'enable_mini_app' => (string) ($data['enable_mini_app'] ?? '0') === '1' ? '1' : '0',
            'enable_notification' => (string) ($data['enable_notification'] ?? '1') === '1' ? '1' : '0',
            'bot_qr_image' => $this->normalizeBotQrImagePath($data['bot_qr_image'] ?? ''),
            'bot_qr_image_name' => trim((string) ($data['bot_qr_image_name'] ?? '')),
            'bot_qr_image_uploaded_at' => trim((string) ($data['bot_qr_image_uploaded_at'] ?? '')),
        ]);
    }

    protected function applyBotQrImageUpload(array $data, array $existingData): array
    {
        $removeCurrentImage = Yii::$app->request->post('remove_bot_qr_image') === '1';
        $file = UploadedFile::getInstanceByName('bot_qr_image');
        $deleteAfterSave = [];
        $deleteOnFailure = [];

        if ($removeCurrentImage) {
            $deleteAfterSave[] = $existingData['bot_qr_image'] ?? '';
            $data['bot_qr_image'] = '';
            $data['bot_qr_image_name'] = '';
            $data['bot_qr_image_uploaded_at'] = '';
        }

        if ($file === null) {
            return [
                'success' => true,
                'data' => $data,
                'delete_after_save' => $deleteAfterSave,
                'delete_on_failure' => $deleteOnFailure,
            ];
        }

        if ((int) $file->error !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'อัปโหลดรูป QR Code ไม่สำเร็จ'];
        }

        if ((int) $file->size > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'รูป QR Code ต้องมีขนาดไม่เกิน 5 MB'];
        }

        $extension = strtolower((string) $file->extension);
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
        $mimeType = FileHelper::getMimeType($file->tempName) ?: $file->type;

        if (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            return ['success' => false, 'message' => 'อนุญาตเฉพาะไฟล์รูปภาพ PNG, JPG, WEBP หรือ GIF'];
        }

        $uploadDir = Yii::getAlias('@webroot') . '/uploads/telegrambot/qr';
        FileHelper::createDirectory($uploadDir, 0775, true);

        $filename = 'bot-qr-' . date('YmdHis') . '-' . Yii::$app->security->generateRandomString(8) . '.' . $extension;
        $savePath = $uploadDir . '/' . $filename;
        if (!$file->saveAs($savePath)) {
            return ['success' => false, 'message' => 'บันทึกรูป QR Code ไม่สำเร็จ'];
        }

        $data['bot_qr_image'] = '/uploads/telegrambot/qr/' . $filename;
        $data['bot_qr_image_name'] = $file->name;
        $data['bot_qr_image_uploaded_at'] = date('c');
        $deleteAfterSave[] = $existingData['bot_qr_image'] ?? '';
        $deleteOnFailure[] = $data['bot_qr_image'];

        return [
            'success' => true,
            'data' => $data,
            'delete_after_save' => $deleteAfterSave,
            'delete_on_failure' => $deleteOnFailure,
        ];
    }

    protected function isValidPublicHttpsUrl(?string $url): bool
    {
        if (!$url) {
            return false;
        }

        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ($host === '' || $host === 'localhost') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)
            && !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        return true;
    }

    protected function normalizeBotUsername($username): string
    {
        $username = ltrim(trim((string) $username), '@');
        $username = preg_replace('/[^A-Za-z0-9_]/', '', $username) ?: '';

        return substr($username, 0, 64);
    }

    protected function normalizeBotQrImagePath($path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        return strpos($path, '/uploads/telegrambot/qr/') === 0 ? $path : '';
    }

    protected function resolveBotQrImageFullPath(string $path): ?string
    {
        $path = $this->normalizeBotQrImagePath($path);
        if ($path === '') {
            return null;
        }

        return Yii::getAlias('@webroot') . $path;
    }

    protected function deleteBotQrImageFile($path): void
    {
        $fullPath = $this->resolveBotQrImageFullPath((string) $path);
        if ($fullPath !== null && is_file($fullPath)) {
            @unlink($fullPath);
        }
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

<?php

namespace app\modules\telegrambot\controllers;

use app\models\Categorise;
use app\modules\telegrambot\components\TelegramBot;
use app\modules\usermanager\models\User;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
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

            $model->name = 'telegram_setting';
            $model->code = $model->code ?: 'telegram_setting';
            $model->title = $model->title ?: 'Telegram Personal Notification';
            $model->data_json = json_encode($mergedData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', 'บันทึกการตั้งค่า Telegram สำเร็จ');
                return $this->refresh();
            }

            $model->data_json = $mergedData;
            Yii::$app->session->setFlash('error', 'บันทึกการตั้งค่า Telegram ไม่สำเร็จ');
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
            'notificationTestScenarios' => $this->getNotificationTestScenarios(),
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

    public function actionTestNotificationScenario()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $botToken = trim((string) Yii::$app->request->post('bot_token', ''));
        $scenarioKey = trim((string) Yii::$app->request->post('scenario', ''));
        $miniAppBaseUrl = trim((string) Yii::$app->request->post('mini_app_base_url', ''));
        $scenarios = $this->getNotificationTestScenarios();
        if (!isset($scenarios[$scenarioKey])) {
            return $this->jsonError('ไม่พบรูปแบบการแจ้งเตือนที่เลือก', 'invalid_scenario');
        }

        try {
            $user = $this->findTestUser((int) Yii::$app->request->post('user_id', 0));
        } catch (NotFoundHttpException $e) {
            return $this->jsonError($e->getMessage(), 'user_not_linked');
        }

        $scenario = $scenarios[$scenarioKey];
        $attachWebApp = (bool) ($scenario['attach_web_app'] ?? true);
        $webAppUrl = null;
        $options = [
            'parse_mode' => 'HTML',
        ];

        if ($attachWebApp) {
            if (!$this->isValidPublicHttpsUrl($miniAppBaseUrl)) {
                return $this->jsonError('Mini App Base URL ต้องเป็น https ที่เข้าถึงได้จากภายนอก', 'invalid_url');
            }

            $webAppUrl = $this->buildScenarioWebAppUrl($miniAppBaseUrl, $scenario['route'] ?? ['/mobile/default/index']);
            if (!$this->isValidPublicHttpsUrl($webAppUrl)) {
                return $this->jsonError('ไม่สามารถสร้าง URL สำหรับปุ่ม Mini App ได้ กรุณาตรวจสอบ Base URL', 'invalid_url');
            }

            $options['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => (string) ($scenario['button_text'] ?? 'เปิด ERP Mobile'),
                            'web_app' => [
                                'url' => $webAppUrl,
                            ],
                        ],
                    ],
                ],
            ];
        }

        $telegram = new TelegramBot($botToken !== '' ? $botToken : null);
        $sent = $telegram->sendMessage(
            $user->telegram_id,
            $this->buildScenarioTestMessage($scenario, $user),
            $options
        );

        if (!$sent) {
            return $this->jsonError($telegram->getLastError() ?: 'ส่งการแจ้งเตือนทดสอบไม่สำเร็จ');
        }

        return [
            'status' => 'success',
            'message' => 'ส่งการแจ้งเตือนทดสอบระบบ ' . ($scenario['label'] ?? '') . ' สำเร็จ',
            'scenario' => $scenarioKey,
            'web_app_url' => $webAppUrl,
            'attach_web_app' => $attachWebApp,
        ];
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

    protected function getNotificationTestScenarios(): array
    {
        return [
            'general' => [
                'label' => 'แจ้งเตือนทั่วไป',
                'short_label' => 'ทั่วไป',
                'description' => 'ข้อความตรงพร้อมปุ่มเปิดหน้าแรก ERP Mobile',
                'button_text' => 'เปิด ERP Mobile',
                'route' => ['/mobile/default/index'],
                'lines' => [
                    '🔔 <b>ทดสอบแจ้งเตือนรายบุคคล</b>',
                    'ระบบส่งข้อความตรงถึงผู้ใช้ที่ผูก Telegram แล้ว',
                    'ใช้ตรวจสอบ token, chat id และสิทธิ์รับข้อความ',
                ],
            ],
            'leave_pending' => [
                'label' => 'ใบลา: รออนุมัติ',
                'short_label' => 'ลา',
                'description' => 'จำลองข้อความถึงหัวหน้าหรือผู้อนุมัติใบลา',
                'button_text' => 'เปิดรายการอนุมัติใบลา',
                'route' => ['/mobile/default/leave-approvals'],
                'lines' => [
                    '📋 <b>แจ้งเตือนการอนุมัติใบลา</b>',
                    'ผู้ขอ: เจ้าหน้าที่ทดสอบ ระบบ ERP',
                    'ประเภท: ลาพักผ่อน',
                    'ช่วงเวลา: วันนี้ 08:30-16:30',
                    'จำนวน: 1 วัน',
                    'ขั้นตอน: ผู้อนุมัติลำดับถัดไป',
                ],
            ],
            'inventory_requisition_pending' => [
                'label' => 'เบิกวัสดุ: รออนุมัติ',
                'short_label' => 'เบิกวัสดุ',
                'description' => 'จำลองข้อความถึงผู้อนุมัติเมื่อมีใบขอเบิกวัสดุใหม่',
                'attach_web_app' => false,
                'lines' => [
                    '📦 <b>ใบขอเบิกวัสดุรออนุมัติ</b>',
                    'เลขที่: REQ-TEST-001',
                    'ผู้ขอ: เจ้าหน้าที่ทดสอบ ระบบ ERP',
                    'คลังที่จ่าย: คลังวัสดุกลาง',
                    'คลังที่รับ: คลังย่อยงานบริการ',
                    'วันที่ขอ: วันนี้',
                    'จำนวนรายการ: 3 รายการ',
                    'วัตถุประสงค์: เติมสต็อกคลังย่อย',
                ],
            ],
            'inventory_requisition_approved' => [
                'label' => 'เบิกวัสดุ: อนุมัติแล้ว',
                'short_label' => 'อนุมัติ',
                'description' => 'จำลองข้อความถึงผู้ขอหลังผู้อนุมัติดำเนินการอนุมัติแล้ว',
                'attach_web_app' => false,
                'lines' => [
                    '✅ <b>ผลการอนุมัติใบขอเบิกวัสดุ</b>',
                    'เลขที่: REQ-TEST-001',
                    'ผลการพิจารณา: อนุมัติแล้ว',
                    'ผู้อนุมัติ: หัวหน้าหน่วยงานทดสอบ',
                    'สถานะต่อไป: รอคลังหลักดำเนินการจ่าย',
                ],
            ],
            'inventory_requisition_issue_queue' => [
                'label' => 'เบิกวัสดุ: รอคลังจ่าย',
                'short_label' => 'รอจ่าย',
                'description' => 'จำลองข้อความถึงผู้ดูแลคลังเมื่อใบขอเบิกได้รับอนุมัติ',
                'attach_web_app' => false,
                'lines' => [
                    '📦 <b>ใบขอเบิกวัสดุรอจ่ายจากคลังหลัก</b>',
                    'เลขที่: REQ-TEST-001',
                    'ผู้ขอ: เจ้าหน้าที่ทดสอบ ระบบ ERP',
                    'คลังที่จ่าย: คลังวัสดุกลาง',
                    'คลังที่รับ: คลังย่อยงานบริการ',
                    'วันที่อนุมัติ: วันนี้',
                    'จำนวนรายการ: 3 รายการ',
                ],
            ],
            'inventory_requisition_disbursed' => [
                'label' => 'เบิกวัสดุ: จ่ายแล้ว',
                'short_label' => 'จ่ายแล้ว',
                'description' => 'จำลองข้อความถึงผู้ขอเมื่อคลังหลักจ่ายพัสดุเสร็จ',
                'attach_web_app' => false,
                'lines' => [
                    '✅ <b>คลังหลักจ่ายพัสดุแล้ว</b>',
                    'เลขที่: REQ-TEST-001',
                    'คลังที่จ่าย: คลังวัสดุกลาง',
                    'คลังที่รับ: คลังย่อยงานบริการ',
                    'วันที่จ่าย: วันนี้',
                    'ผู้จ่าย: เจ้าหน้าที่คลังทดสอบ',
                    'จำนวนรายการ: 3 รายการ',
                ],
            ],
            'official_travel' => [
                'label' => 'ขออนุญาตไปราชการ',
                'short_label' => 'ไปราชการ',
                'description' => 'จำลองคำขอเดินทางไปราชการและงานเอกสารประกอบ',
                'button_text' => 'เปิดรายการขอไปราชการ',
                'route' => ['/development/default/mine'],
                'lines' => [
                    '🧾 <b>คำขออนุญาตไปราชการรอพิจารณา</b>',
                    'เรื่อง: อบรมระบบงานสารสนเทศโรงพยาบาล',
                    'วันที่เดินทาง: วันนี้ 09:00 น.',
                    'สถานที่: สำนักงานสาธารณสุขจังหวัด',
                    'สถานะ: รอผู้มีอำนาจพิจารณา',
                ],
            ],
            'meeting_request' => [
                'label' => 'จองห้องประชุม',
                'short_label' => 'ห้องประชุม',
                'description' => 'จำลองข้อความถึงผู้รับผิดชอบห้องประชุม',
                'button_text' => 'เปิดระบบจองห้องประชุม',
                'route' => ['/mobile/default/booking-meeting'],
                'lines' => [
                    '🏢 <b>มีคำขอจองห้องประชุมใหม่</b>',
                    'หัวข้อ: ประชุมคณะกรรมการบริหาร',
                    'ห้อง: ห้องประชุมใหญ่',
                    'เวลา: วันนี้ 13:30-15:30 น.',
                    'ผู้ติดต่อ: เจ้าหน้าที่ทดสอบ',
                ],
            ],
            'meeting_approved' => [
                'label' => 'ผลอนุมัติห้องประชุม',
                'short_label' => 'อนุมัติห้อง',
                'description' => 'จำลองข้อความแจ้งผู้จองหลังอนุมัติห้องประชุม',
                'button_text' => 'ดูคำขอจองห้องของฉัน',
                'route' => ['/mobile/default/my-requests', 'type' => 'meeting'],
                'lines' => [
                    '✅ <b>การจองห้องประชุมได้รับการอนุมัติแล้ว</b>',
                    'หัวข้อ: ประชุมติดตามงานประจำเดือน',
                    'ห้อง: ห้องประชุม 2',
                    'เวลา: วันนี้ 10:00-11:30 น.',
                ],
            ],
            'vehicle_request' => [
                'label' => 'จองรถราชการ',
                'short_label' => 'จองรถ',
                'description' => 'จำลองข้อความถึงผู้ดูแลรถหรือพนักงานขับ',
                'button_text' => 'เปิดระบบจองรถ',
                'route' => ['/mobile/default/booking-vehicle'],
                'lines' => [
                    '🚗 <b>แจ้งเตือนการจองรถ</b>',
                    'ผู้จอง: เจ้าหน้าที่ทดสอบ ระบบ ERP',
                    'วัตถุประสงค์: ส่งเอกสารราชการ',
                    'สถานที่: ที่ว่าการอำเภอด่านซ้าย',
                    'เวลาเดินทาง: วันนี้ 09:00-11:00 น.',
                ],
            ],
            'vehicle_allocated' => [
                'label' => 'จัดสรรรถแล้ว',
                'short_label' => 'รถพร้อม',
                'description' => 'จำลองข้อความแจ้งผู้ขอเมื่อจัดสรรรถ/พนักงานขับแล้ว',
                'button_text' => 'ดูคำขอจองรถของฉัน',
                'route' => ['/mobile/default/my-requests', 'type' => 'vehicle'],
                'lines' => [
                    '✅ <b>การจัดสรรยานพาหนะ</b>',
                    'คำขอ VEH-TEST ได้รับการจัดสรรแล้ว',
                    'ทะเบียน: กข 1234 เลย',
                    'พนักงานขับ: เจ้าหน้าที่ทดสอบ',
                    'ช่วงเดินทาง: วันนี้ 09:00-11:00 น.',
                ],
            ],
            'repair_new' => [
                'label' => 'แจ้งซ่อม',
                'short_label' => 'ช่างซ่อม',
                'description' => 'จำลองงานซ่อมใหม่ถึงทีมช่างหรือผู้รับผิดชอบ',
                'button_text' => 'เปิดระบบแจ้งซ่อม',
                'route' => ['/mobile/default/repair-request'],
                'lines' => [
                    '🛠️ <b>แจ้งงานซ่อมใหม่</b>',
                    'รหัส: RP-TEST',
                    'หัวข้อ: เครื่องคอมพิวเตอร์เปิดไม่ติด',
                    'กลุ่มงาน: คอมพิวเตอร์',
                    'สถานที่: ห้องเวชระเบียน',
                    'ความเร่งด่วน: ปกติ',
                ],
            ],
            'official_document' => [
                'label' => 'หนังสือราชการ',
                'short_label' => 'สารบรรณ',
                'description' => 'จำลองหนังสือราชการหรือความเห็นใหม่ที่ส่งถึงผู้ใช้',
                'button_text' => 'เปิดหนังสือราชการ',
                'route' => ['/mobile/default/news'],
                'lines' => [
                    '📄 <b>มีหนังสือราชการใหม่</b>',
                    'เลขรับ: TEST-2569-001',
                    'เรื่อง: แจ้งเวียนการประชุมประจำเดือน',
                    'โดย: งานสารบรรณ',
                    'สถานะ: ยังไม่ได้อ่าน',
                ],
            ],
        ];
    }

    protected function buildScenarioTestMessage(array $scenario, User $user): string
    {
        $lines = $scenario['lines'] ?? [];
        $lines[] = '';
        $lines[] = '<b>โหมดทดสอบ Telegram</b>';
        $lines[] = 'ผู้รับ: ' . $this->resolveUserDisplayName($user);
        $lines[] = 'เวลา: ' . Html::encode(date('d/m/Y H:i:s'));
        $lines[] = 'หมายเหตุ: ข้อความนี้เป็นข้อมูลจำลอง ไม่สร้างรายการจริงในระบบ';

        return implode("\n", $lines);
    }

    protected function buildScenarioWebAppUrl(string $miniAppBaseUrl, array $route): string
    {
        $relativeUrl = Url::to($route);
        $relativeParts = parse_url($relativeUrl) ?: [];
        $routePath = (string) ($relativeParts['path'] ?? '');
        $routeQuery = (string) ($relativeParts['query'] ?? '');

        $baseParts = parse_url($miniAppBaseUrl) ?: [];
        $scheme = (string) ($baseParts['scheme'] ?? '');
        $host = (string) ($baseParts['host'] ?? '');
        if ($scheme === '' || $host === '') {
            return '';
        }

        $url = $scheme . '://' . $host;
        if (!empty($baseParts['port'])) {
            $url .= ':' . $baseParts['port'];
        }

        $url .= '/' . ltrim($routePath, '/');
        if ($routeQuery !== '') {
            $url .= '?' . $routeQuery;
        }

        return $url;
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
        ]);
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

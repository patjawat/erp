<?php

namespace app\modules\telegrambot\controllers;

use app\models\Categorise;
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
        $telegram = new TelegramBot();

        $input = json_decode(file_get_contents('php://input'), true);

        if (isset($input['message'])) {
            $chatId = $input['message']['chat']['id'];
            $text = $input['message']['text'];

            // ตอบกลับข้อความ
            $telegram->sendMessage($chatId, "คุณส่งข้อความว่า: " . $text);

            return ['status' => 'ok'];
        }

        return ['status' => 'no_message'];
    }

}


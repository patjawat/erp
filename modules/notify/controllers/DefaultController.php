<?php

namespace app\modules\notify\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\components\UserHelper;
use app\modules\notify\models\Notify;
use app\modules\notify\models\NotifySearch;
use app\modules\notify\models\NotifySendTestForm;
use app\modules\hr\models\Employees;
use yii\helpers\Url;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        $searchModel = new NotifySearch();
        $searchModel->recipient_emp_id = $me->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        // บังคับแสดงเฉพาะแจ้งเตือนของผู้อ่าน (ป้องกัน params หรือ filter แทนที่ recipient_emp_id)
        $dataProvider->query->andWhere(['recipient_emp_id' => (int) $me->id]);

        $unreadCount = (int) Notify::find()
            ->andWhere(['recipient_emp_id' => $me->id, 'read_at' => null])
            ->count();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'unreadCount' => $unreadCount,
            'me' => $me,
        ]);
    }

    public function actionView($id)
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        $model = $this->findModel($id);
        if ((int) $model->recipient_emp_id !== (int) $me->id) {
            throw new NotFoundHttpException('ไม่พบรายการนี้');
        }
        $model->markAsRead();

        return $this->render('view', ['model' => $model]);
    }

    public function actionMarkRead($id)
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return $this->redirect(['/me']);
        }
        $model = $this->findModel($id);
        if ((int) $model->recipient_emp_id === (int) $me->id) {
            $model->markAsRead();
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['ok' => true];
        }
        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    public function actionMarkAllRead()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return $this->redirect(['/me']);
        }
        Notify::updateAll(
            ['read_at' => date('Y-m-d H:i:s')],
            ['recipient_emp_id' => $me->id, 'read_at' => null]
        );
        Yii::$app->session->setFlash('success', 'ทำเครื่องหมายอ่านทั้งหมดแล้ว');
        return $this->redirect(['index']);
    }

    /**
     * แสดงฟอร์มส่งแจ้งเตือนทดสอบใน modal (เลือกประเภท + ผู้รับ)
     */
    public function actionSendTestForm()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['title' => 'แจ้งเตือน', 'content' => '<p class="text-danger">ไม่พบข้อมูลพนักงาน</p>', 'footer' => ''];
            }
            return $this->redirect(['/me']);
        }

        $model = new NotifySendTestForm();
        $model->recipient_emp_id = $me->id;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'title' => 'ส่งแจ้งเตือนทดสอบ',
                'content' => $this->renderAjax('_send_test_form', [
                    'model' => $model,
                    'typeLabels' => Notify::typeLabels(),
                    'me' => $me,
                ]),
                'footer' => '<button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">ปิด</button>',
            ];
        }
        return $this->redirect(['index']);
    }

    /**
     * สร้างแจ้งเตือนทดสอบ (รับ type, recipient_emp_id จากฟอร์มหรือ query)
     */
    public function actionSendTest()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        $type = Yii::$app->request->get('type');
        $recipientEmpId = Yii::$app->request->get('recipient_emp_id')
            ?? Yii::$app->request->get('NotifySendTest')['recipient_emp_id'] ?? null;
        $types = array_keys(Notify::typeLabels());
        if ($type === null || $type === '' || !in_array($type, $types, true)) {
            $type = $types[array_rand($types)];
        }
        if ($recipientEmpId === null || $recipientEmpId === '') {
            $recipientEmpId = $me->id;
        } else {
            $recipientEmpId = (int) $recipientEmpId;
            $exists = Employees::find()->where(['id' => $recipientEmpId])->exists();
            if (!$exists) {
                $recipientEmpId = $me->id;
            }
        }

        $titles = [
            Notify::TYPE_LEAVE_APPROVE => 'มีคำขอลารออนุมัติ – ทดสอบ',
            Notify::TYPE_PURCHASE_APPROVE => 'มีคำขอจัดซื้อจัดจ้างรออนุมัติ – ทดสอบ',
            Notify::TYPE_CHECKIN_APPROVE => 'มีรายการลงเวลารออนุมัติ – ทดสอบ',
            Notify::TYPE_VEHICLE_APPROVE => 'มีคำขอใช้รถรออนุมัติ – ทดสอบ',
            Notify::TYPE_STOCK_APPROVE => 'มีคำขอเบิกวัสดุรออนุมัติ – ทดสอบ',
            Notify::TYPE_DEVELOPMENT_APPROVE => 'มีคำขออบรม/ประชุม/ดูงานรออนุมัติ – ทดสอบ',
            Notify::TYPE_ASSET_MOVE_APPROVE => 'มีคำขอเคลื่อนย้ายครุภัณฑ์รออนุมัติ – ทดสอบ',
        ];
        $title = $titles[$type] ?? ('แจ้งเตือนทดสอบ – ' . (Notify::typeLabels()[$type] ?? $type));
        $message = 'นี่เป็นแจ้งเตือนทดสอบ สร้างเมื่อ ' . date('d/m/Y H:i');

        // ตรวจสอบว่าตาราง notify มีอยู่ (ถ้ายังไม่รัน migration จะบันทึกไม่ได้)
        if (Yii::$app->db->getTableSchema(Notify::tableName(), true) === null) {
            $migrationHint = 'กรุณารัน: php yii migrate --migrationPath=@app/modules/notify/migrations';
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'ยังไม่มีตารางแจ้งเตือน ' . $migrationHint];
            }
            Yii::$app->session->setFlash('error', 'ยังไม่มีตารางแจ้งเตือน ' . $migrationHint);
            return $this->redirect(['index']);
        }

        $notify = Notify::createFromApprove($type, $title, $recipientEmpId, Notify::REF_TYPE_TEST, null, $message, null);

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            if ($notify) {
                return ['success' => true, 'message' => 'ส่งแจ้งเตือนทดสอบแล้ว'];
            }
            return ['success' => false, 'message' => 'สร้างแจ้งเตือนทดสอบไม่สำเร็จ (ตรวจสอบ log หรือรัน migration)'];
        }

        if ($notify) {
            Yii::$app->session->setFlash('success', 'ส่งแจ้งเตือนทดสอบแล้ว (ประเภท: ' . (Notify::typeLabels()[$type] ?? $type) . ')');
        } else {
            Yii::$app->session->setFlash('error', 'สร้างแจ้งเตือนทดสอบไม่สำเร็จ');
        }
        return $this->redirect(['index']);
    }

    /**
     * API สำหรับ PWA polling: คืนรายการแจ้งเตือนที่ยังไม่อ่าน (ใหม่ตั้งแต่ last_id)
     * GET ?last_id=123 คืนรายการที่ id > 123
     */
    public function actionPoll()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return ['last_id' => 0, 'items' => []];
        }
        $lastId = (int) Yii::$app->request->get('last_id', 0);
        $query = Notify::find()
            ->andWhere(['recipient_emp_id' => $me->id, 'read_at' => null])
            ->orderBy(['id' => SORT_ASC])
            ->limit(20);
        if ($lastId > 0) {
            $query->andWhere(['>', 'id', $lastId]);
        }
        $list = $query->all();
        $items = [];
        $maxId = $lastId;
        foreach ($list as $n) {
            if ($n->id > $maxId) {
                $maxId = $n->id;
            }
            $items[] = [
                'id' => $n->id,
                'title' => $n->title,
                'type_label' => $n->getTypeLabel(),
                'url' => Url::to(['/notify/default/view', 'id' => $n->id], true),
            ];
        }
        return ['last_id' => $maxId, 'items' => $items];
    }

    protected function findModel($id)
    {
        $model = Notify::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการแจ้งเตือน');
        }
        return $model;
    }
}

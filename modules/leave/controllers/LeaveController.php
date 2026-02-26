<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\Url;
use app\components\UserHelper;
use app\components\AppHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\LeaveType;
use app\components\SiteHelper;

/**
 * สร้างใบลา (2 ขั้น: กรอกรายละเอียด → ตรวจสอบ + ลงลายมือชื่อ)
 */
class LeaveController extends Controller
{
    const SESSION_KEY = 'leave_create_draft';

    public function actionCreate()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/leave/default/index']);
        }

        $thaiYear = (int) AppHelper::YearBudget();
        $budgetRange = AppHelper::BudgetYearRange($thaiYear);
        $roundLabel = 'รอบที่ 1 (1 ม.ค. ' . substr($thaiYear - 1, 2) . ' - 31 มี.ค. ' . substr($thaiYear, 2) . ')';

        $types = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        $stats = $this->getStatsForCreate($me->id, $thaiYear);

        if (Yii::$app->request->isPost) {
            $leaveTypeId = Yii::$app->request->post('leave_type_id');
            $dateStart = Yii::$app->request->post('date_start');
            $dateEnd = Yii::$app->request->post('date_end');
            $reason = Yii::$app->request->post('reason', '');
            $address = Yii::$app->request->post('address', '');
            if (!$leaveTypeId || !$dateStart || !$dateEnd) {
                Yii::$app->session->setFlash('error', 'กรุณาเลือกประเภทการลา และวันที่เริ่มต้น-สิ้นสุด');
                return $this->redirect(['create']);
            }
            $dateStartGregorian = AppHelper::convertToGregorian($dateStart);
            $dateEndGregorian = AppHelper::convertToGregorian($dateEnd);
            $totalDays = $this->calDays($dateStartGregorian, $dateEndGregorian);
            $draft = [
                'leave_type_id' => $leaveTypeId,
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'date_start_g' => $dateStartGregorian,
                'date_end_g' => $dateEndGregorian,
                'total_days' => $totalDays,
                'reason' => $reason,
                'address' => $address,
            ];
            Yii::$app->session->set(self::SESSION_KEY, $draft);
            return $this->redirect(['confirm']);
        }

        return $this->render('create', [
            'employee' => $me,
            'types' => $types,
            'stats' => $stats,
            'roundLabel' => $roundLabel,
            'thaiYear' => $thaiYear,
        ]);
    }

    public function actionConfirm()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            return $this->redirect(['/leave/default/index']);
        }
        $draft = Yii::$app->session->get(self::SESSION_KEY);
        if (!$draft) {
            Yii::$app->session->setFlash('warning', 'ไม่พบข้อมูลกรอกใบลา กรุณากรอกใหม่');
            return $this->redirect(['create']);
        }

        $leaveType = LeaveType::find()->where(['name' => 'leave_type', 'code' => $draft['leave_type_id']])->one();

        return $this->render('confirm', [
            'employee' => $me,
            'draft' => $draft,
            'leaveType' => $leaveType,
        ]);
    }

    public function actionSave()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'ไม่พบข้อมูลพนักงาน'];
            }
            return $this->redirect(['/leave/default/index']);
        }

        $draft = Yii::$app->session->get(self::SESSION_KEY);
        if (!$draft) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'message' => 'หมดอายุ กรุณากรอกใบลาใหม่'];
            }
            return $this->redirect(['create']);
        }

        $signatureData = Yii::$app->request->post('signature_data'); // base64 image or null

        $model = new Leave([
            'ref' => substr(Yii::$app->getSecurity()->generateRandomString(), 10),
            'leave_type_id' => $draft['leave_type_id'],
            'date_start' => $draft['date_start_g'],
            'date_end' => $draft['date_end_g'],
            'total_days' => (float) $draft['total_days'],
            'emp_id' => $me->id,
            'on_holidays' => 0,
            'thai_year' => (int) AppHelper::YearBudget($draft['date_start_g']),
        ]);

        $approve = $model->Approve();
        $model->data_json = [
            'reason' => $draft['reason'] ?? '',
            'address' => $draft['address'] ?? '',
            'work_shift' => $me->work_shift ?? null,
            'approve_1' => $approve['approve_1']['id'] ?? null,
            'approve_2' => $approve['approve_2']['id'] ?? null,
            'leave_contact_phone' => $me->phone ?? '',
            'director' => SiteHelper::viewDirector()['id'] ?? null,
            'director_fullname' => SiteHelper::viewDirector()['fullname'] ?? '',
            'signature_data' => $signatureData,
        ];

        $model->status = $me->isDirector() ? 'Approve' : 'Pending';
        if ($model->save(false)) {
            if (!$me->isDirector()) {
                $model->createApprove();
            }
            Yii::$app->session->remove(self::SESSION_KEY);
            Yii::$app->session->setFlash('success', 'สร้างใบลาสำเร็จ');
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => true, 'redirect' => Url::to(['/leave/default/index'])];
            }
            return $this->redirect(['/leave/default/index']);
        }

        Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => false, 'message' => 'บันทึกไม่สำเร็จ'];
        }
        return $this->redirect(['confirm']);
    }

    protected function getStatsForCreate($empId, $thaiYear)
    {
        $types = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $rows = [];
        foreach ($types as $t) {
            $used = Leave::find()
                ->where([
                    'emp_id' => $empId,
                    'thai_year' => $thaiYear,
                    'leave_type_id' => $t->code,
                    'status' => 'Approve',
                ])
                ->select(['days' => 'SUM(total_days)', 'times' => 'COUNT(id)'])
                ->asArray()
                ->one();
            $rows[] = [
                'code' => $t->code,
                'title' => $t->title,
                'used_days' => (float) ($used['days'] ?? 0),
                'used_times' => (int) ($used['times'] ?? 0),
            ];
        }
        return $rows;
    }

    protected function calDays($start, $end)
    {
        $startTs = strtotime($start);
        $endTs = strtotime($end);
        if ($endTs < $startTs) {
            return 0;
        }
        $days = (($endTs - $startTs) / 86400) + 1;
        return round($days, 2);
    }
}

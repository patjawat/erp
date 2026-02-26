<?php

namespace app\modules\development\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
use app\components\UserHelper;
use app\components\CategoriseHelper;
use app\modules\hr\models\Development;
use app\modules\hr\models\DevelopmentDetail;

/**
 * ฟอร์มบันทึกข้อความขอไปราชการ แบบหลายขั้นตอน (ตาม UX ใหม่)
 */
class TravelRequestController extends Controller
{
    const SESSION_KEY = 'travel_request_draft';

    private function getDraft()
    {
        return Yii::$app->session->get(self::SESSION_KEY, []);
    }

    private function setDraft($data)
    {
        Yii::$app->session->set(self::SESSION_KEY, array_merge($this->getDraft(), $data));
    }

    private function clearDraft()
    {
        Yii::$app->session->remove(self::SESSION_KEY);
    }

    /**
     * Step 1: ข้อมูลการเดินทาง
     */
    public function actionIndex()
    {
        $draft = $this->getDraft();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $members = $draft['members'] ?? [];
            if (!empty($post['members_json'])) {
                $decoded = json_decode($post['members_json'], true);
                if (is_array($decoded)) {
                    $members = $decoded;
                }
            }
            $this->setDraft([
                'date_start' => $post['date_start'] ?? '',
                'date_end' => $post['date_end'] ?? '',
                'topic' => $post['topic'] ?? '',
                'location' => $post['location'] ?? '',
                'province_name' => $post['province_name'] ?? '',
                'members' => $members,
            ]);
            return $this->redirect(['step2']);
        }
        $provinces = CategoriseHelper::ListProvinceName();
        return $this->render('step1', [
            'draft' => $draft,
            'provinces' => $provinces,
        ]);
    }

    /**
     * Step 2: รายละเอียดเพิ่มเติม (สิ่งที่ส่งมาด้วย + เรื่องที่ขออนุมัติ)
     */
    public function actionStep2()
    {
        $draft = $this->getDraft();
        if (empty($draft['date_start']) && empty($draft['topic'])) {
            return $this->redirect(['index']);
        }
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $classRows = $post['class_change_rows'] ?? [];
            if (is_array($classRows)) {
                $classRows = array_values(array_filter($classRows, function ($r) {
                    return !empty($r['day_time']) || !empty($r['period']) || !empty($r['subject_class']);
                }));
            } else {
                $classRows = [];
            }
            $this->setDraft([
                'attach_invitation' => !empty($post['attach_invitation']),
                'attach_class_change' => !empty($post['attach_class_change']),
                'class_change_rows' => $classRows,
                'attach_vehicle' => !empty($post['attach_vehicle']),
                'attach_budget' => !empty($post['attach_budget']),
                'attach_other_text' => $post['attach_other_text'] ?? '',
                'claim_travel' => !empty($post['claim_travel']),
                'claim_per_diem' => !empty($post['claim_per_diem']),
                'claim_transport' => !empty($post['claim_transport']),
                'claim_accommodation' => !empty($post['claim_accommodation']),
                'claim_registration' => !empty($post['claim_registration']),
                'registration_amount' => $post['registration_amount'] ?? '',
                'no_claim_org' => !empty($post['no_claim_org']),
                'use_official_vehicle' => !empty($post['use_official_vehicle']),
                'vehicle_plate' => $post['vehicle_plate'] ?? '',
                'driver_name' => $post['driver_name'] ?? '',
            ]);
            return $this->redirect(['step4']);
        }
        return $this->render('step2', ['draft' => $draft]);
    }

    /**
     * Step 4: ยืนยันและลงนาม
     */
    public function actionStep4()
    {
        $draft = $this->getDraft();
        if (empty($draft['date_start']) && empty($draft['topic'])) {
            return $this->redirect(['index']);
        }
        return $this->render('step4', ['draft' => $draft]);
    }

    /**
     * บันทึกจริงและไปหน้า success
     */
    public function actionSubmit()
    {
        $draft = $this->getDraft();
        if (empty($draft['topic']) || empty($draft['date_start']) || empty($draft['date_end'])) {
            Yii::$app->session->setFlash('error', 'กรุณากรอกข้อมูลขั้นตอนที่ 1 ให้ครบ');
            return $this->redirect(['index']);
        }

        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['index']);
        }

        $model = new Development();
        $model->thai_year = (int) AppHelper::YearBudget();
        $model->emp_id = $me->id;
        $model->status = 'Pending';
        $model->topic = $draft['topic'];
        $model->date_start = AppHelper::convertToGregorian($draft['date_start']);
        $model->date_end = AppHelper::convertToGregorian($draft['date_end']);
        $model->vehicle_date_start = $model->date_start;
        $model->vehicle_date_end = $model->date_end;
        $model->leader_id = $me->leader_id ?? $me->id;
        $model->leader_group_id = $me->leader_group_id ?? $me->id;
        $model->assigned_to = $me->id;

        $dataJson = [
            'location' => $draft['location'] ?? '',
            'province_name' => $draft['province_name'] ?? '',
            'location_org' => $draft['location_org'] ?? '',
            'attach_invitation' => !empty($draft['attach_invitation']),
            'attach_class_change' => !empty($draft['attach_class_change']),
            'class_change_rows' => $draft['class_change_rows'] ?? [],
            'attach_vehicle' => !empty($draft['attach_vehicle']),
            'attach_budget' => !empty($draft['attach_budget']),
            'attach_other_text' => $draft['attach_other_text'] ?? '',
            'claim_travel' => !empty($draft['claim_travel']),
            'claim_per_diem' => !empty($draft['claim_per_diem']),
            'claim_transport' => !empty($draft['claim_transport']),
            'claim_accommodation' => !empty($draft['claim_accommodation']),
            'claim_registration' => !empty($draft['claim_registration']),
            'registration_amount' => $draft['registration_amount'] ?? '',
            'no_claim_org' => !empty($draft['no_claim_org']),
            'use_official_vehicle' => !empty($draft['use_official_vehicle']),
            'vehicle_plate' => $draft['vehicle_plate'] ?? '',
            'driver_name' => $draft['driver_name'] ?? '',
        ];
        $model->data_json = $dataJson;

        if ($model->save(false)) {
            AppHelper::checkLocation($model->data_json['location']);
            if (!empty($model->data_json['location_org'])) {
                AppHelper::checkLocation($model->data_json['location_org']);
            }
            $mainMember = new DevelopmentDetail();
            $mainMember->development_id = $model->id;
            $mainMember->name = 'member';
            $mainMember->emp_id = $me->id;
            $mainMember->save(false);
            foreach ($draft['members'] ?? [] as $m) {
                if (empty($m['emp_id']) && empty($m['label'])) {
                    continue;
                }
                $detail = new DevelopmentDetail();
                $detail->development_id = $model->id;
                $detail->name = 'member';
                $detail->emp_id = $m['emp_id'] ?? null;
                $detail->data_json = isset($m['label']) ? ['label' => $m['label']] : null;
                $detail->save(false);
            }
            if (method_exists($model, 'createApprove')) {
                $model->createApprove();
            }
            $this->clearDraft();
            return $this->redirect(['success', 'id' => $model->id]);
        }

        Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ');
        return $this->redirect(['step4']);
    }

    /**
     * หน้า success หลังบันทึก
     */
    public function actionSuccess($id)
    {
        $model = Development::findOne($id);
        if (!$model) {
            return $this->redirect(['/development/default/dashboard']);
        }
        return $this->render('success', ['model' => $model]);
    }
}

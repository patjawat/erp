<?php

namespace app\modules\plan\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use app\components\AppHelper;
use app\modules\plan\models\PlanOrder;

/**
 * ApproveController — อนุมัติ/ไม่อนุมัติแผนของหน่วยงาน (Part D)
 * เข้าถึงได้เฉพาะผู้มีสิทธิ planApprove (ผอ./ผู้ที่กำหนด) แม้จะมี role plan ก็อนุมัติไม่ได้ถ้าไม่มีสิทธิ์นี้
 */
class ApproveController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'approve' => ['POST'],
                    'reject'  => ['POST'],
                ],
            ],
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!Yii::$app->user->can('planApprove')) {
            throw new ForbiddenHttpException('ไม่มีสิทธิ์อนุมัติแผนของหน่วยงาน');
        }
        return true;
    }

    /** รายการแผนหน่วยงานที่ส่งขออนุมัติ (แยกตามสถานะ) */
    public function actionIndex()
    {
        $thaiYear = (int) $this->request->get('thai_year', \app\modules\plan\components\PlanHelper::currentPlanYear());
        $status   = $this->request->get('status', 'submit');
        $group    = (string) $this->request->get('group', 'all'); // department|parcel|personnel|expenses|all

        // inbox รวมทุกประเภทแผน (พัสดุ/บุคลากร/ค่าใช้สอย/หน่วยงาน)
        $base = PlanOrder::find()->where(['thai_year' => $thaiYear]);
        if (in_array($group, ['department', 'parcel', 'personnel', 'expenses'], true)) {
            $base->andWhere(['plan_group_id' => $group]);
        }

        // นับแต่ละสถานะ (สำหรับ tabs)
        $counts = ['submit' => 0, 'approve' => 0, 'reject' => 0];
        foreach ((clone $base)->select(['status', 'c' => 'COUNT(*)'])->groupBy('status')->asArray()->all() as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = (int) $row['c'];
            }
        }

        $query = clone $base;
        if ($status !== 'all') {
            $query->andWhere(['status' => $status]);
        }
        $models = $query->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC])->all();

        return $this->render('index', [
            'models'   => $models,
            'thaiYear' => $thaiYear,
            'status'   => $status,
            'group'    => $group,
            'counts'   => $counts,
            'years'    => $this->yearOptions(),
        ]);
    }

    /** อนุมัติ (รออนุมัติ -> อนุมัติ) */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        if ($model->status === 'submit') {
            $model->status = 'approve';
            $this->stampDecision($model, null);
            $model->save(false);
            Yii::$app->session->setFlash('success', 'อนุมัติแผนเรียบร้อย');
        }
        return $this->redirect(['index', 'thai_year' => $model->thai_year, 'status' => 'submit']);
    }

    /** ไม่อนุมัติ (รออนุมัติ -> ไม่อนุมัติ) พร้อมเหตุผล */
    public function actionReject($id)
    {
        $model = $this->findModel($id);
        if ($model->status === 'submit') {
            $model->status = 'reject';
            $reason = trim((string) $this->request->post('reason', ''));
            $this->stampDecision($model, $reason);
            $model->save(false);
            Yii::$app->session->setFlash('success', 'บันทึกผลไม่อนุมัติเรียบร้อย');
        }
        return $this->redirect(['index', 'thai_year' => $model->thai_year, 'status' => 'submit']);
    }

    /** เก็บผู้อนุมัติ/เหตุผลลง data_json (คอลัมน์ json) */
    private function stampDecision(PlanOrder $model, $reason)
    {
        $json = $model->data_json;
        if (!is_array($json)) {
            $json = json_decode((string) $json, true) ?: [];
        }
        $json['approver_id'] = Yii::$app->user->id;
        $json['decided_at']  = date('Y-m-d H:i:s');
        if ($reason !== null && $reason !== '') {
            $json['reject_reason'] = $reason;
        }
        $model->data_json = $json;
    }

    private function yearOptions()
    {
        $years = PlanOrder::find()
            ->select('thai_year')
            ->distinct()
            ->column();
        $years[] = AppHelper::YearBudget() + 1;
        $years[] = AppHelper::YearBudget();
        $years = array_values(array_unique(array_filter($years)));
        rsort($years);
        return $years;
    }

    protected function findModel($id)
    {
        $model = PlanOrder::findOne(['id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบแผนที่ต้องการ');
        }
        return $model;
    }
}

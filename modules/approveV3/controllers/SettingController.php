<?php

namespace app\modules\approveV3\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\ApproveLevelResolver;
use app\modules\approveV3\models\ApproveLevelSetting;
use app\modules\hr\models\Employees;

/**
 * ตั้งค่าระดับการอนุมัติของแต่ละระบบ
 */
class SettingController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * รายการระบบ — เลือกไปตั้งระดับการอนุมัติ
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * รายการระดับการอนุมัติของระบบหนึ่ง
     * GET emp_id = รหัสพนักงาน เพื่อแสดงผลระดับการอนุมัติที่ resolve แล้ว (ตรวจสอบตามโครงสร้าง)
     */
    public function actionLevels($system)
    {
        $systemName = ApproveLevelSetting::systemOptions()[$system] ?? $system;
        $models = ApproveLevelSetting::find()
            ->where(['system' => $system])
            ->orderBy(['sort_order' => SORT_ASC, 'level' => SORT_ASC])
            ->all();

        $selectedEmpId = (int) Yii::$app->request->get('emp_id', 0);
        $selectedEmployee = null;
        $resolvedLevels = [];

        if ($selectedEmpId > 0) {
            $selectedEmployee = Employees::findOne($selectedEmpId);
            $resolvedLevels = ApproveLevelResolver::resolve($system, $selectedEmpId);
            foreach ($resolvedLevels as &$r) {
                if (!empty($r['emp_id'])) {
                    $emp = Employees::findOne($r['emp_id']);
                    $r['approver_display'] = $emp ? $emp->fullname : '(รหัส ' . $r['emp_id'] . ')';
                } else {
                    $r['approver_display'] = $r['approver_value'] ? 'บทบาท: ' . $r['approver_value'] : '—';
                }
            }
            unset($r);
        }

        return $this->render('levels', [
            'system' => $system,
            'systemName' => $systemName,
            'models' => $models,
            'selectedEmpId' => $selectedEmpId,
            'selectedEmployee' => $selectedEmployee,
            'resolvedLevels' => $resolvedLevels,
        ]);
    }

    /**
     * สร้างระดับการอนุมัติ
     */
    public function actionCreateLevel($system)
    {
        $model = new ApproveLevelSetting([
            'system' => $system,
            'active' => 1,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกแล้ว');
            return $this->redirect(['levels', 'system' => $system]);
        }

        return $this->render('_form_level', [
            'model' => $model,
            'system' => $system,
        ]);
    }

    /**
     * แก้ไขระดับการอนุมัติ
     */
    public function actionUpdateLevel($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกแล้ว');
            return $this->redirect(['levels', 'system' => $model->system]);
        }

        return $this->render('_form_level', [
            'model' => $model,
            'system' => $model->system,
        ]);
    }

    /**
     * ลบระดับการอนุมัติ
     */
    public function actionDeleteLevel($id)
    {
        $model = $this->findModel($id);
        $system = $model->system;
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบแล้ว');
        return $this->redirect(['levels', 'system' => $system]);
    }

    protected function findModel($id)
    {
        $model = ApproveLevelSetting::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('ไม่พบรายการ');
        }
        return $model;
    }
}

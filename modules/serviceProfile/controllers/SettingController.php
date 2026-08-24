<?php

namespace app\modules\serviceProfile\controllers;

use app\modules\hr\models\Employees;
use app\modules\serviceProfile\models\ServiceProfileQualityReviewer;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\components\AppHelper;
use app\modules\serviceProfile\services\OwnerDirectoryService;

class SettingController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['serviceProfileAdmin']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete-reviewer' => ['POST']]],
        ]);
    }
    public function actionReviewers()
    {
        $model = new ServiceProfileQualityReviewer(['owner_type' => 'department', 'active' => 1]);
        $selectedOrgUnitId = 0;
        if ($model->load(Yii::$app->request->post())) {
            $selectedOrgUnitId = (int) $model->owner_id;
            try {
                $resolved = (new OwnerDirectoryService())->resolveOwner($selectedOrgUnitId, (int) AppHelper::YearBudget());
                $model->owner_type = $resolved['owner_type'];
                $model->owner_id = $resolved['owner_id'];
            } catch (\DomainException $e) {
                $model->addError('owner_id', $e->getMessage());
            }
        }
        if (!$model->hasErrors() && Yii::$app->request->isPost && $model->validate()) {
            $tx = Yii::$app->db->beginTransaction();
            try {
                $existing = ServiceProfileQualityReviewer::findOne([
                    'owner_type' => $model->owner_type,
                    'owner_id' => $model->owner_id,
                    'employee_id' => $model->employee_id,
                ]);
                $reviewer = $existing ?? $model;
                $reviewer->active = 1;
                $reviewer->is_lead = (bool) $model->is_lead;
                $reviewer->effective_to = null;

                if ($reviewer->isNewRecord) {
                    $reviewer->created_at = date('Y-m-d H:i:s');
                    $reviewer->created_by = Yii::$app->user->id;
                }
                if ($reviewer->is_lead) {
                    ServiceProfileQualityReviewer::updateAll(['is_lead' => 0], [
                        'owner_type' => $reviewer->owner_type,
                        'owner_id' => $reviewer->owner_id,
                    ]);
                }
                if (!$reviewer->save()) {
                    throw new \RuntimeException(implode(' ', $reviewer->getFirstErrors()));
                }
                $tx->commit();
                Yii::$app->session->setFlash('success', $existing
                    ? 'บุคคลนี้เป็นผู้แทนคุณภาพอยู่แล้ว ระบบได้อัปเดตสถานะให้แล้ว'
                    : 'เพิ่มผู้แทนคุณภาพแล้ว');
                return $this->refresh();
            } catch (\Throwable $e) {
                $tx->rollBack();
                $model->addError('employee_id', 'ไม่สามารถบันทึกผู้แทนคุณภาพได้: ' . $e->getMessage());
            }
        }
        $rows = ServiceProfileQualityReviewer::find()->with('employee')->orderBy(['owner_id' => SORT_ASC, 'is_lead' => SORT_DESC])->all();
        $employeeOptions = ArrayHelper::map(Employees::find()->where(['status' => 1])->orderBy(['fname' => SORT_ASC])->all(), 'id', static fn($employee) => $employee->fullname());
        $directory = new OwnerDirectoryService();
        $owners = [];
        foreach ($rows as $row) $owners[$row->owner_type . ':' . (int) $row->owner_id] = $directory->findOrgUnit($row->owner_type, (int) $row->owner_id, (int) AppHelper::YearBudget());
        if ($selectedOrgUnitId) $model->owner_id = $selectedOrgUnitId;
        return $this->render('reviewers', ['model' => $model, 'rows' => $rows, 'owners' => $owners, 'ownerOptions' => $directory->ownerOptions((int) AppHelper::YearBudget(), $selectedOrgUnitId), 'employeeOptions' => $employeeOptions]);
    }
    public function actionDeleteReviewer($id)
    {
        ServiceProfileQualityReviewer::deleteAll(['id' => $id]);
        Yii::$app->session->setFlash('success', 'นำผู้แทนคุณภาพออกแล้ว');
        return $this->redirect(['reviewers']);
    }
}

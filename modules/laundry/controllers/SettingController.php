<?php

namespace app\modules\laundry\controllers;

use app\modules\hr\models\Organization;
use app\modules\laundry\models\LaundryUnit;
use app\modules\settings\models\OrgUnit;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ตั้งค่างานซักฟอก — ทะเบียนหน่วยงานซักฟอก (คุมการ์ดในหน้ารับผ้า/ตรวจรับ/จ่าย)
 */
class SettingController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['unit'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['unit-save', 'unit-delete'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['unit-save' => ['POST'], 'unit-delete' => ['POST']],
            ],
        ];
    }

    public function actionUnit()
    {
        $units = LaundryUnit::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        // ตัวเลือกหน่วยงานมาตรฐาน ERP: org_unit จัดกลุ่ม+เยื้องระดับ (value = org_unit_id)
        $thaiYear = (int) date('Y') + 543;
        $ouGroups = OrgUnit::groupedForSelect($thaiYear);
        return $this->render('unit', compact('units', 'ouGroups'));
    }

    public function actionUnitSave()
    {
        $req = Yii::$app->request;
        $id = (int) $req->post('id');
        $model = $id ? $this->findUnit($id) : new LaundryUnit(['is_active' => 1, 'sort_order' => 0]);
        if (!$id) {
            // แปลง org_unit_id (มาตรฐาน) → tree_id (ให้ตรงกับ department_id ในบัญชีผ้า)
            $ou = OrgUnit::findOne((int) $req->post('org_unit_id'));
            $treeId = $ou && $ou->ref_id !== null ? (int) $ou->ref_id : 0;
            if (!$treeId) {
                Yii::$app->session->setFlash('error', 'หน่วยงานนี้ไม่ผูกกับโครงสร้างจริง เลือกหน่วยงานในผังองค์กร');
                return $this->redirect(['unit']);
            }
            $model->tree_id = $treeId;
            $model->created_at = date('Y-m-d H:i:s');
            $model->created_by = Yii::$app->user->id ? (int) Yii::$app->user->id : null;
        }
        $model->abbr = trim((string) $req->post('abbr')) ?: null;
        $model->sort_order = (int) $req->post('sort_order');
        $model->is_active = $req->post('is_active') ? 1 : 0;
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกหน่วยงานแล้ว');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()) ?: 'บันทึกไม่สำเร็จ');
        }
        return $this->redirect(['unit']);
    }

    public function actionUnitDelete()
    {
        $model = $this->findUnit((int) Yii::$app->request->post('id'));
        $model->delete();
        Yii::$app->session->setFlash('success', 'ลบหน่วยงานออกจากทะเบียนแล้ว');
        return $this->redirect(['unit']);
    }

    private function findUnit(int $id): LaundryUnit
    {
        $model = LaundryUnit::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหน่วยงาน');
        }
        return $model;
    }
}

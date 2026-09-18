<?php

namespace app\modules\laundry\controllers;

use app\modules\am\models\Asset;
use app\modules\hr\models\Organization;
use app\modules\laundry\models\LaundryUnit;
use app\modules\settings\models\OrgUnit;
use Yii;
use yii\db\Query;
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
                    ['allow' => true, 'actions' => ['unit', 'machine'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['unit-save', 'unit-delete', 'machine-save', 'machine-delete'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['unit-save' => ['POST'], 'unit-delete' => ['POST'], 'machine-save' => ['POST'], 'machine-delete' => ['POST']],
            ],
        ];
    }

    /** ตั้งค่าเครื่องซัก-อบ (เชื่อมครุภัณฑ์ + รายการเครื่อง) — ย้ายมาจากหน้าปฏิบัติงาน */
    public function actionMachine()
    {
        $machines = (new Query())->select(['m.*', 'asset_name' => 'a.asset_name', 'asset_code' => 'a.code', 'lifecycle_status' => 'a.lifecycle_status'])
            ->from(['m' => 'laundry_machine'])->innerJoin(['a' => Asset::tableName()], 'a.id = m.asset_id')
            ->orderBy(['m.machine_type' => SORT_ASC, 'a.code' => SORT_ASC])->all();
        return $this->render('machine', compact('machines'));
    }

    public function actionMachineSave()
    {
        $req = Yii::$app->request;
        $id = (int) $req->post('id');
        $machine = (new Query())->from('laundry_machine')->where(['id' => $id])->one();
        if (!$machine) {
            throw new NotFoundHttpException('ไม่พบเครื่อง');
        }
        $cap = (float) $req->post('capacity_kg');
        if ($cap <= 0) {
            Yii::$app->session->setFlash('error', 'กำลังเครื่องต้องมากกว่า 0');
            return $this->redirect(['machine']);
        }
        Yii::$app->db->createCommand()->update('laundry_machine', [
            'capacity_kg' => number_format(round($cap, 3), 3, '.', ''),
            'is_active' => $req->post('is_active') ? 1 : 0,
        ], ['id' => $id])->execute();
        Yii::$app->session->setFlash('success', 'บันทึกเครื่องแล้ว');
        return $this->redirect(['machine']);
    }

    public function actionMachineDelete()
    {
        $id = (int) Yii::$app->request->post('id');
        $machine = (new Query())->from('laundry_machine')->where(['id' => $id])->one();
        if (!$machine) {
            throw new NotFoundHttpException('ไม่พบเครื่อง');
        }
        $running = (new Query())->from('laundry_processing_batch')
            ->where(['asset_id' => $machine['asset_id'], 'status' => 'RUNNING'])->exists();
        if ($running) {
            Yii::$app->session->setFlash('error', 'เครื่องกำลังทำงานอยู่ ลบไม่ได้');
            return $this->redirect(['machine']);
        }
        Yii::$app->db->createCommand()->delete('laundry_machine', ['id' => $id])->execute();
        Yii::$app->session->setFlash('success', 'ลบเครื่องออกจากทะเบียนแล้ว');
        return $this->redirect(['machine']);
    }

    public function actionUnit()
    {
        $units = LaundryUnit::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        // ตัวเลือกหน่วยงานมาตรฐาน ERP: org_unit จัดกลุ่ม+เยื้องระดับ (value = org_unit_id)
        $thaiYear = \app\modules\plan\components\PlanHelper::currentPlanYear();
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

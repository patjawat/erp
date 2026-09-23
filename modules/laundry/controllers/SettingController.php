<?php

namespace app\modules\laundry\controllers;

use app\modules\am\models\Asset;
use app\modules\hr\models\Organization;
use app\modules\laundry\models\LaundryExternalSource;
use app\modules\laundry\models\LaundryItem;
use app\modules\laundry\models\LaundryItemCategory;
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
                    ['allow' => true, 'actions' => ['unit', 'machine', 'item', 'external'], 'roles' => ['laundry.view']],
                    ['allow' => true, 'actions' => ['unit-save', 'unit-delete', 'machine-save', 'machine-delete', 'item-save', 'item-delete', 'category-save', 'category-delete', 'external-save', 'external-delete'], 'roles' => ['laundry.manage']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['unit-save' => ['POST'], 'unit-delete' => ['POST'], 'machine-save' => ['POST'], 'machine-delete' => ['POST'], 'item-save' => ['POST'], 'item-delete' => ['POST'], 'category-save' => ['POST'], 'category-delete' => ['POST'], 'external-save' => ['POST'], 'external-delete' => ['POST']],
            ],
        ];
    }

    /** ตั้งค่าประเภทผ้า (ผ้าห่ม/ผ้าปูเตียง/เสื้อ/กางเกง ฯลฯ) */
    public function actionItem()
    {
        $items = LaundryItem::find()->orderBy(['is_active' => SORT_DESC, 'item_name' => SORT_ASC])->all();
        $categories = LaundryItemCategory::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();
        // จัดกลุ่มรายการตามหมวด (แสดงเป็นหัวข้อในตาราง)
        $grouped = LaundryItemCategory::group(array_map(static fn($it) => ['category_id' => $it->category_id, 'model' => $it], $items));
        return $this->render('item', compact('items', 'categories', 'grouped'));
    }

    public function actionItemSave()
    {
        $req = Yii::$app->request;
        $id = (int) $req->post('id');
        $model = $id ? (LaundryItem::findOne($id) ?: new LaundryItem()) : new LaundryItem();
        $model->item_name = trim((string) $req->post('item_name'));
        $model->is_active = $req->post('is_active') ? 1 : 0;
        if ($req->post('category_id') !== null) {
            $model->category_id = (int) $req->post('category_id') ?: null;
        }
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกประเภทผ้าแล้ว');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()) ?: 'บันทึกไม่สำเร็จ');
        }
        return $this->redirect(['item']);
    }

    public function actionItemDelete()
    {
        $id = (int) Yii::$app->request->post('id');
        $model = LaundryItem::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบประเภทผ้า');
        }
        // ถ้ามีการใช้งานแล้ว (เดินสต็อก/ตรวจนับ/ยอดตั้งต้น) ให้ปิดใช้แทนลบ (กันข้อมูลอ้างอิงพัง)
        $used = (new Query())->from('laundry_piece_event')->where(['item_id' => $id])->exists()
            || (new Query())->from('laundry_unit_count_line')->where(['item_id' => $id])->exists()
            || (new Query())->from('laundry_par')->where(['item_id' => $id])->exists();
        if ($used) {
            $model->is_active = 0;
            $model->save(false);
            Yii::$app->session->setFlash('success', 'ประเภทผ้านี้มีการใช้งานแล้ว จึงปิดใช้งานแทนการลบ');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบประเภทผ้าแล้ว');
        }
        return $this->redirect(['item']);
    }

    /** เพิ่ม/แก้หมวดประเภทผ้า (เช่น ผ้าของโรงพยาบาล / ผ้าจากหน่วยงานภายนอก) */
    public function actionCategorySave()
    {
        $req = Yii::$app->request;
        $id = (int) $req->post('id');
        $model = $id ? LaundryItemCategory::findOne($id) : new LaundryItemCategory(['is_active' => 1]);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหมวดผ้า');
        }
        if (!$id) {
            $model->created_by = Yii::$app->user->id ? (int) Yii::$app->user->id : null;
        }
        $model->name = trim((string) $req->post('name'));
        $model->sort_order = (int) $req->post('sort_order');
        if ($req->post('is_active') !== null) {
            $model->is_active = $req->post('is_active') ? 1 : 0;
        }
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกหมวดผ้าแล้ว');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()) ?: 'บันทึกไม่สำเร็จ');
        }
        return $this->redirect(['item']);
    }

    public function actionCategoryDelete()
    {
        $model = LaundryItemCategory::findOne((int) Yii::$app->request->post('id'));
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหมวดผ้า');
        }
        // มีประเภทผ้าอยู่ในหมวด → ปิดใช้แทนลบ (ประเภทผ้าไม่หลุดหมวด)
        if (LaundryItem::find()->where(['category_id' => $model->id])->exists()) {
            $model->is_active = 0;
            $model->save(false);
            Yii::$app->session->setFlash('success', 'หมวดนี้มีประเภทผ้าอยู่ จึงปิดใช้งานแทนการลบ');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบหมวดผ้าแล้ว');
        }
        return $this->redirect(['item']);
    }

    /** ทะเบียนหน่วยงานภายนอกที่ส่งผ้ามาให้ (เช่น รพ.เลย) — ใช้ในหน้า นับ–รีด–QC */
    public function actionExternal()
    {
        $sources = LaundryExternalSource::find()->orderBy(['is_active' => SORT_DESC, 'sort_order' => SORT_ASC, 'name' => SORT_ASC])->all();
        // จำนวนครั้ง/ชิ้นที่รับมาแล้ว (ต่อหน่วยงาน)
        $usage = (new Query())->select([
                'external_source_id', 'times' => new \yii\db\Expression('COUNT(DISTINCT f.id)'),
                'pieces' => new \yii\db\Expression('COALESCE(SUM(l.qty),0)'),
            ])
            ->from(['f' => 'laundry_finish'])->leftJoin(['l' => 'laundry_finish_line'], 'l.finish_id = f.id')
            ->where(['f.source_type' => 'EXTERNAL'])->andWhere(['not', ['f.external_source_id' => null]])
            ->groupBy('f.external_source_id')->indexBy('external_source_id')->all();
        return $this->render('external', compact('sources', 'usage'));
    }

    public function actionExternalSave()
    {
        $req = Yii::$app->request;
        $id = (int) $req->post('id');
        $model = $id ? LaundryExternalSource::findOne($id) : new LaundryExternalSource(['is_active' => 1]);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหน่วยงานภายนอก');
        }
        if (!$id) {
            $model->created_by = Yii::$app->user->id ? (int) Yii::$app->user->id : null;
        }
        $model->name = (string) $req->post('name');
        $model->sort_order = (int) $req->post('sort_order');
        if ($req->post('is_active') !== null) {
            $model->is_active = $req->post('is_active') ? 1 : 0;
        }
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกหน่วยงานภายนอกแล้ว');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()) ?: 'บันทึกไม่สำเร็จ');
        }
        return $this->redirect(['external']);
    }

    public function actionExternalDelete()
    {
        $model = LaundryExternalSource::findOne((int) Yii::$app->request->post('id'));
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบหน่วยงานภายนอก');
        }
        // เคยรับผ้าแล้ว → ปิดใช้แทนลบ (ประวัติยังอ้างชื่อได้)
        if ((new Query())->from('laundry_finish')->where(['external_source_id' => $model->id])->exists()) {
            $model->is_active = 0;
            $model->save(false);
            Yii::$app->session->setFlash('success', 'หน่วยงานนี้มีประวัติรับผ้าแล้ว จึงปิดใช้งานแทนการลบ');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบหน่วยงานภายนอกแล้ว');
        }
        return $this->redirect(['external']);
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

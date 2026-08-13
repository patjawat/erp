<?php

namespace app\modules\roster\controllers;

use app\components\ModalHelper;
use app\modules\roster\helpers\RosterAccess;
use app\modules\roster\models\ShiftType;
use app\modules\roster\models\UnitRule;
use app\modules\roster\models\UnitShift;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * ตั้งค่าตารางเวร
 *
 *   shift-type  ประเภทเวรกลาง ช/บ/ด/ควบ — ผู้ดูแลเท่านั้น (กระทบทุกหน่วย)
 *   unit        เวลาเข้า-ออก + จำนวนคนที่ต้องการ ของหน่วยนั้น — หัวหน้าหน่วยตั้งเองได้
 *   rule        กฎการจัดเวรของหน่วย — หัวหน้าหน่วยตั้งเองได้
 */
class SettingController extends Controller
{
    /** บังคับล็อกอินเอง เพราะโมดูลอยู่ใน allowActions ระดับแอป */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'authOnly' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ]);
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        if (!RosterAccess::canEnter()) {
            $this->redirect(['/roster/default/no-access'])->send();
            return false;
        }
        return true;
    }

    // ── ประเภทเวร ────────────────────────────────────────────────────────────

    public function actionShiftType()
    {
        $this->requireManager();
        $dataProvider = new ActiveDataProvider([
            'query' => ShiftType::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]),
            'pagination' => false,
        ]);
        return $this->render('shift-type', ['dataProvider' => $dataProvider]);
    }

    public function actionShiftTypeForm($id = null)
    {
        $this->requireManager();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $id ? $this->findShiftType((int) $id) : new ShiftType(['active' => 1, 'color' => 'secondary']);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return ['status' => 'success', 'container' => '#roster-setting'];
        }
        return [
            'title' => ($id ? 'แก้ไข' : 'เพิ่ม') . 'ประเภทเวร',
            'content' => $this->renderAjax('_shift_type_form', ['model' => $model]),
            'footer' => ModalHelper::modalFooterSaveClose(),
        ];
    }

    public function actionShiftTypeDelete($id)
    {
        $this->requireManager();
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->findShiftType((int) $id);
        // ปิดใช้งานแทนการลบ เพราะตารางเวรเดิมยังอ้างถึงอยู่
        $model->active = 0;
        $model->save(false);
        return ['status' => 'success', 'container' => '#roster-setting'];
    }

    // ── เวลาเวร + จำนวนคน รายหน่วยงาน ─────────────────────────────────────────

    public function actionUnit($unit_id = null)
    {
        $units = RosterAccess::unitOptions();
        $unitId = (int) ($unit_id ?: array_key_first($units) ?: 0);
        if ($unitId && !RosterAccess::canManageUnit($unitId)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ตั้งค่าหน่วยงานนี้');
        }
        return $this->render('unit', [
            'units' => $units,
            'unitId' => $unitId,
            'types' => ShiftType::activeList(),
            'shifts' => $unitId ? UnitShift::listForUnit($unitId) : [],
        ]);
    }

    /** ฟอร์มเพิ่ม/แก้ไขเวรของหน่วยงาน */
    public function actionShiftForm($unit_id = null, $id = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $id ? UnitShift::findOne((int) $id) : new UnitShift([
            'unit_id' => (int) $unit_id,
            'active' => 1,
            'required_staff' => 1,
            'pay_unit' => UnitShift::PAY_PER_SHIFT,
        ]);
        if (!$model) {
            return ['status' => 'error', 'message' => 'ไม่พบเวร'];
        }
        if (!RosterAccess::canManageUnit((int) $model->unit_id)) {
            return ['status' => 'error', 'message' => 'คุณไม่มีสิทธิ์ตั้งค่าหน่วยงานนี้'];
        }

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->hours = null; // ให้ beforeSave คำนวณใหม่จากเวลาที่กรอก
            if ($model->save()) {
                return ['status' => 'success', 'container' => '#roster-setting'];
            }
            $first = array_values($model->getFirstErrors());
            return ['status' => 'error', 'message' => $first[0] ?? 'บันทึกไม่สำเร็จ'];
        }

        // อักษรย่อที่หน่วยนี้ใช้ไปแล้ว — ส่งไปให้ฟอร์มเตือนตั้งแต่ตอนพิมพ์ ไม่ต้องรอกดบันทึก
        $taken = [];
        foreach (UnitShift::listForUnit((int) $model->unit_id, false) as $other) {
            if ((int) $other->id !== (int) $model->id && $other->short_name) {
                $taken[$other->short_name] = $other->displayName();
            }
        }

        return [
            'title' => ($id ? 'แก้ไข' : 'เพิ่ม') . 'เวรของหน่วยงาน',
            'content' => $this->renderAjax('_unit_shift_form', [
                'model' => $model,
                'types' => ShiftType::activeList(),
                'takenShorts' => $taken,
            ]),
            'footer' => ModalHelper::modalFooterSaveClose(),
        ];
    }

    /** ปิดใช้งานเวร — ไม่ลบ เพราะตารางเวรเดิมยังอ้างถึง */
    public function actionShiftDisable($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = UnitShift::findOne((int) $id);
        if (!$model || !RosterAccess::canManageUnit((int) $model->unit_id)) {
            return ['status' => 'error', 'message' => 'ไม่พบเวร หรือไม่มีสิทธิ์'];
        }
        $model->active = $model->active ? 0 : 1;
        $model->save(false);
        return ['status' => 'success', 'container' => '#roster-setting'];
    }

    // ── กฎการจัดเวร รายหน่วยงาน ───────────────────────────────────────────────

    public function actionRule($unit_id = null)
    {
        $units = RosterAccess::unitOptions();
        $unitId = (int) ($unit_id ?: array_key_first($units) ?: 0);
        if ($unitId && !RosterAccess::canManageUnit($unitId)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์ตั้งค่าหน่วยงานนี้');
        }
        return $this->render('rule', [
            'units' => $units,
            'unitId' => $unitId,
            'grouped' => $unitId ? UnitRule::groupedForUnit($unitId) : [],
            'types' => ShiftType::activeList(),
            'hasAny' => $unitId ? UnitRule::find()->where(['unit_id' => $unitId])->exists() : false,
        ]);
    }

    /** ใส่กฎชุดแนะนำให้หน่วยที่ยังไม่เคยตั้ง */
    public function actionRuleSeed($unit_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $unitId = (int) $unit_id;
        if (!RosterAccess::canManageUnit($unitId)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์ตั้งค่าหน่วยงานนี้'];
        }
        $created = UnitRule::seedDefaults($unitId);
        return $created > 0
            ? ['status' => 'success', 'message' => "เพิ่มกฎแนะนำ $created ข้อ"]
            : ['status' => 'error', 'message' => 'หน่วยงานนี้มีกฎอยู่แล้ว'];
    }

    public function actionRuleSave()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $unitId = (int) $this->request->post('unit_id');
        if (!$unitId || !RosterAccess::canManageUnit($unitId)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์ตั้งค่าหน่วยงานนี้'];
        }
        $rows = (array) $this->request->post('rule', []);
        $saved = 0;
        foreach ($rows as $ruleId => $row) {
            $model = UnitRule::findOne(['id' => (int) $ruleId, 'unit_id' => $unitId]);
            if (!$model) {
                continue;
            }
            $model->active = !empty($row['active']) ? 1 : 0;
            $value = $row['int_value'] ?? null;
            $model->int_value = ($value === '' || $value === null) ? null : (int) $value;
            if ($model->save()) {
                $saved++;
            }
        }
        return ['status' => 'success', 'message' => "บันทึกแล้ว $saved ข้อ"];
    }

    public function actionRuleDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = UnitRule::findOne((int) $id);
        if (!$model || !RosterAccess::canManageUnit((int) $model->unit_id)) {
            return ['status' => 'error', 'message' => 'ไม่พบกฎ หรือไม่มีสิทธิ์'];
        }
        $model->delete();
        return ['status' => 'success', 'container' => '#roster-setting'];
    }

    /** เพิ่มกฎคู่เวร (ห้ามวันเดียวกัน / ห้ามวันถัดไป) */
    public function actionRulePair()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $unitId = (int) $this->request->post('unit_id');
        $key = (string) $this->request->post('rule_key');
        $a = (int) $this->request->post('a');
        $b = (int) $this->request->post('b');
        if (!RosterAccess::canManageUnit($unitId)) {
            return ['status' => 'error', 'message' => 'ไม่มีสิทธิ์ตั้งค่าหน่วยงานนี้'];
        }
        if (!in_array($key, [UnitRule::KEY_FORBID_SAME_DAY, UnitRule::KEY_FORBID_NEXT_DAY], true)) {
            return ['status' => 'error', 'message' => 'ชนิดกฎไม่ถูกต้อง'];
        }
        if (!$a || !$b || $a === $b) {
            return ['status' => 'error', 'message' => 'เลือกเวร 2 ชนิดที่ต่างกัน'];
        }
        $rule = new UnitRule([
            'unit_id' => $unitId,
            'rule_key' => $key,
            'data_json' => ['a' => $a, 'b' => $b],
            'active' => 1,
        ]);
        if (!$rule->save()) {
            return ['status' => 'error', 'message' => 'บันทึกไม่สำเร็จ'];
        }
        return ['status' => 'success', 'container' => '#roster-setting'];
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function requireManager(): void
    {
        if (!RosterAccess::isGlobalViewer()) {
            throw new ForbiddenHttpException('เฉพาะผู้ดูแลตารางเวรเท่านั้นที่แก้ประเภทเวรได้');
        }
    }

    private function findShiftType(int $id): ShiftType
    {
        $model = ShiftType::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('ไม่พบประเภทเวร');
        }
        return $model;
    }
}

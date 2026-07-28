<?php

namespace app\modules\kpi\controllers;

use app\modules\hr\models\Employees;
use app\modules\kpi\models\KpiCycle;
use app\modules\kpi\models\KpiEntry;
use app\modules\kpi\models\KpiItem;
use app\modules\kpi\services\KpiService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * หน้าจัดการ KPI รายบุคคลแบบเต็ม (แยกจากหน้าโปรไฟล์) — เพิ่ม/แก้ KPI เป้าหมาย น้ำหนัก และบันทึกผลงาน
 * เลียนแบบรูปแบบหน้าจัดการ JD (/jd/employee-jd/view)
 */
class ManageController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create-cycle' => ['post'],
                    'add-item' => ['post'],
                    'update-item' => ['post'],
                    'remove-item' => ['post'],
                    'approve' => ['post'],
                    'save-entries' => ['post'],
                ],
            ],
        ];
    }

    /** หน้าจัดการหลัก */
    public function actionView(int $emp_id, ?int $fiscal_year = null)
    {
        $employee = $this->findEmployee($emp_id);
        if (!KpiService::canViewEmp($emp_id)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าดู KPI ของบุคลากรคนนี้');
        }

        $cycles = KpiCycle::find()
            ->where(['emp_id' => $emp_id])
            ->orderBy(['fiscal_year' => SORT_DESC, 'id' => SORT_DESC])
            ->all();

        $currentFy = KpiService::currentFiscalYear();
        $fy = (int) $fiscal_year;
        if ($fy <= 0) {
            $fy = $cycles[0]->fiscal_year ?? $currentFy;
        }

        $cycle = null;
        foreach ($cycles as $c) {
            if ((int) $c->fiscal_year === $fy) {
                $cycle = $c;
                break;
            }
        }

        $items = [];
        $entries = [];
        if ($cycle) {
            $items = KpiItem::find()
                ->where(['cycle_id' => $cycle->id])
                ->orderBy(['status' => SORT_ASC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
            if ($items) {
                foreach (KpiEntry::find()
                    ->where(['kpi_item_id' => array_column($items, 'id'), 'period_type' => KpiEntry::PERIOD_MONTH])
                    ->all() as $e) {
                    $entries[$e->kpi_item_id][$e->period_index] = $e;
                }
            }
        }

        return $this->render('view', [
            'employee' => $employee,
            'cycles' => $cycles,
            'cycle' => $cycle,
            'fiscalYear' => $fy,
            'currentFy' => $currentFy,
            'items' => $items,
            'entries' => $entries,
            'canManage' => KpiService::isHrOrAdmin() || KpiService::isSupervisorOf($emp_id),
            'canRecord' => $cycle ? KpiService::canRecord($cycle) : false,
        ]);
    }

    /** สร้างชุด KPI ประจำปี + seed จาก JD */
    public function actionCreateCycle(int $emp_id, int $fiscal_year)
    {
        $this->findEmployee($emp_id);
        if (!KpiService::isHrOrAdmin() && !KpiService::isSupervisorOf($emp_id)) {
            throw new ForbiddenHttpException('เฉพาะหัวหน้าหน่วยงานหรือ HR เท่านั้นที่สร้างชุด KPI ได้');
        }
        try {
            KpiService::createCycleFromJd($emp_id, $fiscal_year);
            Yii::$app->session->setFlash('success', 'สร้างชุด KPI ปีงบประมาณ ' . $fiscal_year . ' แล้ว (ดึงตั้งต้นจาก JD)');
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(['view', 'emp_id' => $emp_id, 'fiscal_year' => $fiscal_year]);
    }

    /** เพิ่ม KPI เปล่า 1 แถวในชุด (หัวหน้า/HR) */
    public function actionAddItem(int $cycle_id)
    {
        $cycle = $this->findCycle($cycle_id);
        $this->assertManage($cycle);

        $maxSort = (int) KpiItem::find()->where(['cycle_id' => $cycle->id])->max('sort_order');
        $item = new KpiItem([
            'cycle_id' => $cycle->id,
            'source_type' => KpiItem::SOURCE_MANUAL,
            'indicator' => 'ตัวชี้วัดใหม่',
            'value_type' => KpiItem::TYPE_NUMERIC,
            'frequency' => KpiItem::FREQ_MONTHLY,
            'weight' => 0,
            'sort_order' => $maxSort + 10,
            'status' => KpiItem::STATUS_ACTIVE,
        ]);
        $item->save();
        Yii::$app->session->setFlash('success', 'เพิ่ม KPI แล้ว — กรุณากรอกรายละเอียดและกดบันทึก');
        return $this->redirect(['view', 'emp_id' => $cycle->emp_id, 'fiscal_year' => $cycle->fiscal_year]);
    }

    /** บันทึกรายละเอียด KPI ทีละตัว (indicator/target/weight/type/frequency) — หัวหน้า/HR */
    public function actionUpdateItem(int $id)
    {
        $item = KpiItem::findOne($id);
        if (!$item) {
            throw new NotFoundHttpException('ไม่พบ KPI');
        }
        $cycle = $this->findCycle((int) $item->cycle_id);
        $this->assertManage($cycle);
        $post = Yii::$app->request->post();

        $item->indicator = trim((string) ($post['indicator'] ?? $item->indicator)) ?: $item->indicator;
        $item->target_text = trim((string) ($post['target_text'] ?? '')) ?: null;
        $tv = $post['target_value'] ?? '';
        $item->target_value = ($tv === '' || $tv === null) ? null : (float) $tv;
        $item->unit = trim((string) ($post['unit'] ?? '')) ?: null;
        $item->value_type = in_array($post['value_type'] ?? '', [KpiItem::TYPE_NUMERIC, KpiItem::TYPE_QUALITATIVE], true)
            ? $post['value_type'] : $item->value_type;
        $item->frequency = in_array($post['frequency'] ?? '', [KpiItem::FREQ_MONTHLY, KpiItem::FREQ_QUARTERLY, KpiItem::FREQ_YEARLY], true)
            ? $post['frequency'] : $item->frequency;
        $item->aggregation = in_array($post['aggregation'] ?? '', [KpiItem::AGG_SUM, KpiItem::AGG_AVG, KpiItem::AGG_MIN, KpiItem::AGG_MAX, KpiItem::AGG_LAST], true)
            ? $post['aggregation'] : $item->aggregation;
        $item->direction = in_array($post['direction'] ?? '', [KpiItem::DIR_ASC, KpiItem::DIR_DESC], true)
            ? $post['direction'] : $item->direction;
        foreach ([1, 2, 3, 4, 5] as $l) {
            $lv = $post['level' . $l] ?? '';
            $item->{'level' . $l} = ($lv === '' || $lv === null) ? null : (float) $lv;
        }
        $w = $post['weight'] ?? '';
        $item->weight = ($w === '' || $w === null) ? 0 : (float) $w;

        if ($item->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกรายละเอียด KPI แล้ว');
        } else {
            Yii::$app->session->setFlash('error', 'บันทึกไม่สำเร็จ: ' . json_encode($item->getErrors(), JSON_UNESCAPED_UNICODE));
        }
        return $this->redirect(['view', 'emp_id' => $cycle->emp_id, 'fiscal_year' => $cycle->fiscal_year]);
    }

    /** ยกเลิก KPI กลางปี (soft remove — เก็บผลเดิมไว้) */
    public function actionRemoveItem(int $id)
    {
        $item = KpiItem::findOne($id);
        if (!$item) {
            throw new NotFoundHttpException('ไม่พบ KPI');
        }
        $cycle = $this->findCycle((int) $item->cycle_id);
        $this->assertManage($cycle);

        $item->status = KpiItem::STATUS_REMOVED;
        $item->removed_by = (int) Yii::$app->user->id;
        $item->removed_at = date('Y-m-d H:i:s');
        $item->removed_reason = trim((string) Yii::$app->request->post('reason')) ?: null;
        $item->save(false);
        Yii::$app->session->setFlash('success', 'ยกเลิก KPI แล้ว (ข้อมูลผลงานเดิมยังถูกเก็บไว้)');
        return $this->redirect(['view', 'emp_id' => $cycle->emp_id, 'fiscal_year' => $cycle->fiscal_year]);
    }

    /** อนุมัติชุด KPI ให้เริ่มบันทึกผลได้ (draft → active) */
    public function actionApprove(int $cycle_id)
    {
        $cycle = $this->findCycle($cycle_id);
        $this->assertManage($cycle);
        if ($cycle->status === KpiCycle::STATUS_ACTIVE) {
            Yii::$app->session->setFlash('info', 'ชุดนี้อนุมัติแล้ว');
        } else {
            $cycle->status = KpiCycle::STATUS_ACTIVE;
            $cycle->approved_by = (int) Yii::$app->user->id;
            $cycle->approved_at = date('Y-m-d H:i:s');
            $cycle->save(false);
            Yii::$app->session->setFlash('success', 'อนุมัติชุด KPI แล้ว เจ้าหน้าที่เริ่มบันทึกผลงานได้');
        }
        return $this->redirect(['view', 'emp_id' => $cycle->emp_id, 'fiscal_year' => $cycle->fiscal_year]);
    }

    /** บันทึกผลรายเดือนของ KPI 1 ตัว ทั้ง 12 เดือนพร้อมกัน (เจ้าของ KPI) */
    public function actionSaveEntries(int $kpi_item_id)
    {
        $item = KpiItem::findOne($kpi_item_id);
        if (!$item) {
            throw new NotFoundHttpException('ไม่พบ KPI');
        }
        $cycle = $this->findCycle((int) $item->cycle_id);
        if (!KpiService::canRecord($cycle)) {
            throw new ForbiddenHttpException('คุณไม่มีสิทธิ์บันทึกผลของ KPI นี้');
        }
        // HR/admin แก้ไข/ลงข้อมูลได้ทุกสถานะเพื่อความยืดหยุ่น; เจ้าของต้องรอชุดอนุมัติก่อน
        if ($cycle->status !== KpiCycle::STATUS_ACTIVE && !KpiService::isHrOrAdmin()) {
            Yii::$app->session->setFlash('error', 'ต้องอนุมัติชุด KPI ก่อนจึงจะบันทึกผลได้');
            return $this->redirect(['view', 'emp_id' => $cycle->emp_id, 'fiscal_year' => $cycle->fiscal_year]);
        }

        $nums = (array) Yii::$app->request->post('m', []);   // ผลเชิงตัวเลข ต่อเดือน (fiscal index 1–12)
        $texts = (array) Yii::$app->request->post('mt', []); // ผลเชิงคุณภาพ/หมายเหตุ ต่อเดือน
        $existing = [];
        foreach (KpiEntry::find()->where(['kpi_item_id' => $kpi_item_id, 'period_type' => KpiEntry::PERIOD_MONTH])->all() as $e) {
            $existing[(int) $e->period_index] = $e;
        }

        $now = date('Y-m-d H:i:s');
        $uid = (int) Yii::$app->user->id;
        for ($fi = 1; $fi <= 12; $fi++) {
            $numRaw = $nums[$fi] ?? '';
            $textRaw = trim((string) ($texts[$fi] ?? ''));
            $hasNum = ($numRaw !== '' && $numRaw !== null);
            $hasText = ($textRaw !== '');
            $entry = $existing[$fi] ?? null;

            if (!$hasNum && !$hasText) {
                // ไม่มีข้อมูล — ถ้าเคยมีให้ลบ (เคลียร์ช่อง)
                if ($entry) {
                    $entry->delete();
                }
                continue;
            }
            if (!$entry) {
                $entry = new KpiEntry(['kpi_item_id' => $kpi_item_id, 'period_type' => KpiEntry::PERIOD_MONTH, 'period_index' => $fi]);
            }
            $entry->value_num = $hasNum ? (float) $numRaw : null;
            $entry->value_text = $hasText ? $textRaw : null;
            $entry->recorded_by = $uid;
            $entry->recorded_at = $now;
            if ($entry->confirm_status === KpiEntry::CONFIRM_REVISE) {
                $entry->confirm_status = KpiEntry::CONFIRM_PENDING;
            }
            $entry->save();
        }
        Yii::$app->session->setFlash('success', 'บันทึกผลงานของ “' . $item->indicator . '” แล้ว');
        return $this->redirect(['view', 'emp_id' => $cycle->emp_id, 'fiscal_year' => $cycle->fiscal_year]);
    }

    // ---- helpers ----

    private function findEmployee(int $id): Employees
    {
        $m = Employees::findOne($id);
        if (!$m) {
            throw new NotFoundHttpException('ไม่พบพนักงาน');
        }
        return $m;
    }

    private function findCycle(int $id): KpiCycle
    {
        $c = KpiCycle::findOne($id);
        if (!$c) {
            throw new NotFoundHttpException('ไม่พบชุด KPI');
        }
        return $c;
    }

    private function assertManage(KpiCycle $cycle): void
    {
        if (!KpiService::isHrOrAdmin() && !KpiService::isSupervisorOf((int) $cycle->emp_id)) {
            throw new ForbiddenHttpException('เฉพาะหัวหน้าหน่วยงานหรือ HR เท่านั้นที่จัดการ KPI ได้');
        }
    }
}

<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\pm\components\KpiStatus;
use app\modules\pm\models\KpiGroup;
use app\modules\pm\models\KpiHaPart;
use app\modules\pm\models\KpiIndicator;
use app\modules\pm\models\KpiIndicatorMonth;
use app\modules\pm\models\KpiIndicatorYear;
use app\modules\pm\services\KpiRegistry;
use app\modules\settings\models\OrgUnit;
use app\modules\plan\components\PlanHelper;

/**
 * pill "KPI โรงพยาบาล" — จัดการตัวชี้วัดนอกแผนยุทธศาสตร์ (กลุ่ม 2-5)
 * ดู = pmStrategyView/kpiManage, จัดการ = kpiManage
 * (ตัวชี้วัดยุทธศาสตร์กลุ่ม 1 จัดการที่ strategy-catalog เดิม ไม่แตะ)
 */
class KpiController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [
                ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['pmStrategyView', 'kpiManage']],
                ['allow' => true, 'roles' => ['kpiManage']],
            ]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'delete' => ['POST'], 'copy-year' => ['POST'],
                'group-delete' => ['POST'], 'part-delete' => ['POST'],
            ]],
        ];
    }

    /** คัดลอกค่าเป้าหมายของตัวชี้วัดนอกแผนจากปีหนึ่งไปอีกปีหนึ่ง (ผลจริงเว้นว่างให้กรอกใหม่) */
    public function actionCopyYear()
    {
        $from = (int) Yii::$app->request->post('from_year');
        $to = (int) Yii::$app->request->post('to_year');
        if (!$from || !$to || $from === $to) {
            Yii::$app->session->setFlash('error', 'กรุณาเลือกปีต้นทางและปลายทางที่ต่างกัน');
            return $this->redirect(['index']);
        }

        $stdGroupIds = KpiGroup::find()->select('id')->where(['kind' => KpiGroup::KIND_STANDALONE])->column();
        $indicators = KpiIndicator::find()->where(['group_id' => $stdGroupIds, 'is_active' => true])->all();
        $created = 0; $skipped = 0;
        foreach ($indicators as $ind) {
            $src = $ind->yearEntry($from);
            if (!$src || $src->target_value === null) continue;
            if ($ind->yearEntry($to)) { $skipped++; continue; }
            (new KpiIndicatorYear([
                'kpi_indicator_id' => $ind->id, 'fiscal_year' => $to,
                'target_value' => $src->target_value, 'actual_value' => null,
            ]))->save();
            $created++;
        }
        Yii::$app->session->setFlash('success', "คัดลอกเป้าหมายจากปี $from → ปี $to แล้ว: สร้างใหม่ $created รายการ, ข้าม (มีอยู่แล้ว) $skipped รายการ");
        return $this->redirect(['index', 'year' => $to]);
    }

    public function actionIndex(?int $group = null, ?int $unit = null, ?int $year = null, ?string $status = null, ?int $part = null)
    {
        $year = $year ?: KpiRegistry::defaultFiscalYear();
        $q = trim((string) Yii::$app->request->get('q'));
        $status = in_array($status, [KpiStatus::PASS, KpiStatus::GAP, KpiStatus::NODATA], true) ? $status : null;

        $stdGroupIds = KpiGroup::find()->select('id')->where(['kind' => KpiGroup::KIND_STANDALONE])->column();
        $query = KpiIndicator::find()->with(['group', 'years', 'part'])->where(['group_id' => $stdGroupIds]);
        if ($group) $query->andWhere(['group_id' => $group]);
        if ($part) $query->andWhere(['ha_part_id' => $part]);
        if ($unit) $query->andWhere(['org_unit_id' => $unit]);
        if ($q !== '') $query->andWhere(['like', 'name', $q]);
        $all = $query->orderBy(['group_id' => SORT_ASC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();

        // สถานะรายตัว (คำนวณครั้งเดียว) + สรุปจากทั้งหมด แล้วค่อยกรองรายการที่แสดงตามการ์ดที่คลิก
        $statusMap = [];
        $summary = ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0];
        foreach ($all as $ind) {
            $st = $ind->statusFor($year);
            $statusMap[$ind->id] = $st;
            $summary['total']++;
            $summary[$st]++;
        }
        $indicators = $status ? array_values(array_filter($all, static fn ($ind) => $statusMap[$ind->id] === $status)) : $all;

        return $this->render('index', [
            'indicators' => $indicators,
            'statusMap' => $statusMap,
            'year' => $year,
            'q' => $q,
            'group' => $group,
            'unit' => $unit,
            'part' => $part,
            'status' => $status,
            'summary' => $summary,
            'groups' => $this->groupItems(),
            'parts' => \yii\helpers\ArrayHelper::map(KpiHaPart::activeParts(), 'id', 'name'),
            'units' => $this->orgUnitItems($unit),
            'unitNames' => \yii\helpers\ArrayHelper::map(OrgUnit::find()->select(['id', 'name'])->asArray()->all(), 'id', 'name'),
            'canManage' => Yii::$app->user->can('kpiManage'),
        ]);
    }

    /** รายละเอียดตัวชี้วัด + กราฟเทรนด์ (เป้า vs ผลจริง) */
    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        $years = $model->getYears()->all();
        $unitName = $model->org_unit_id ? (string) OrgUnit::find()->select('name')->where(['id' => $model->org_unit_id])->scalar() : null;

        // ปีล่าสุดที่มีผลจริง ใช้สรุปผลประเมิน
        $latest = null;
        foreach ($years as $y) {
            if ($y->actual_value !== null) $latest = $y;
        }

        return $this->render('view', [
            'model' => $model,
            'years' => $years,
            'unitName' => $unitName,
            'latest' => $latest,
            'canManage' => Yii::$app->user->can('kpiManage'),
        ]);
    }

    public function actionCreate()
    {
        return $this->formPage(new KpiIndicator(['is_active' => true, 'operator' => '>=', 'frequency' => 'year']));
    }

    public function actionUpdate(int $id)
    {
        return $this->formPage($this->findModel($id));
    }

    public function actionDelete(int $id)
    {
        $this->findModel($id)->delete(); // ค่ารายปีลบตามด้วย FK cascade
        Yii::$app->session->setFlash('success', 'ลบตัวชี้วัดแล้ว');
        return $this->redirect(['index']);
    }

    // ===================== จัดการกลุ่มตัวชี้วัด =====================

    public function actionGroups()
    {
        return $this->groupForm(new KpiGroup(['kind' => KpiGroup::KIND_STANDALONE, 'is_active' => true, 'color' => '#4f46e5', 'icon' => 'bi-graph-up']));
    }

    public function actionGroupUpdate(int $id)
    {
        return $this->groupForm(KpiGroup::findOne($id) ?: throw new NotFoundHttpException('ไม่พบกลุ่ม'));
    }

    private function groupForm(KpiGroup $model)
    {
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->code) $model->code = $this->uniqueGroupCode($model);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกกลุ่มตัวชี้วัดแล้ว');
                return $this->redirect(['groups']);
            }
        }
        return $this->render('groups', [
            'model' => $model,
            'groups' => KpiGroup::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all(),
        ]);
    }

    public function actionGroupDelete(int $id)
    {
        $model = KpiGroup::findOne($id) ?: throw new NotFoundHttpException('ไม่พบกลุ่ม');
        if ($model->isStrategy()) {
            Yii::$app->session->setFlash('error', 'ลบกลุ่มยุทธศาสตร์ไม่ได้');
        } elseif (KpiIndicator::find()->where(['group_id' => $id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะยังมีตัวชี้วัดในกลุ่มนี้');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบกลุ่มแล้ว');
        }
        return $this->redirect(['groups']);
    }

    private function uniqueGroupCode(KpiGroup $m): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', (string) ($m->name_en ?: $m->name)), '_')) ?: 'grp';
        $code = $base; $i = 1;
        while (KpiGroup::find()->where(['code' => $code])->andWhere(['<>', 'id', $m->id ?? 0])->exists()) {
            $code = $base . '_' . (++$i);
        }
        return $code;
    }

    // ===================== จัดการตอน HA (Part) =====================

    public function actionParts()
    {
        return $this->partForm(new KpiHaPart(['is_active' => true]));
    }

    public function actionPartUpdate(int $id)
    {
        return $this->partForm(KpiHaPart::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตอน HA'));
    }

    private function partForm(KpiHaPart $model)
    {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'บันทึกตอน HA แล้ว');
            return $this->redirect(['parts']);
        }
        return $this->render('parts', [
            'model' => $model,
            'parts' => KpiHaPart::find()->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->all(),
        ]);
    }

    public function actionPartDelete(int $id)
    {
        $model = KpiHaPart::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตอน HA');
        if (KpiIndicator::find()->where(['ha_part_id' => $id])->exists()) {
            Yii::$app->session->setFlash('error', 'ลบไม่ได้ เพราะยังมีตัวชี้วัดผูกกับตอนนี้');
        } else {
            $model->delete();
            Yii::$app->session->setFlash('success', 'ลบตอน HA แล้ว');
        }
        return $this->redirect(['parts']);
    }

    // ===================== บันทึกข้อมูลรายเดือน =====================

    /** บันทึกเป้า + ค่ารายเดือน (ต.ค.→ก.ย.) — ค่าจริงรายปีคำนวณอัตโนมัติจากรายเดือน */
    public function actionData(int $id, ?int $year = null)
    {
        $indicator = $this->findModel($id);
        $year = (int) ($year ?: KpiRegistry::defaultFiscalYear());
        $entry = $indicator->yearEntry($year) ?: new KpiIndicatorYear(['kpi_indicator_id' => $indicator->id, 'fiscal_year' => $year]);

        $post = Yii::$app->request->post();
        if ($post) {
            $tx = Yii::$app->db->beginTransaction();
            $t = $post['target'] ?? '';
            $entry->kpi_indicator_id = $indicator->id;
            $entry->fiscal_year = $year;
            $entry->target_value = (trim((string) $t) === '' ? null : $t);
            $entry->save(false);

            $input = $post['Months'] ?? [];
            foreach (KpiIndicatorMonth::FISCAL_MONTHS as $m) {
                $v = $input[$m]['value'] ?? '';
                $row = KpiIndicatorMonth::findOne(['kpi_indicator_year_id' => $entry->id, 'month' => $m])
                    ?: new KpiIndicatorMonth(['kpi_indicator_year_id' => $entry->id, 'month' => $m]);
                if (trim((string) $v) === '') {
                    if (!$row->isNewRecord) $row->delete();
                    continue;
                }
                $row->value = $v;
                $row->save();
            }
            $entry->recomputeActualFromMonths();
            $tx->commit();
            Yii::$app->session->setFlash('success', 'บันทึกข้อมูลรายเดือนแล้ว (คำนวณค่าจริงรายปีอัตโนมัติ)');
            return $this->redirect(['data', 'id' => $id, 'year' => $year]);
        }

        $monthMap = [];
        if (!$entry->isNewRecord) {
            foreach ($entry->months as $mm) $monthMap[(int) $mm->month] = $mm;
        }
        $dy = KpiRegistry::defaultFiscalYear();
        return $this->render('data', [
            'indicator' => $indicator, 'entry' => $entry, 'year' => $year,
            'monthMap' => $monthMap, 'yearOpts' => range($dy - 4, $dy + 1), // น้อย→มาก เหมือนยุทธศาสตร์
        ]);
    }

    /** ฟอร์มตัวชี้วัด + ตารางค่ารายปี (เป้า/ผลจริง) ในหน้าเดียว */
    private function formPage(KpiIndicator $model)
    {
        $years = $this->yearWindow();
        $entries = [];
        if (!$model->isNewRecord) {
            foreach ($model->years as $y) $entries[(int) $y->fiscal_year] = $y;
        }
        $rowModels = [];
        foreach ($years as $fy) {
            $rowModels[$fy] = $entries[$fy] ?? new KpiIndicatorYear(['fiscal_year' => $fy]);
        }

        $post = Yii::$app->request->post();
        if ($post && $model->load($post)) {
            $tx = Yii::$app->db->beginTransaction();
            if ($model->save()) {
                $yearsInput = $post['Years'] ?? [];
                foreach ($years as $fy) {
                    $entry = $rowModels[$fy];
                    $t = $yearsInput[$fy]['target_value'] ?? '';
                    $a = $yearsInput[$fy]['actual_value'] ?? '';
                    $entry->kpi_indicator_id = $model->id;
                    $entry->fiscal_year = $fy;
                    if (trim((string) $t) === '' && trim((string) $a) === '') {
                        if (!$entry->isNewRecord) $entry->delete();
                        continue;
                    }
                    $entry->target_value = ($t === '' ? null : $t);
                    $entry->actual_value = ($a === '' ? null : $a);
                    $entry->save(); // beforeSave คำนวณ status อัตโนมัติ
                }
                $tx->commit();
                Yii::$app->session->setFlash('success', 'บันทึกตัวชี้วัดแล้ว');
                return $this->redirect(['index', 'group' => $model->group_id]);
            }
            $tx->rollBack();
        }

        return $this->render('form', [
            'model' => $model,
            'years' => $years,
            'rowModels' => $rowModels,
            'groups' => $this->groupItems(),
            'parts' => \yii\helpers\ArrayHelper::map(KpiHaPart::activeParts(), 'id', 'name'),
            'units' => $this->orgUnitItems($model->org_unit_id),
        ]);
    }

    /** ช่วงปีที่เปิดให้กรอกค่า (5 ปีล่าสุดถึงปีงบปัจจุบัน) */
    private function yearWindow(): array
    {
        $y = KpiRegistry::defaultFiscalYear();
        return range($y - 4, $y);
    }

    /** กลุ่มนอกแผน (2-5) เป็นตัวเลือก */
    private function groupItems(): array
    {
        $items = [];
        foreach (KpiGroup::find()->where(['kind' => KpiGroup::KIND_STANDALONE, 'is_active' => true])
                     ->orderBy(['sort_order' => SORT_ASC])->all() as $g) {
            $items[$g->id] = $g->name;
        }
        return $items;
    }

    /** หน่วยงานจากทะเบียนกลาง org_unit จัดกลุ่มตามประเภท */
    private function orgUnitItems(?int $keepId): array
    {
        return OrgUnit::groupedForSelect((int) PlanHelper::currentPlanYear(), $keepId);
    }

    private function findModel(int $id): KpiIndicator
    {
        $model = KpiIndicator::findOne($id);
        if (!$model) throw new NotFoundHttpException('ไม่พบตัวชี้วัด');
        // กันไม่ให้แก้ตัวชี้วัดยุทธศาสตร์ผ่านหน้านี้ (คนละทะเบียน)
        if ($model->group && $model->group->isStrategy()) {
            throw new NotFoundHttpException('ตัวชี้วัดยุทธศาสตร์จัดการที่ทะเบียนยุทธศาสตร์');
        }
        return $model;
    }
}

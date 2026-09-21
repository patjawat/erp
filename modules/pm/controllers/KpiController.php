<?php

namespace app\modules\pm\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\modules\pm\components\KpiStatus;
use app\modules\pm\models\KpiGroup;
use app\modules\pm\models\KpiIndicator;
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
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST'], 'copy-year' => ['POST']]],
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

    public function actionIndex(?int $group = null, ?int $unit = null, ?int $year = null)
    {
        $year = $year ?: KpiRegistry::defaultFiscalYear();
        $q = trim((string) Yii::$app->request->get('q'));

        $stdGroupIds = KpiGroup::find()->select('id')->where(['kind' => KpiGroup::KIND_STANDALONE])->column();
        $query = KpiIndicator::find()->with(['group', 'years'])->where(['group_id' => $stdGroupIds]);
        if ($group) $query->andWhere(['group_id' => $group]);
        if ($unit) $query->andWhere(['org_unit_id' => $unit]);
        if ($q !== '') $query->andWhere(['like', 'name', $q]);
        $indicators = $query->orderBy(['group_id' => SORT_ASC, 'sort_order' => SORT_ASC, 'id' => SORT_ASC])->all();

        // สรุปสถานะของปีที่เลือก
        $summary = ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0];
        foreach ($indicators as $ind) {
            $summary['total']++;
            $summary[$ind->statusFor($year)]++;
        }

        return $this->render('index', [
            'indicators' => $indicators,
            'year' => $year,
            'q' => $q,
            'group' => $group,
            'unit' => $unit,
            'summary' => $summary,
            'groups' => $this->groupItems(),
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

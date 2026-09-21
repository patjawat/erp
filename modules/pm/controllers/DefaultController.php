<?php

namespace app\modules\pm\controllers;

use Yii;
use app\modules\pm\components\KpiStatus;
use app\modules\pm\models\KpiGroup;
use app\modules\pm\models\KpiIndicator;
use app\modules\pm\models\StrategyIndicator;
use app\modules\pm\services\KpiRegistry;
use app\modules\settings\models\OrgUnit;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

/**
 * Default controller for the `pm` module — ภาพรวม = KPI Dashboard (ตัวชี้วัดทุกกลุ่ม)
 */
class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

    /**
     * ภาพรวมตัวชี้วัดทุกกลุ่ม (ยุทธศาสตร์ + ของ รพ.) รวมผ่าน KpiRegistry
     * สรุป PASS/GAP + ตัวกรอง + ตาราง union — ชุดนำเสนอสำหรับผู้บริหาร
     */
    public function actionIndex()
    {
        $registry = new KpiRegistry();
        $year = (int) Yii::$app->request->get('year', 0) ?: KpiRegistry::defaultFiscalYear();
        $group = (int) Yii::$app->request->get('group', 0) ?: null;
        $unit = (int) Yii::$app->request->get('unit', 0) ?: null;
        $q = trim((string) Yii::$app->request->get('q'));
        $status = Yii::$app->request->get('status');
        $status = in_array($status, [KpiStatus::PASS, KpiStatus::GAP, KpiStatus::NODATA], true) ? $status : null;

        $allRows = array_values(array_filter($registry->rows($year), static function ($r) use ($group, $unit, $q) {
            if ($group && $r->groupId !== $group) return false;
            if ($unit && $r->orgUnitId !== $unit) return false;
            if ($q !== '' && mb_stripos($r->name, $q) === false) return false;
            return true;
        }));

        // สรุป/กราฟ/ตามกลุ่ม คิดจากชุดที่กรอง group/unit/q (ไม่รวมตัวกรองสถานะ) — ตารางค่อยกรองตามการ์ดที่คลิก
        $summary = ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0];
        $byGroup = [];
        foreach ($allRows as $r) {
            $summary['total']++;
            $summary[$r->status]++;
            $byGroup[$r->groupName] ??= ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0, 'color' => '#6c757d'];
            $byGroup[$r->groupName]['total']++;
            $byGroup[$r->groupName][$r->status]++;
        }
        $judged = $summary['pass'] + $summary['gap'];
        $summary['successRate'] = $judged ? (int) round($summary['pass'] * 100 / $judged) : 0;

        $rows = $status ? array_values(array_filter($allRows, static fn ($r) => $r->status === $status)) : $allRows;

        $groupItems = [];
        foreach (KpiGroup::activeGroups() as $g) {
            $groupItems[$g->id] = $g->name;
            if (isset($byGroup[$g->name])) $byGroup[$g->name]['color'] = $g->color ?: '#6c757d';
        }
        $unitNames = ArrayHelper::map(OrgUnit::find()->select(['id', 'name'])->asArray()->all(), 'id', 'name');

        return $this->render('index', [
            'rows' => $rows, 'summary' => $summary, 'byGroup' => $byGroup,
            'year' => $year, 'group' => $group, 'unit' => $unit, 'q' => $q, 'status' => $status,
            'groupItems' => $groupItems, 'unitNames' => $unitNames,
            'defaultYear' => KpiRegistry::defaultFiscalYear(),
        ]);
    }

    /**
     * หน้ารายละเอียดตัวชี้วัดจาก dashboard — กราฟเทรนด์ + รายละเอียด รองรับทั้ง 2 แหล่ง
     * standalone = ตัวชี้วัด รพ. (KpiIndicator) / strategy = ตัวชี้วัดยุทธศาสตร์ (StrategyIndicator)
     */
    public function actionDetail(string $source, int $id)
    {
        $rowsY = [];
        if ($source === 'strategy') {
            $ind = StrategyIndicator::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัด');
            $years = $ind->getYears()->all();
            $curYear = KpiRegistry::defaultFiscalYear();
            $metaYear = null;
            foreach ($years as $y) { if ((int) $y->fiscal_year === $curYear) $metaYear = $y; }
            $metaYear = $metaYear ?: (end($years) ?: null);
            $meta = [
                'name' => $ind->name, 'group' => (string) KpiGroup::find()->select('name')->where(['code' => 'strategy'])->scalar() ?: 'ตัวชี้วัดระดับยุทธศาสตร์',
                'unit' => $ind->unit, 'operator' => $metaYear?->operator, 'owner' => $metaYear?->owner_name,
                'orgUnit' => $metaYear?->owner_team,
                'definition' => $metaYear?->definition, 'formula' => $metaYear?->formula,
                'evaluation_method' => $metaYear?->evaluation_method, 'data_source' => $metaYear?->data_source,
                'editUrl' => $metaYear ? ['/pm/strategy-catalog/template', 'id' => (int) $metaYear->id] : null,
                'editLabel' => 'ดูแบบฟอร์ม KPI',
            ];
            foreach ($years as $y) $rowsY[] = ['fy' => (int) $y->fiscal_year, 'target' => $y->target_value, 'actual' => $y->actual_value, 'operator' => $y->operator];
        } else {
            $ind = KpiIndicator::findOne($id) ?: throw new NotFoundHttpException('ไม่พบตัวชี้วัด');
            if ($ind->group && $ind->group->isStrategy()) throw new NotFoundHttpException('แหล่งข้อมูลไม่ถูกต้อง');
            $meta = [
                'name' => $ind->name, 'group' => $ind->group->name ?? '-', 'unit' => $ind->unit,
                'operator' => $ind->operator, 'owner' => $ind->owner_name,
                'orgUnit' => $ind->org_unit_id ? (string) OrgUnit::find()->select('name')->where(['id' => $ind->org_unit_id])->scalar() : null,
                'definition' => $ind->definition, 'formula' => $ind->formula,
                'evaluation_method' => $ind->evaluation_method, 'data_source' => $ind->data_source,
                'editUrl' => Yii::$app->user->can('kpiManage') ? ['/pm/kpi/update', 'id' => (int) $ind->id] : null,
                'editLabel' => 'แก้ไข',
            ];
            foreach ($ind->getYears()->all() as $y) $rowsY[] = ['fy' => (int) $y->fiscal_year, 'target' => $y->target_value, 'actual' => $y->actual_value, 'operator' => $ind->operator];
        }

        usort($rowsY, static fn ($a, $b) => $a['fy'] <=> $b['fy']);
        $labels = []; $targetData = []; $actualData = []; $latest = null;
        foreach ($rowsY as $r) {
            $labels[] = $r['fy'];
            $targetData[] = $r['target'] !== null ? (float) $r['target'] : null;
            $actualData[] = $r['actual'] !== null ? (float) $r['actual'] : null;
            if ($r['actual'] !== null) $latest = $r;
        }

        return $this->render('kpi-detail', [
            'meta' => $meta, 'rowsY' => $rowsY, 'labels' => $labels,
            'targetData' => $targetData, 'actualData' => $actualData, 'latest' => $latest,
        ]);
    }

    /** รายงานรวมสำหรับพิมพ์ — ตัวชี้วัดทุกกลุ่ม ค่าย้อนหลัง 5 ปี + สถานะ จัดกลุ่มตามกลุ่มตัวชี้วัด */
    public function actionReport()
    {
        $registry = new KpiRegistry();
        $year = (int) Yii::$app->request->get('year', 0) ?: KpiRegistry::defaultFiscalYear();
        $group = (int) Yii::$app->request->get('group', 0) ?: null;
        $unit = (int) Yii::$app->request->get('unit', 0) ?: null;
        $q = trim((string) Yii::$app->request->get('q'));

        $rows = array_values(array_filter($registry->rows($year), static function ($r) use ($group, $unit, $q) {
            if ($group && $r->groupId !== $group) return false;
            if ($unit && $r->orgUnitId !== $unit) return false;
            if ($q !== '' && mb_stripos($r->name, $q) === false) return false;
            return true;
        }));

        // จัดกลุ่มตามลำดับกลุ่ม
        $byGroup = [];
        foreach (KpiGroup::activeGroups() as $g) $byGroup[$g->name] = [];
        foreach ($rows as $r) $byGroup[$r->groupName][] = $r;
        $byGroup = array_filter($byGroup);

        $unitNames = ArrayHelper::map(OrgUnit::find()->select(['id', 'name'])->asArray()->all(), 'id', 'name');
        $site = \app\components\SiteHelper::getInfo();

        return $this->render('report', [
            'byGroup' => $byGroup, 'year' => $year, 'years' => range($year - 4, $year),
            'unitNames' => $unitNames, 'hospitalName' => $site['company_name'] ?? 'โรงพยาบาล',
        ]);
    }
}

<?php

namespace app\modules\pm\controllers;

use Yii;
use app\modules\pm\components\KpiStatus;
use app\modules\pm\models\KpiGroup;
use app\modules\pm\services\KpiRegistry;
use app\modules\settings\models\OrgUnit;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
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

        $rows = array_values(array_filter($registry->rows($year), static function ($r) use ($group, $unit, $q) {
            if ($group && $r->groupId !== $group) return false;
            if ($unit && $r->orgUnitId !== $unit) return false;
            if ($q !== '' && mb_stripos($r->name, $q) === false) return false;
            return true;
        }));

        $summary = ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0];
        $byGroup = [];
        foreach ($rows as $r) {
            $summary['total']++;
            $summary[$r->status]++;
            $byGroup[$r->groupName] ??= ['total' => 0, 'pass' => 0, 'gap' => 0, 'nodata' => 0, 'color' => '#6c757d'];
            $byGroup[$r->groupName]['total']++;
            $byGroup[$r->groupName][$r->status]++;
        }
        $judged = $summary['pass'] + $summary['gap'];
        $summary['successRate'] = $judged ? (int) round($summary['pass'] * 100 / $judged) : 0;

        $groupItems = [];
        foreach (KpiGroup::activeGroups() as $g) {
            $groupItems[$g->id] = $g->name;
            if (isset($byGroup[$g->name])) $byGroup[$g->name]['color'] = $g->color ?: '#6c757d';
        }
        $unitNames = ArrayHelper::map(OrgUnit::find()->select(['id', 'name'])->asArray()->all(), 'id', 'name');

        return $this->render('index', [
            'rows' => $rows, 'summary' => $summary, 'byGroup' => $byGroup,
            'year' => $year, 'group' => $group, 'unit' => $unit, 'q' => $q,
            'groupItems' => $groupItems, 'unitNames' => $unitNames,
            'defaultYear' => KpiRegistry::defaultFiscalYear(),
        ]);
    }
}

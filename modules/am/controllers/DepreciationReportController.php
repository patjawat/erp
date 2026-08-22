<?php

namespace app\modules\am\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\modules\am\models\AccountingPeriod;
use app\modules\am\services\DepreciationReportService;

/**
 * รายงานค่าเสื่อมราชการ: รายเดือน/ไตรมาส/ปีงบ (screen 10)
 * ไตรมาส/ปีรวมจากรายการรายเดือนชุดเดียวกันเพื่อให้ยอดตรง
 *
 * หมายเหตุ: คนละตัวกับ ReportController เดิม (ไม่แตะรายงาน legacy)
 */
class DepreciationReportController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['depreciationView']]],
            ],
        ]);
    }

    public function actionIndex($fiscal_year = null, $type = 'month', $period_no = null, $group = 'flat')
    {
        $service = new DepreciationReportService();

        $fyBE = (int) ($fiscal_year ?: (date('n') >= 10 ? date('Y') + 544 : date('Y') + 543));
        $type = in_array($type, ['month', 'quarter', 'fiscal_year'], true) ? $type : 'month';
        $group = in_array($group, ['flat', 'type_category'], true) ? $group : 'flat';

        $rows = [];
        $selectedPeriod = null;

        if ($type === 'month') {
            $periodNo = (int) ($period_no ?: 1);
            $period = AccountingPeriod::findOne(['fiscal_year' => $fyBE, 'period_type' => 'month', 'period_no' => $periodNo]);
            if ($period) {
                $selectedPeriod = $period;
                $rows = $service->monthly($period->id);
            }
        } elseif ($type === 'quarter') {
            $q = (int) ($period_no ?: 1);
            $rows = $service->quarter($fyBE, $q);
            $selectedPeriod = AccountingPeriod::findOne(['fiscal_year' => $fyBE, 'period_type' => 'quarter', 'period_no' => $q]);
        } else {
            $rows = $service->fiscalYear($fyBE);
            $selectedPeriod = AccountingPeriod::findOne(['fiscal_year' => $fyBE, 'period_type' => 'fiscal_year', 'period_no' => 1]);
        }

        $years = AccountingPeriod::find()->select('fiscal_year')->distinct()->orderBy(['fiscal_year' => SORT_DESC])->column();
        $months = AccountingPeriod::find()
            ->where(['fiscal_year' => $fyBE, 'period_type' => 'month'])
            ->orderBy(['period_no' => SORT_ASC])->all();

        return $this->render('index', [
            'fyBE' => $fyBE,
            'type' => $type,
            'periodNo' => (int) ($period_no ?: 1),
            'group' => $group,
            'rows' => $rows,
            'grouped' => $group === 'type_category' ? $service->groupByTypeCategory($rows) : [],
            'totals' => $service->totals($rows),
            'selectedPeriod' => $selectedPeriod,
            'years' => $years,
            'months' => $months,
        ]);
    }
}

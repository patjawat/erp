<?php

namespace app\modules\complaint\controllers;

use app\components\AppHelper;
use app\components\UserHelper;
use app\modules\complaint\models\Complaint;
use app\modules\complaint\services\ComplaintKpiService;
use app\modules\complaint\services\ComplaintService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * ภาพรวมโซนงานคุณภาพ → รับเรื่องร้องเรียน (สรุปสถานะ + เรื่องล่าสุด)
 */
class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
        ]);
    }

    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        $empUnitId = $me ? (int) $me->department : null;
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;

        $fy = (int) Yii::$app->request->get('fy') ?: (int) AppHelper::YearBudget();

        $base = Complaint::find()->where(['fiscal_year' => $fy]);
        if (!ComplaintService::isManager()) {
            $scope = ComplaintService::unitScopeIds($empUnitId) ?: [-1];
            $base->andWhere(['or', ['assigned_unit_id' => $scope], ['created_by' => $userId ?? -1]]);
        }

        // นับตามสถานะ
        $counts = [];
        foreach (array_keys(Complaint::statusLabels()) as $st) {
            $q = clone $base;
            $counts[$st] = (int) $q->andWhere(['status' => $st])->count();
        }
        $total = (int) (clone $base)->count();

        $open = (clone $base)
            ->andWhere(['not in', 'status', [Complaint::STATUS_CLOSED, Complaint::STATUS_REJECTED]])
            ->count();

        // เกินกำหนดปิดเคส (close_due < วันนี้ และยังไม่ปิด)
        $overdue = (int) (clone $base)
            ->andWhere(['not in', 'status', [Complaint::STATUS_CLOSED, Complaint::STATUS_REJECTED]])
            ->andWhere(['<', 'close_due', date('Y-m-d')])
            ->andWhere(['not', ['close_due' => null]])
            ->count();

        $recent = (clone $base)
            ->with(['type', 'assignedUnit'])
            ->orderBy(['id' => SORT_DESC])
            ->limit(8)
            ->all();

        return $this->render('index', [
            'fiscalYear' => $fy,
            'years' => range($fy + 1, $fy - 3),
            'counts' => $counts,
            'total' => $total,
            'open' => (int) $open,
            'overdue' => $overdue,
            'recent' => $recent,
        ]);
    }

    /** ขอบเขตสิทธิ์สำหรับ KPI: manager=null (ทุกหน่วย) ; อื่น ๆ = [scopeIds, userId] */
    private function kpiScope(): array
    {
        if (ComplaintService::isManager()) {
            return [null, null];
        }
        $me = UserHelper::GetEmployee();
        $empUnitId = $me ? (int) $me->department : null;
        $userId = Yii::$app->user->id !== null ? (int) Yii::$app->user->id : null;
        return [ComplaintService::unitScopeIds($empUnitId) ?: [-1], $userId];
    }

    /** แดชบอร์ด KPI (CC01–CC07) + กราฟ */
    public function actionKpi()
    {
        $fy = (int) Yii::$app->request->get('fy') ?: (int) AppHelper::YearBudget();
        [$scope, $userId] = $this->kpiScope();
        $m = ComplaintKpiService::metrics($fy, $scope, $userId);

        return $this->render('kpi', [
            'm' => $m,
            'fiscalYear' => $fy,
            'years' => range($fy + 1, $fy - 3),
        ]);
    }

    /** SLA monitor — เคสที่เกินกำหนดแยกตามช่วง */
    public function actionMonitor()
    {
        $fy = (int) Yii::$app->request->get('fy') ?: (int) AppHelper::YearBudget();
        [$scope, $userId] = $this->kpiScope();
        $m = ComplaintKpiService::metrics($fy, $scope, $userId);

        return $this->render('monitor', [
            'm' => $m,
            'fiscalYear' => $fy,
            'years' => range($fy + 1, $fy - 3),
        ]);
    }

    /** รายงานสรุป (พิมพ์ได้) — KPI + การกระจาย ตามปีงบ */
    public function actionReport()
    {
        $fy = (int) Yii::$app->request->get('fy') ?: (int) AppHelper::YearBudget();
        [$scope, $userId] = $this->kpiScope();
        $m = ComplaintKpiService::metrics($fy, $scope, $userId);

        return $this->render('report', [
            'm' => $m,
            'fiscalYear' => $fy,
            'years' => range($fy + 1, $fy - 3),
        ]);
    }
}

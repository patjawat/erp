<?php

namespace app\modules\roster\controllers;

use app\modules\hr\models\Organization;
use app\modules\roster\helpers\RosterAccess;
use app\modules\roster\helpers\RosterAnalytics;
use app\modules\roster\models\Period;
use app\modules\roster\models\ShiftType;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * ภาพรวมสำหรับผู้ตรวจสอบ — หัวหน้ากลุ่มงานดูทุกหน่วยในกิ่งตัวเอง, ผอ. ดูทั้งโรงพยาบาล
 *
 * ขอบเขตการเห็นใช้ RosterAccess::viewableUnitIds() ตัวเดียวกับที่ใช้ทั้งโมดูล
 * ไม่มี logic สิทธิ์ชุดใหม่ที่นี่ จะได้ไม่มีทางหลุดขอบเขต
 */
class OverviewController extends Controller
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
        if (!RosterAccess::canSeeOverview()) {
            throw new ForbiddenHttpException('หน้านี้สำหรับผู้ตรวจสอบตารางเวรเท่านั้น');
        }
        return true;
    }

    public function actionIndex($month = null, $year = null)
    {
        $month = (int) ($month ?: date('n'));
        $year = (int) ($year ?: date('Y'));

        $unitIds = $this->unitsInScope();
        if (empty($unitIds)) {
            return $this->render('index', [
                'month' => $month, 'year' => $year, 'units' => [], 'periods' => [],
                'statusMatrix' => ['months' => [], 'matrix' => []],
                'coverage' => [], 'violations' => [], 'fairness' => [], 'swapCounts' => [],
                'types' => [], 'pendingCount' => 0,
            ]);
        }

        // เอาเฉพาะหน่วยที่มีคนขึ้นเวรจริง — ไม่งั้นตารางเต็มไปด้วยหน่วยที่ไม่เกี่ยว
        $units = $this->unitsWithShiftStaff($unitIds);

        $periods = Period::find()
            ->where(['unit_id' => array_keys($units), 'month' => $month, 'year_ce' => $year, 'deleted_at' => null])
            ->all();

        return $this->render('index', [
            'month' => $month,
            'year' => $year,
            'units' => $units,
            'periods' => $periods,
            'statusMatrix' => RosterAnalytics::periodStatusMatrix(array_keys($units), 2, 2, $year, $month),
            'coverage' => RosterAnalytics::coverageHeatmap($periods),
            'violations' => RosterAnalytics::violationSummary($periods),
            'fairness' => RosterAnalytics::fairness($periods),
            'swapCounts' => RosterAnalytics::swapCounts($periods),
            'types' => ShiftType::activeList(),
            'pendingCount' => $this->pendingCount($units),
        ]);
    }

    /** @return int[] */
    private function unitsInScope(): array
    {
        $viewable = RosterAccess::viewableUnitIds();
        if ($viewable !== null) {
            return $viewable;
        }
        return array_map('intval', Organization::find()->select('id')->where(['active' => 1])->column());
    }

    /**
     * หน่วยที่มีเจ้าหน้าที่ขึ้นเวร (work_shift='shift') อย่างน้อย 1 คน
     * @param int[] $unitIds
     * @return array [unitId => name]
     */
    private function unitsWithShiftStaff(array $unitIds): array
    {
        $rows = (new \yii\db\Query())
            ->select(['t.id', 't.name', 'n' => 'COUNT(e.id)'])
            ->from(['t' => Organization::tableName()])
            ->innerJoin(['e' => 'employees'], 'e.department = t.id AND e.status = 1')
            ->where(['t.id' => $unitIds])
            ->andWhere(['e.work_shift' => 'shift'])
            ->groupBy(['t.id', 't.name'])
            ->having(['>', 'COUNT(e.id)', 0])
            ->orderBy(['n' => SORT_DESC, 't.name' => SORT_ASC])
            ->all();
        $units = [];
        foreach ($rows as $row) {
            $units[(int) $row['id']] = (string) $row['name'];
        }
        return $units;
    }

    /** งานที่รอผู้ใช้คนนี้ตรวจสอบหรืออนุมัติ */
    private function pendingCount(array $units): int
    {
        $count = 0;
        $rows = Period::find()
            ->select(['unit_id', 'status'])
            ->where(['status' => [Period::STATUS_SUBMITTED, Period::STATUS_REVIEWED], 'deleted_at' => null])
            ->andWhere(['unit_id' => array_keys($units) ?: [0]])
            ->asArray()->all();
        foreach ($rows as $row) {
            $unitId = (int) $row['unit_id'];
            if ($row['status'] === Period::STATUS_SUBMITTED && RosterAccess::canReviewUnit($unitId)) {
                $count++;
            } elseif ($row['status'] === Period::STATUS_REVIEWED && RosterAccess::canApprove()) {
                $count++;
            }
        }
        return $count;
    }
}

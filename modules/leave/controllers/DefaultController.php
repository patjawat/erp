<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\components\UserHelper;
use app\components\ApproveHelper;
use app\components\AppHelper;
use app\modules\leave\models\Leave;
use app\modules\leave\models\LeaveSearch;
use app\modules\leave\models\LeaveSummarySearch;
use app\modules\leave\models\LeaveType;
use app\modules\leave\models\LeavePolicies;

/**
 * Default controller for the leave module.
 */
class DefaultController extends Controller
{
    /**
     * Dashboard ภาพรวมการลา — สรุปสถิติการลารายปี/รายเดือน/ประเภทการลา
     */
    public function actionDashboard()
    {
        $searchModel = new LeaveSummarySearch([
            'thai_year' => AppHelper::YearBudget(),
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->groupBy('code');

        return $this->render('dashboard', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ขอลา / รายการของฉัน — ข้อมูลปีงบประมาณ, สิทธิ์การลา, เกณฑ์การลา, ประวัติลาล่าสุด (เฉพาะของฉัน)
     */
    public function actionIndex()
    {
        $me = UserHelper::GetEmployee();
        if (!$me) {
            Yii::$app->session->setFlash('error', 'ไม่พบข้อมูลพนักงาน');
            return $this->redirect(['/me']);
        }

        $thaiYear = (int) (Yii::$app->request->get('thai_year') ?: AppHelper::YearBudget());
        $round = (int) (Yii::$app->request->get('round') ?: 1);

        $approveInfo = ApproveHelper::Info();
        $totalLeavePending = $approveInfo['leave']['total'] ?? 0;
        $canHr = Yii::$app->user->can('admin') || Yii::$app->user->can('hr');

        $budgetRange = AppHelper::BudgetYearRange($thaiYear);
        $yPrev = substr((string) ($thaiYear - 1), -2);
        $yCurr = substr((string) $thaiYear, -2);
        $fiscalLabel = 'พ.ศ. ' . $thaiYear . ' (1 ต.ค. ' . $yPrev . ' - 31 มี.ค. ' . $yCurr . ')';

        $typeSummaries = $this->getLeaveTypeSummaries($me, $thaiYear);
        $criteriaRules = $this->getLeaveCriteriaRules($me->position_type ?? null);

        $searchModel = new LeaveSearch(['emp_id' => $me->id, 'thai_year' => $thaiYear]);
        $params = array_merge(
            ['LeaveSearch' => ['emp_id' => $me->id, 'thai_year' => $thaiYear]],
            Yii::$app->request->queryParams
        );
        $dataProvider = $searchModel->search($params);
        $dataProvider->query->andWhere(['leave.emp_id' => $me->id, 'leave.thai_year' => $thaiYear]);
        $dataProvider->query->joinWith(['leaveType', 'leaveStatus']);
        $dataProvider->query->with(['approves' => function ($q) {
            $q->orderBy(['level' => SORT_ASC])->with('employee');
        }]);
        $dataProvider->setSort(['defaultOrder' => ['created_at' => SORT_DESC]]);
        // $dataProvider->pagination = ['pageSize' => 10, 'pageParam' => 'leave-page'];
        $dataProvider->pagination = false;

        $listThaiYear = (new Leave())->ListThaiYear();

        $myPendingLeaveCount = (int) Leave::find()
            ->andWhere(['emp_id' => $me->id, 'thai_year' => $thaiYear, 'status' => 'Pending'])
            ->count();
        $remainingAnnualLeave = 0;
        $totalDaysUsedThisYear = 0;
        foreach ($typeSummaries as $s) {
            $totalDaysUsedThisYear += (float) ($s['days_used'] ?? 0);
            if (($s['code'] ?? '') === 'LT4') {
                $remainingAnnualLeave = max(0, (int) ($s['entitlement_days'] ?? 0) - (int) ($s['days_used'] ?? 0));
            }
        }

        return $this->render('index', [
            'employee' => $me,
            'totalLeavePending' => (int) $totalLeavePending,
            'myPendingLeaveCount' => $myPendingLeaveCount,
            'remainingAnnualLeave' => $remainingAnnualLeave,
            'totalDaysUsedThisYear' => $totalDaysUsedThisYear,
            'canHr' => (bool) $canHr,
            'thaiYear' => $thaiYear,
            'round' => $round,
            'fiscalLabel' => $fiscalLabel,
            'budgetRange' => $budgetRange,
            'typeSummaries' => $typeSummaries,
            'criteriaRules' => $criteriaRules,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'listThaiYear' => $listThaiYear,
        ]);
    }

    /**
     * ดึงสิทธิ์วันลา (รวมยอดยกมา) จาก leave_entitlements ตาม emp_id และปีงบประมาณ
     * คืน [ leave_type_id => days ]
     */
    protected function getEntitlementFromLeaveEntitlements($empId, $thaiYear)
    {
        $rows = Yii::$app->db->createCommand(
            'SELECT leave_type_id, days FROM leave_entitlements WHERE emp_id = :emp_id AND thai_year = :thai_year'
        )->bindValues([':emp_id' => $empId, ':thai_year' => $thaiYear])->queryAll();

        $map = [];
        foreach ($rows as $row) {
            $code = $row['leave_type_id'] ?? null;
            if ($code !== null && $code !== '') {
                $map[$code] = (int) $row['days'];
            }
        }
        return $map;
    }

    /**
     * สรุปการใช้และสิทธิ์แยกตามประเภทการลา
     * สิทธิ์ดึงจาก leave_entitlements (สรุปยอดยกมาจากปีก่อน) ถ้ามี ไม่มีจึงใช้ leave_policies
     * ไม่แสดงลาออก, ชายไม่แสดงลาคลอดบุตร (LT2), หญิงไม่แสดงลาอุปสมบท (LT5, LT7)
     */
    protected function getLeaveTypeSummaries($employee, $thaiYear)
    {
        $empId = $employee->id;
        $positionType = $employee->position_type ?? null;
        $gender = $employee->gender ?? null;

        $entitlementByType = $this->getEntitlementFromLeaveEntitlements($empId, $thaiYear);

        $types = LeaveType::find()
            ->where(['name' => 'leave_type', 'active' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        $summaries = [];
        foreach ($types as $t) {
            // ไม่แสดงลาออก (ทุกคน)
            if (stripos((string) $t->title, 'ลาออก') !== false) {
                continue;
            }
            // ชาย: ไม่แสดงลาคลอดบุตร (LT2)
            if ($gender === 'ชาย' && $t->code === 'LT2') {
                continue;
            }
            // หญิง: ไม่แสดงลาอุปสมบท (LT5, LT7)
            if ($gender === 'หญิง' && in_array($t->code, ['LT5', 'LT7'], true)) {
                continue;
            }

            $usage = Leave::find()
                ->where([
                    'emp_id' => $empId,
                    'thai_year' => $thaiYear,
                    'leave_type_id' => $t->code,
                    'status' => 'Approve',
                ])
                ->select(['days' => 'SUM(total_days)', 'times' => 'COUNT(id)'])
                ->asArray()
                ->one();
            $days = (float) ($usage['days'] ?? 0);
            $times = (int) ($usage['times'] ?? 0);

            // สิทธิ์: จาก leave_entitlements (ยอดยกมา+สิทธิปีนี้) ก่อน ไม่มีจึงใช้ leave_policies
            $entitlement = isset($entitlementByType[$t->code]) ? (int) $entitlementByType[$t->code] : 0;
            if ($entitlement === 0) {
                $policy = LeavePolicies::find()
                    ->andWhere(['leave_type_id' => $t->code])
                    ->andFilterWhere(['position_type_id' => $positionType])
                    ->orderBy(['year_of_service' => SORT_DESC])
                    ->one();
                if ($policy) {
                    $entitlement = (int) ($policy->max_days ?? $policy->days ?? 0);
                }
            }

            $summaries[] = [
                'code' => $t->code,
                'title' => $t->title,
                'color' => $t->data_json['color'] ?? '#6c757d',
                'icon' => $t->data_json['icon'] ?? null,
                'days_used' => $days,
                'times_used' => $times,
                'entitlement_days' => $entitlement,
            ];
        }
        return $summaries;
    }

    /**
     * เกณฑ์การลาราชการ— ข้อความตามภาพตัวอย่าง
     */
    protected function getLeaveCriteriaRules($positionTypeId)
    {
        return [
            'ลาป่วย: ผอ.อนุมัติครั้งละไม่เกิน 60 วัน (ลา 30 วันขึ้นไปต้องมีใบรับรองแพทย์)',
            'ลากิจส่วนตัว: ครั้งละไม่เกิน 30 วัน และรวมปีละไม่เกิน 45 วันทำการ (ได้รับเงินเดือน)',
            'ลาคลอดบุตร: ไม่เกิน 90 วัน (ได้รับเงินเดือนระหว่างลา)',
            'ลาช่วยเหลือภริยาคลอด: ครั้งหนึ่งไม่เกิน 15 วันทำการ (รับเงินเดือนปกติ)',
            'ลาอุปสมบท: ไม่เกิน 120 วัน (ต้องรับราชการไม่น้อยกว่า 1 ปี)',
        ];
    }
}

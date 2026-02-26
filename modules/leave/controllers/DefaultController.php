<?php

namespace app\modules\leave\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\components\UserHelper;
use app\components\ApproveHelper;
use app\components\AppHelper;
use app\modules\hr\models\Leave;
use app\modules\hr\models\LeaveSearch;
use app\modules\hr\models\LeaveType;
use app\modules\hr\models\LeavePolicies;

/**
 * Default controller for the leave module.
 */
class DefaultController extends Controller
{
    /**
     * Dashboard การลางาน — ข้อมูลปีงบประมาณ, สิทธิ์การลา, เกณฑ์การลา, ประวัติลาล่าสุด
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
        $fiscalLabel = $thaiYear . ' (1 ต.ค. ' . substr($thaiYear - 1, 2) . ' - 31 มี.ค. ' . substr($thaiYear, 2) . ')';

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
        $dataProvider->setSort(['defaultOrder' => ['created_at' => SORT_DESC]]);
        $dataProvider->pagination = ['pageSize' => 10, 'pageParam' => 'leave-page'];

        $listThaiYear = (new Leave())->ListThaiYear();

        return $this->render('index', [
            'employee' => $me,
            'totalLeavePending' => (int) $totalLeavePending,
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
     * สรุปการใช้และสิทธิ์แยกตามประเภทการลา
     * ไม่แสดงลาออก, ชายไม่แสดงลาคลอดบุตร (LT2), หญิงไม่แสดงลาอุปสมบท (LT5, LT7)
     */
    protected function getLeaveTypeSummaries($employee, $thaiYear)
    {
        $empId = $employee->id;
        $positionType = $employee->position_type ?? null;
        $gender = $employee->gender ?? null;

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

            $entitlement = 0;
            $policy = LeavePolicies::find()
                ->andWhere(['leave_type_id' => $t->code])
                ->andFilterWhere(['position_type_id' => $positionType])
                ->orderBy(['year_of_service' => SORT_DESC])
                ->one();
            if ($policy) {
                $entitlement = (int) ($policy->max_days ?? $policy->days ?? 0);
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
     * เกณฑ์การลาราชการ (ครู) — ข้อความตามภาพตัวอย่าง
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

<?php

namespace app\components;

use Yii;
use yii\db\Expression;
use yii\base\Component;
use app\models\Province;
use app\models\Categorise;
use app\modules\am\models\AssetDetail;
use yii\helpers\ArrayHelper;
use app\modules\purchase\models\Order;
use app\modules\approveV2\models\Approve;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\inventory\models\StockEvent;
use app\modules\jd\models\JdEmployee;
use app\modules\jd\models\JdEmployeeAcknowledgement;
use app\modules\jd\models\JdChangeRequest;
use app\modules\hr\models\IdpPlan;
use app\modules\hr\models\ProbationCase;
use app\modules\hr\models\ProbationEvaluation;
use app\modules\hr\models\ProbationRound;

// การแจ้งเตือนต่างๆ
class ApproveHelper extends Component
{
    // รวมค่าการแจ้งเตือนต่างๆ
    public static function Info()
    {
        $jdAcknowledgement = self::JdAcknowledgement();
        $jdSignature = self::JdSignature();
        $jdChangeReview = self::JdChangeReview();
        $idp = self::Idp();
        $probation = self::Probation();

        return [
            // 'total' => (self::Leave()['total'] + self::Purchase()['total'] + self::StockApprove()['total'] + self::Development()['total'] + self::Checkin()['total'] + self::AssetMove()['total'] + self::RequisitionV2()['total']),
            'total' => (self::Leave()['total'] + self::Purchase()['total'] + self::StockApprove()['total'] + self::Development()['total'] + self::AssetMove()['total'] + self::RequisitionV2()['total'] + $jdAcknowledgement['total'] + $jdSignature['total'] + $jdChangeReview['total'] + $idp['total'] + $probation['total']),
            'leave' => self::Leave(),
            'booking_car' => self::DriverService(),
            'stock' => self::StockApprove(),
            'purchase' => self::Purchase(),
            'development' => self::Development(),
            'checkin' => self::Checkin(),
            'assetMove' => self::AssetMove(),
            'requisitionV2' => self::RequisitionV2(),
            'jd_acknowledgement' => $jdAcknowledgement,
            'jd_signature' => $jdSignature,
            'jd_change_review' => $jdChangeReview,
            'idp' => $idp,
            'probation' => $probation,
        ];
    }

    public static function Probation(): array
    {
        $default = ['title' => 'ประเมินทดลองงาน', 'total' => 0, 'datas' => []];
        try {
            $me = UserHelper::GetEmployee();
            if (!$me) return $default;

            $items = [];
            $evaluations = ProbationEvaluation::find()
                ->alias('evaluation')
                ->joinWith(['round.case.employee'])
                ->where([
                    'evaluation.evaluator_employee_id' => (int) $me->id,
                    'evaluation.role' => 'group_head',
                    'evaluation.status' => 'open',
                ])
                ->orderBy(['evaluation.id' => SORT_ASC])
                ->all();
            foreach ($evaluations as $evaluation) {
                $case = $evaluation->round->case;
                $items[] = [
                    'employee' => $case->employee,
                    'title' => 'ประเมินทดลองงาน เดือนที่ ' . $evaluation->round->month_no,
                    'detail' => 'รอการประเมินโดยหัวหน้ากลุ่มงานหรือผู้ที่ได้รับมอบหมาย',
                    'url' => ['/hr/employees/view', 'id' => $case->employee_id, 'name' => 'performance_appraisal', 'view' => 'manager'],
                ];
            }

            $decisionCases = ProbationCase::find()
                ->with('employee')
                ->where(['final_recommender_employee_id' => (int) $me->id, 'status' => 'waiting_decision'])
                ->andWhere(['<>', 'supervisor_employee_id', (int) $me->id])
                ->orderBy(['updated_at' => SORT_ASC])
                ->all();
            foreach ($decisionCases as $case) {
                $items[] = [
                    'employee' => $case->employee,
                    'title' => 'สรุปผลการทดลองงาน',
                    'detail' => 'รอบันทึกข้อเสนอจ้างต่อหรือไม่จ้างต่อ',
                    'url' => ['/hr/employees/view', 'id' => $case->employee_id, 'name' => 'performance_appraisal', 'view' => 'manager'],
                ];
            }

            $directorRounds = ProbationRound::find()
                ->alias('round')
                ->joinWith(['case.employee'])
                ->where([
                    'round.status' => 'waiting_acknowledgement',
                    'probation_case.director_employee_id' => (int) $me->id,
                ])
                ->orderBy(['round.id' => SORT_ASC])
                ->all();
            foreach ($directorRounds as $round) {
                $case = $round->case;
                $items[] = [
                    'employee' => $case->employee,
                    'title' => 'รับทราบผลทดลองงาน เดือนที่ ' . $round->month_no,
                    'detail' => 'รอผู้อำนวยการรับทราบผลการประเมิน',
                    'url' => ['/hr/employees/view', 'id' => $case->employee_id, 'name' => 'performance_appraisal', 'view' => 'manager'],
                ];
            }

            return ['title' => 'ประเมินทดลองงาน', 'total' => count($items), 'datas' => $items];
        } catch (\Throwable $th) {
            Yii::warning('Unable to load probation notifications: ' . $th->getMessage(), __METHOD__);
            return $default;
        }
    }

    /**
     * HR/admin: คำขอทบทวน JD ที่ยังเปิดอยู่ (รอ HR รับ/ไม่รับ)
     */
    public static function JdChangeReview(): array
    {
        $default = ['title' => 'คำขอทบทวน JD', 'total' => 0, 'datas' => [], 'url' => ['/jd/employee-jd/review-inbox']];
        try {
            if (!Yii::$app->user->can('hr') && !Yii::$app->user->can('admin')) {
                return $default;
            }
            $rows = JdChangeRequest::find()
                ->where(['status' => JdChangeRequest::openStatuses()])
                ->orderBy(['submitted_at' => SORT_ASC])
                ->all();
            return ['title' => 'คำขอทบทวน JD', 'total' => count($rows), 'datas' => $rows, 'url' => ['/jd/employee-jd/review-inbox']];
        } catch (\Throwable $th) {
            Yii::warning('Unable to load JD change reviews: ' . $th->getMessage(), __METHOD__);
            return $default;
        }
    }

    /**
     * JD ที่รอให้ผู้ใช้ปัจจุบันลงนาม (ผู้จัดทำ/ผู้ตรวจสอบ/ผู้อนุมัติ) ตามลำดับที่ถึงคิว
     */
    public static function JdSignature(): array
    {
        try {
            $me = UserHelper::GetEmployee();
            if (!$me) {
                return ['title' => 'JD รอลงนาม', 'total' => 0, 'datas' => []];
            }

            $rows = Approve::find()
                ->where([
                    'name' => \app\modules\jd\services\JdApprovalService::APPROVE_NAME,
                    'emp_id' => (int) $me->id,
                    'status' => 'Pending',
                ])
                ->orderBy(['id' => SORT_DESC])
                ->all();

            $datas = [];
            foreach ($rows as $row) {
                $jd = JdEmployee::find()->where(['id' => (int) $row->from_id])->one();
                if ($jd && $jd->status === JdEmployee::STATUS_PENDING) {
                    $datas[] = ['approve' => $row, 'jd' => $jd];
                }
            }

            return [
                'title' => 'JD รอลงนาม',
                'total' => count($datas),
                'datas' => $datas,
            ];
        } catch (\Throwable $th) {
            Yii::warning('Unable to load pending JD signatures: ' . $th->getMessage(), __METHOD__);
            return ['title' => 'JD รอลงนาม', 'total' => 0, 'datas' => []];
        }
    }

    public static function Idp(): array
    {
        $default = ['title' => 'IDP รอดำเนินการ', 'total' => 0, 'datas' => [], 'url' => ['/profile', 'name' => 'idp']];
        try {
            $me = UserHelper::GetEmployee();
            if (!$me) return $default;
            // หัวหน้า: แผนรอเห็นชอบ
            $reviewPlans = IdpPlan::find()->where(['supervisor_emp_id' => $me->id, 'status' => 'submitted'])->orderBy(['submitted_at' => SORT_ASC])->all();
            // เจ้าหน้าที่: แผนที่ถูกส่งกลับให้ปรับปรุง
            $revisionPlans = IdpPlan::find()->where(['emp_id' => $me->id, 'status' => 'revision'])->orderBy(['reviewed_at' => SORT_DESC])->all();
            // HR/admin: แผนรอเปิดบันทึก (approved) + รอปิดรอบ (assessment)
            $hrPlans = [];
            if (Yii::$app->user->can('hr') || Yii::$app->user->can('admin')) {
                $hrPlans = IdpPlan::find()->where(['status' => ['approved', 'assessment']])->orderBy(['updated_at' => SORT_ASC])->all();
            }
            $datas = array_merge($reviewPlans, $hrPlans, $revisionPlans);
            $url = ['/profile', 'name' => 'idp'];
            if ($reviewPlans) {
                $url = ['/hr/idp/employee', 'emp_id' => $reviewPlans[0]->emp_id];
            } elseif ($hrPlans) {
                $url = ['/hr/idp/index'];
            }
            return ['title' => 'IDP รอดำเนินการ', 'total' => count($datas), 'datas' => $datas, 'url' => $url];
        } catch (\Throwable $th) {
            Yii::warning('Unable to load IDP notifications: ' . $th->getMessage(), __METHOD__);
            return $default;
        }
    }

    /**
     * JD ฉบับปัจจุบันของผู้ใช้ที่ยังไม่ได้ลงนามรับทราบ
     */
    public static function JdAcknowledgement(): array
    {
        try {
            $me = UserHelper::GetEmployee();
            if (!$me) {
                return ['title' => 'JD รอลงนามรับทราบ', 'total' => 0, 'datas' => []];
            }

            $jd = JdEmployee::findCurrent((int) $me->id);
            if (!$jd) {
                return ['title' => 'JD รอลงนามรับทราบ', 'total' => 0, 'datas' => []];
            }

            // ถ้ามีคำขอทบทวนที่ยังเปิดอยู่ ระบบพักการลงนามรับทราบไว้ (ตรงกับหน้า JD) — ไม่ต้องเด้งเตือนค้าง
            if ($jd->openChangeRequest !== null) {
                return ['title' => 'JD รอลงนามรับทราบ', 'total' => 0, 'datas' => []];
            }

            // กำลังมีฉบับร่าง/รอลงนามที่ revision สูงกว่า (HR รับคำขอแล้วกำลังทำฉบับใหม่) → ไม่ต้องเด้งให้รับทราบฉบับเดิม
            if (JdEmployee::find()
                ->where(['emp_id' => (int) $me->id, 'status' => [JdEmployee::STATUS_DRAFT, JdEmployee::STATUS_PENDING]])
                ->andWhere(['>', 'revision_no', (int) $jd->revision_no])
                ->exists()) {
                return ['title' => 'JD รอลงนามรับทราบ', 'total' => 0, 'datas' => []];
            }

            $acknowledged = JdEmployeeAcknowledgement::find()
                ->where([
                    'jd_employee_id' => (int) $jd->id,
                    'emp_id' => (int) $me->id,
                ])
                ->exists();

            return [
                'title' => 'JD รอลงนามรับทราบ',
                'total' => $acknowledged ? 0 : 1,
                'datas' => $acknowledged ? [] : [$jd],
            ];
        } catch (\Throwable $th) {
            Yii::warning('Unable to load pending JD acknowledgement: ' . $th->getMessage(), __METHOD__);
            return ['title' => 'JD รอลงนามรับทราบ', 'total' => 0, 'datas' => []];
        }
    }


    public static function viewStep($name, $formId)
{
    // ดึงรายการ Approve ทั้งหมดของ form นั้น
    $steps = Approve::find()
        ->where(['name' => $name, 'from_id' => $formId])
        ->orderBy(['id' => SORT_ASC])
        ->all();

    $count = count($steps);

    // นับ step ที่ pass
    $pass = 0;

    // สร้าง progress bar แบบ dynamic
    $bars = '';

    foreach ($steps as $step) {

        if ($step->status == 'Pass') {
            $color = "bg-primary";
            $pass++;
        } else {
            $color = "bg-secondary bg-opacity-25";
        }

        $bars .= '<div class="rounded-pill '.$color.'" style="width: 20px; height: 6px;"></div>';
    }

    return '
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-1">
            <div class="d-flex gap-1">
                '.$bars.'
            </div>
            <small class="text-muted ms-2" style="font-size: 0.75rem;">Step '.$pass.'/'.$count.'</small>
        </div>
    </div>';
}

    /**
     * Render step progress from pre-loaded Approve models (avoids N+1 in lists).
     * @param \app\modules\approveV2\models\Approve[] $steps
     * @return string
     */
    public static function viewStepFromSteps(array $steps)
    {
        $count = count($steps);
        $pass = 0;
        $bars = '';
        foreach ($steps as $step) {
            $color = ($step->status == 'Pass') ? 'bg-primary' : 'bg-secondary bg-opacity-25';
            if ($step->status == 'Pass') {
                $pass++;
            }
            $bars .= '<div class="rounded-pill ' . $color . '" style="width: 20px; height: 6px;"></div>';
        }
        return '<div class="d-flex align-items-center justify-content-between">'
            . '<div class="d-flex align-items-center gap-1">'
            . '<div class="d-flex gap-1">' . $bars . '</div>'
            . '<small class="text-muted ms-2" style="font-size: 0.75rem;">Step ' . $pass . '/' . $count . '</small>'
            . '</div></div>';
    }


    // ระบบการแจ้งเตือนการอนุมัติใช้รถยนต์
    public static function DriverService()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'driver_service', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->limit(10)->all();

            return [
                'title' => 'ขออนุญาตใช้รถ',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'แจ้งซ่อม',
                'total' => 0,
                'datas' => []
            ];
        }
    }

    // ระบบการแจ้งเตือนการอนุมัติ
    public static function Leave()
    {
        try {
            $me = UserHelper::GetEmployee();
            $approveQuery = Approve::find()
                ->alias('approve')
                ->leftJoin('`leave`', "approve.from_id = `leave`.id")
                ->where([
                    'approve.name' => 'leave',
                    'approve.emp_id' => $me->id,
                    'approve.status' => 'Pending'
                ])
                ->andWhere(['NOT IN', 'leave.status', ['ReqCancel', 'cancel']])
                ->orderBy(['approve.id' => SORT_DESC]);

            // Debug SQL ที่ถูกสร้าง
            $sql = $approveQuery->createCommand()->getRawSql();

            $datas = $approveQuery->all();

            return [
                'title' => 'ขออนุมัติลา',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'sql' => $sql
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'ขออนุมัติลา',
                'total' => 0,
                'datas' => [],
                'sql' => 'sql'
            ];
        }
    }

    // รายการที่ต้องอนุมัติจัดซื้อ
    public static function Purchase()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'purchase', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();

            return [
                'title' => 'อนุมัติขอซื้อขอจ้าง',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติขอซื้อขอจ้าง',
                'total' => 0,
                'datas' => [],
            ];
        }
    }

    // รายการที่ต้องอนุมัติจัดซื้อ
    public static function StockApprove()
    {
        try {
            $me = UserHelper::GetEmployee();
            $datas = Approve::find()->where(['name' => 'main_stock', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();

            return [
                'title' => 'อนุมัติขอเบิกวัสดุ',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติขอเบิกวัสดุ',
                'total' => 0,
                'datas' => [],
            ];
        }
    }

    /** รายการขออนุมัติเบิกวัสดุ (inventoryV2 – ใบขอเบิกรอหัวหน้าอนุมัติ) */
    public static function RequisitionV2()
    {
        try {
            $me = UserHelper::GetEmployee();
            if (!$me) {
                return ['title' => 'ขออนุมัติเบิกวัสดุ', 'total' => 0, 'datas' => []];
            }

            $count = (int) Approve::find()
                ->alias('a')
                ->innerJoin('stock_order so', 'so.id = a.from_id')
                ->where([
                    'a.name' => 'requisition_v2',
                    'a.emp_id' => (int) $me->id,
                    'a.status' => 'Pending',
                    'so.order_type' => \app\modules\inventoryV2\models\StockOrder::ORDER_TYPE_OUT,
                    'so.source_type' => 'REQUEST',
                    'so.status' => \app\modules\inventoryV2\models\StockOrder::STATUS_PENDING,
                ])
                ->count();

            return [
                'title' => 'ขออนุมัติเบิกวัสดุ',
                'total' => $count,
                'datas' => [],
                'emp_id' => $me->id,
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'ขออนุมัติเบิกวัสดุ',
                'total' => 0,
                'datas' => [],
            ];
        }
    }

    //อบรม/ประชุม/ดูงาน
    public static function Development()
    {
        try {
            $me = UserHelper::GetEmployee();

            $approveQuery = Approve::find()
                ->alias('approve')
                ->leftJoin('`development`', "approve.from_id = `development`.id")
                ->where([
                    'approve.name' => 'development',
                    'approve.emp_id' => $me->id,
                    'approve.status' => 'Pending'
                ])
                ->andWhere(['NOT IN', 'development.status', ['ReqCancel', 'Cancel']])
                ->orderBy(['approve.id' => SORT_DESC]);

            // Debug SQL ที่ถูกสร้าง
            $sql = $approveQuery->createCommand()->getRawSql();

            $datas = $approveQuery->all();
            return [
                'title' => 'อนุมัติอบรม/ประชุม/ดูงาน',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id,
                'sql' => $sql
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติอบรม/ประชุม/ดูงาน',
                'total' => 0,
                'datas' => [],
            ];
        }
    }

    // อนุมัติการลงเวลาเข้างาน
    public static function Checkin()
    {
        try {
            $me = UserHelper::GetEmployee();
            if (!$me) {
                return ['title' => 'อนุมัติลงเวลา', 'total' => 0, 'datas' => []];
            }
            $datas = Approve::find()
                ->where(['name' => 'checkin', 'status' => 'Pending', 'emp_id' => $me->id])
                ->orderBy(['id' => SORT_DESC])
                ->all();
            return [
                'title' => 'อนุมัติลงเวลา',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
            ];
        } catch (\Throwable $th) {
            return ['title' => 'อนุมัติลงเวลา', 'total' => 0, 'datas' => []];
        }
    }

    // ขอเคลื่อนย้ายทรัพย์สิน
     public static function AssetMove()
    {
        try {
            $me = UserHelper::GetEmployee();
             $datas = Approve::find()->where(['name' => 'asset_move', 'status' => 'Pending', 'emp_id' => $me->id])->orderBy(['id' => SORT_DESC])->all();

            return [
                'title' => 'อนุมัติเคลื่อนย้ายครุภัณฑ์',
                'total' => isset($datas) ? count($datas) : 0,
                'datas' => $datas,
                'emp_id' => $me->id,
            ];
        } catch (\Throwable $th) {
            return [
                'title' => 'อนุมัติเคลื่อนย้ายครุภัณฑ์',
                'total' => 0,
                'datas' => [],
                 'emp_id' => 0
            ];
        }
    }

}

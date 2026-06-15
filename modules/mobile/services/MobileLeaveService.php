<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\components\ApproveLevelResolver;
use app\modules\approveV2\models\Approve;
use app\modules\hr\models\Employees;
use app\modules\leave\models\Leave;
use Yii;

/**
 * Leave business logic สำหรับ mobile module.
 *
 * เกิดจากการสกัด logic ที่กระจายอยู่ใน 5 actions + 2 protected helpers + view:
 * - RBAC `canApprove = Pending && (isOwner || (no owner && hasLeavePermission))` (ซ้ำใน 2 actions)
 * - findApproveById + name='leave' filter (ซ้ำใน 2 actions)
 * - findRecentLeaveRequests + findPendingLeaveApprovals (protected ใน controller ที่ใช้ตรง 4 จุด)
 * - DB query ใน view: Employees::find สำหรับ work-send avatar + foreach Employees::findOne ใน approve chain (N+1!)
 *
 * Approval flow ยังคงใช้ {@see LeaveApprovalService::process()} ของ modules/leave — service นี้
 * รับผิดชอบเฉพาะ surface mobile (load + RBAC + view data prep) ไม่ทับ approval flow ที่มีอยู่แล้ว.
 */
class MobileLeaveService
{
    /**
     * สร้าง Leave draft ใหม่พร้อม ref token + defaults.
     *
     * @return array{model:Leave, ref:string}
     */
    public function newDraft(Employees $me): array
    {
        $ref = substr(Yii::$app->getSecurity()->generateRandomString(), 0, 22);

        $model = new Leave();
        $model->ref       = $ref;
        $model->emp_id    = $me->id;
        $model->thai_year = (int) AppHelper::YearBudget();

        return ['model' => $model, 'ref' => $ref];
    }

    /**
     * โหลด Leave ตาม id ถ้าเป็นของ employee ที่ระบุ.
     */
    public function findOwnedById(int $id, $empId): ?Leave
    {
        $leave = Leave::findOne($id);
        if (!$leave || (string) $leave->emp_id !== (string) $empId) {
            return null;
        }
        return $leave;
    }

    /**
     * รับ Leave ที่ load(post) แล้ว: Thai→Greg date conversion + status + save + createApprove.
     *
     * @return array{ok:bool, errors:array}
     */
    public function saveFromPost(Leave $model): array
    {
        $model->date_start = $model->date_start ? AppHelper::convertToGregorian($model->date_start) : null;
        $model->date_end   = $model->date_end ? AppHelper::convertToGregorian($model->date_end) : null;
        $model->status     = 'Pending';

        try {
            if ($model->save()) {
                try {
                    $model->createApprove();
                } catch (\Throwable $e) {
                    // createApprove failed: leave row already saved; surface as soft error
                    Yii::warning('createApprove failed: ' . $e->getMessage(), __METHOD__);
                }
                return ['ok' => true, 'errors' => []];
            }
        } catch (\Throwable $e) {
            return ['ok' => false, 'errors' => $model->getFirstErrors()];
        }

        return ['ok' => false, 'errors' => $model->getFirstErrors()];
    }

    /**
     * รายการ Leave ของ employee ปัจจุบัน เรียงจากใหม่ไปเก่า.
     * @return Leave[]
     */
    public function findRecentRequests(?Employees $me, ?int $limit = null, ?int $thaiYear = null): array
    {
        if (!$me) return [];

        $query = Leave::find()
            ->with(['leaveType', 'leaveStatus'])
            ->where(['emp_id' => (int) $me->id])
            ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);

        if ($thaiYear !== null) {
            $query->andWhere(['thai_year' => $thaiYear]);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->all();
    }

    /**
     * รายการ Leave ของ employee กรองตามปีงบประมาณ (สำหรับหน้า list).
     *
     * @return Leave[]
     */
    public function findRequestsByYear(?Employees $me, int $thaiYear, ?int $limit = 100): array
    {
        if (!$me) return [];

        try {
            $query = Leave::find()
                ->with(['leaveType', 'leaveStatus'])
                ->where(['emp_id' => (int) $me->id])
                ->andWhere(['thai_year' => $thaiYear])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);

            if ($limit !== null) {
                $query->limit($limit);
            }

            return $query->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * รายการปีงบประมาณ (10 ปีย้อนหลังจากปัจจุบัน) สำหรับ filter dropdown.
     * คืน [year => 'พ.ศ. 2568', ...] เรียงใหม่ไปเก่า.
     *
     * @return array<int,string>
     */
    public function listFiscalYears(int $back = 10): array
    {
        $current = (int) AppHelper::YearBudget();
        $years = [];
        for ($y = $current; $y > $current - $back; $y--) {
            $years[$y] = 'พ.ศ. ' . $y;
        }
        return $years;
    }

    /**
     * รายการใบลาที่รออนุมัติ ตาม scope เดียวกับ approve-v2/leave.
     *
     * @return Approve[]
     */
    public function findPendingApprovals(?Employees $me, ?int $limit = null, ?int $thaiYear = null): array
    {
        return (new MobileApprovalService())->findPendingLeaveApprovals($me, $limit);
    }

    /**
     * Find Approve โดย id (เฉพาะ name='leave').
     */
    public function findApproveById(int $id): ?Approve
    {
        return Approve::find()
            ->andWhere(['id' => $id, 'name' => 'leave'])
            ->one();
    }

    /**
     * RBAC check ว่า user ปัจจุบันมีสิทธิ์ approve/reject Approve นี้หรือไม่.
     * ก่อนหน้านี้ duplicate ใน actionApproveLeave + actionApproveLeaveUpdate.
     */
    public function canActOnApprove(Approve $approve, ?Employees $me): bool
    {
        if ($approve->status !== 'Pending') return false;

        $userIsChecker = Yii::$app->user->can('leave');
        $userIsOwner   = $me && (int) $approve->emp_id === (int) $me->id;
        return $userIsOwner || (empty($approve->emp_id) && $userIsChecker);
    }

    /**
     * โหลด avatar URL ของ work-send recipient (สำหรับ view prefill).
     * ก่อนหน้านี้ leave-request.php query Employees::find ใน view โดยตรง.
     */
    public function loadWorkSendAvatar(?string $sendEmpId, bool $isUpdate): string
    {
        if (!$isUpdate || empty($sendEmpId)) return '';

        try {
            $emp = Employees::find()->where(['id' => $sendEmpId])->one();
            return $emp ? (string) $emp->getAvatar(false) : '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * โหลด approve chain พร้อม preloaded Employees (แก้ปัญหา N+1 query ใน view).
     * ก่อนหน้านี้ leave-request.php เรียก ApproveLevelResolver::resolve() แล้ว
     * foreach Employees::findOne ทีละ step (N queries).
     *
     * @return array<int, array{step:array, employee:?Employees}>
     */
    public function loadApproveChain($empId): array
    {
        $steps = [];
        try {
            $steps = (array) ApproveLevelResolver::resolve('leave', $empId);
        } catch (\Throwable $e) {
            return [];
        }
        if (empty($steps)) return [];

        // Batch-load all employees ที่อ้างถึงในทุก step → 1 query แทน N
        $empIds = array_values(array_filter(array_map(static function ($step) {
            return $step['emp_id'] ?? null;
        }, $steps)));

        $employees = [];
        if (!empty($empIds)) {
            try {
                $rows = Employees::find()->where(['id' => $empIds])->all();
                foreach ($rows as $emp) {
                    $employees[(string) $emp->id] = $emp;
                }
            } catch (\Throwable $e) {
                $employees = [];
            }
        }

        $chain = [];
        foreach ($steps as $step) {
            $stepEmpId = $step['emp_id'] ?? null;
            $chain[] = [
                'step'     => (array) $step,
                'employee' => $stepEmpId !== null && isset($employees[(string) $stepEmpId])
                    ? $employees[(string) $stepEmpId]
                    : null,
            ];
        }
        return $chain;
    }
}

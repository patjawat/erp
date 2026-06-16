<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\am\models\AssetDetail;
use app\modules\approveV2\models\Approve;
use app\modules\booking\models\Vehicle;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Development;
use app\modules\leave\components\LeaveApprovalService;
use app\modules\leave\models\Leave;
use app\modules\purchase\models\Order as PurchaseOrder;
use yii\helpers\ArrayHelper;

/**
 * รวมงานอนุมัติที่เปิดใช้งานบน mobile จาก approve table มาให้มือถือใช้แสดงในที่เดียว
 *
 * ใช้ pattern เดียวกับระบบเดิมเป็น "แนวทาง" — ไม่ redirect ไป controller/view ของ module อื่น
 * - LEAVE: ใช้ LeaveApprovalService (มี workflow ครบ: status map ตาม level, telegram notify, MsgApprove/Reject)
 * - DEVELOPMENT: ทำ workflow ให้ตรงกับ approve-v2/development (status map, next approver, parent status)
 */
class MobileApprovalService
{
    public const STATUS_PENDING  = 'Pending';
    public const STATUS_PASS     = 'Pass';
    public const STATUS_REJECT   = 'Reject';
    public const STATUS_SENDBACK = 'SendBack'; // ส่งคืนแก้ไข (ถ้า workflow รองรับ)

    /**
     * @return array<string,array{label:string,icon:string,cat:string}>
     */
    public static function typeMeta(): array
    {
        return [
            'leave'       => ['label' => 'ขอลา',                 'icon' => 'calendar-off',   'cat' => 'leave'],
            'development' => ['label' => 'ขออบรม/ประชุม/ดูงาน', 'icon' => 'graduation-cap', 'cat' => 'document'],
        ];
    }

    public function typeLabel(string $name): string
    {
        $meta = self::typeMeta();
        return (string) ($meta[$name]['label'] ?? $name);
    }

    public function typeIcon(string $name): string
    {
        $meta = self::typeMeta();
        return (string) ($meta[$name]['icon'] ?? 'file-text');
    }

    /**
     * คืน label/tone ของ approve.status
     * @return array{label:string,tone:string}
     */
    public function statusInfo(string $status): array
    {
        static $map = [
            'Pending'  => ['label' => 'รออนุมัติ',  'tone' => 'warning'],
            'Pass'     => ['label' => 'อนุมัติแล้ว', 'tone' => 'success'],
            'Approve'  => ['label' => 'อนุมัติแล้ว', 'tone' => 'success'],
            'Reject'   => ['label' => 'ไม่อนุมัติ',  'tone' => 'danger'],
            'SendBack' => ['label' => 'ส่งคืนแก้ไข', 'tone' => 'info'],
            'None'     => ['label' => 'ยังไม่ถึงคิว', 'tone' => 'secondary'],
        ];
        return $map[$status] ?? ['label' => $status !== '' ? $status : '-', 'tone' => 'secondary'];
    }

    /**
     * คืน bucket จาก approve.status สำหรับ filter chips
     */
    public function bucket(string $status): string
    {
        if ($status === 'Pending') return 'pending';
        if (in_array($status, ['Pass', 'Approve'], true)) return 'approved';
        if ($status === 'Reject') return 'rejected';
        if ($status === 'SendBack') return 'sendback';
        return 'other';
    }

    /**
     * รายการ approve ที่ employee คนนี้รับผิดชอบ
     *  - ตามปกติ approve.emp_id = id ของผู้อนุมัติ
     *  - มี approve.emp_id = null สำหรับ "ยังไม่มอบหมายให้ใคร" (เปิดให้ role ที่มีสิทธิ์)
     *
     * @return Approve[]
     */
    public function findForEmployee(?Employees $me, ?string $bucketFilter = null, ?int $thaiYear = null, int $limit = 200, ?string $typeFilter = null): array
    {
        if (!$me) return [];

        $typeFilter = $this->normalizeTypeFilter($typeFilter);
        if ($bucketFilter === 'pending' && $typeFilter === 'leave') {
            return $this->findPendingLeaveApprovals($me, $limit);
        }
        if ($bucketFilter === 'pending' && $typeFilter === 'development') {
            return $this->findPendingDevelopmentApprovals($me, $limit);
        }
        if ($bucketFilter === 'pending' && $typeFilter === null) {
            return $this->findPendingApprovalsForEnabledTypes($me, $limit);
        }

        $query = Approve::find()
            ->alias('approve')
            ->andWhere(['IS', 'approve.deleted_at', null])
            ->andWhere(['approve.name' => array_keys(self::typeMeta())])
            ->orderBy(['approve.id' => SORT_DESC])
            ->limit($limit);

        if ($typeFilter !== null) {
            $query->andWhere(['approve.name' => $typeFilter]);
        }

        // visibility: ของฉันเอง + รายการที่ยังไม่มอบหมาย (เฉพาะ pending) + รายการที่เราเคย action ไปแล้ว
        $query->andWhere(['or',
            ['approve.emp_id' => (int) $me->id],
            ['and', ['approve.emp_id' => null], ['approve.status' => 'Pending']],
        ]);

        $this->applyLeaveApprovalScope($query, $me);

        // กรองตาม bucket
        if ($bucketFilter === 'pending') {
            $query->andWhere(['approve.status' => 'Pending']);
        } elseif ($bucketFilter === 'approved') {
            $query->andWhere(['approve.status' => ['Pass', 'Approve']]);
        } elseif ($bucketFilter === 'rejected') {
            $query->andWhere(['approve.status' => 'Reject']);
        } elseif ($bucketFilter === 'sendback') {
            $query->andWhere(['approve.status' => 'SendBack']);
        }

        // กรองตามปีงบประมาณ — ปีงบ N ครอบคลุม Oct 1 (N-544) ถึง Sep 30 (N-543)
        //   (สอดคล้องกับ AppHelper::YearBudget(): IF(MONTH>9, YEAR+1, YEAR) + 543)
        if ($thaiYear !== null) {
            $query->andWhere(['between',
                'DATE(approve.created_at)',
                ($thaiYear - 544) . '-10-01',
                ($thaiYear - 543) . '-09-30',
            ]);
        }

        try {
            return $query->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * ใบลาที่รออนุมัติของผู้ใช้ปัจจุบัน ตาม query ใน approve-v2/leave.
     *
     * @return Approve[]
     */
    public function findPendingLeaveApprovals(?Employees $me, ?int $limit = 200): array
    {
        if (!$me) return [];

        $query = Approve::find()
            ->alias('approve')
            ->joinWith(['leave'])
            ->joinWith(['leave.employee'])
            ->andWhere(['like', 'approve.status', self::STATUS_PENDING])
            ->andWhere(['<>', 'approve.status', 'None'])
            ->andWhere(['<>', 'leave.status', 'Cancel'])
            ->andFilterWhere(['approve.name' => 'leave'])
            ->andFilterWhere(['approve.emp_id' => (int) $me->id])
            ->groupBy(['approve.from_id'])
            ->orderBy(['approve.id' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit($limit);
        }

        try {
            return $query->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * ขออบรม/ประชุม/ดูงานที่รออนุมัติ ตาม query ใน approve-v2/development.
     *
     * @return Approve[]
     */
    public function findPendingDevelopmentApprovals(?Employees $me, ?int $limit = 200): array
    {
        if (!$me) return [];

        $query = Approve::find()
            ->alias('approve')
            ->joinWith(['development', 'development.developmentDetail'])
            ->andFilterWhere(['like', 'approve.status', self::STATUS_PENDING])
            ->andFilterWhere(['approve.name' => 'development'])
            ->andFilterWhere(['approve.emp_id' => (int) $me->id])
            ->groupBy(['development.id'])
            ->orderBy(['development.id' => SORT_DESC]);

        if ($limit !== null) {
            $query->limit($limit);
        }

        try {
            return $query->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * หน้า mobile รวมทุกประเภทที่เปิดใช้ตอนนี้: ขอลา + ขออบรม/ประชุม/ดูงาน.
     *
     * @return Approve[]
     */
    private function findPendingApprovalsForEnabledTypes(Employees $me, int $limit): array
    {
        $leaveRows = $this->findPendingLeaveApprovals($me, $limit);
        $developmentRows = $this->findPendingDevelopmentApprovals($me, $limit);
        $rows = array_merge($leaveRows, $developmentRows);

        usort($rows, static function (Approve $a, Approve $b): int {
            return (int) $b->id <=> (int) $a->id;
        });

        return array_slice($rows, 0, $limit);
    }

    /**
     * นับ bucket จากรายการของ user
     * @return array{all:int,pending:int,approved:int,rejected:int,sendback:int}
     */
    public function bucketCounts(?Employees $me, ?int $thaiYear = null, ?string $typeFilter = null): array
    {
        $counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'sendback' => 0];
        if (!$me) return $counts;

        $rows = $this->findForEmployee($me, null, $thaiYear, 500, $typeFilter);
        foreach ($rows as $r) {
            $b = $this->bucket((string) $r->status);
            $counts['all']++;
            if (isset($counts[$b])) $counts[$b]++;
        }
        return $counts;
    }

    public function findByIdForEmployee(int $id, ?Employees $me): ?Approve
    {
        if (!$me) return null;
        $approve = Approve::findOne(['id' => $id]);
        if (!$approve) return null;
        if (!array_key_exists((string) $approve->name, self::typeMeta())) return null;

        if ((string) $approve->name === 'leave') {
            if ((int) ($approve->emp_id ?? 0) !== (int) $me->id) return null;
            if ((string) $approve->status === 'None') return null;

            $leave = $approve->leave;
            if (!$leave || (string) ($leave->status ?? '') === 'Cancel') return null;

            return $approve;
        }

        // owner check: ตอนนี้เปิดใช้งานเฉพาะ leave/development ตาม approve-v2 scope
        $isOwner = ((int) ($approve->emp_id ?? 0)) === (int) $me->id;
        if (!$isOwner) return null;
        return $approve;
    }

    /**
     * โหลด parent record ตาม approve.name + approve.from_id
     */
    public function loadParent(Approve $approve): ?object
    {
        $name = (string) $approve->name;
        $fromId = (int) $approve->from_id;
        try {
            if ($name === 'leave') return Leave::findOne($fromId);
            if ($name === 'vehicle') return Vehicle::findOne($fromId);
            if ($name === 'asset-move') return AssetDetail::findOne($fromId);
            if ($name === 'purchase') return PurchaseOrder::findOne($fromId);
            if ($name === 'development') return Development::findOne($fromId);
        } catch (\Throwable $e) {}
        return null;
    }

    /**
     * คืน meta สำหรับ card/detail
     * @return array{title:string,requester:string,requesterAvatar:string,createdAt:string,summary:string}
     */
    public function buildMeta(Approve $approve, ?object $parent): array
    {
        $createdAt = '';
        if (!empty($approve->created_at)) {
            try {
                $createdAt = ThaiDateHelper::formatThaiDate((string) $approve->created_at, 'short');
            } catch (\Throwable $e) { $createdAt = (string) $approve->created_at; }
        }

        $title = (string) ($approve->title ?: '-');
        $requester = '';
        $requesterAvatar = '';
        $summary = '';

        $name = (string) $approve->name;
        try {
            if ($name === 'leave' && $parent) {
                $requesterEmployee = $parent->employee ?? null;
                if (!empty($parent->created_at)) {
                    try {
                        $createdAt = ThaiDateHelper::formatThaiDate((string) $parent->created_at, 'short');
                    } catch (\Throwable $e) {
                        $createdAt = (string) $parent->created_at;
                    }
                }
                $title = ($parent->leaveType->title ?? 'ใบลา');
                $requester = $this->employeeFullName($requesterEmployee);
                $requesterAvatar = $this->employeeAvatar($requesterEmployee);
                $summary = trim((string) ($parent->data_json['reason'] ?? ''));
                if ($summary === '' && method_exists($parent, 'showLeaveDate')) {
                    $summary = strip_tags((string) $parent->showLeaveDate());
                }
            } elseif ($name === 'vehicle' && $parent) {
                $title = 'จองรถ ' . (string) ($parent->car ?? $parent->code ?? '');
                $requester = (string) ($parent->employee->fullname ?? '');
                $summary = trim((string) ($parent->data_json['purpose'] ?? $parent->title ?? ''));
            } elseif ($name === 'asset-move' && $parent) {
                $title = 'เคลื่อนย้ายครุภัณฑ์';
                $data = is_array($parent->data_json) ? $parent->data_json : [];
                $from = (string) ($data['from_location'] ?? $data['from'] ?? '');
                $to   = (string) ($data['to_location']   ?? $data['to']   ?? '');
                $summary = trim($from . ($to ? ' → ' . $to : ''));
            } elseif ($name === 'purchase' && $parent) {
                $title = 'ขออนุมัติซื้อ ' . (string) ($parent->code ?? '');
                $requester = (string) ($parent->employee->fullname ?? '');
                $summary = (string) ($parent->title ?? '');
            } elseif ($name === 'development' && $parent) {
                $requesterEmployee = $parent->createdByEmp ?? null;
                $title = (string) (($parent->title ?? '') ?: ($parent->topic ?? '') ?: 'ขออบรม/ประชุม/ดูงาน');
                $requester = $this->employeeFullName($requesterEmployee);
                $requesterAvatar = $this->employeeAvatar($requesterEmployee);
                $summary = (string) ($parent->topic ?? '');
            }
        } catch (\Throwable $e) {}

        return [
            'title'           => $title,
            'requester'       => $requester ?: '-',
            'requesterAvatar' => $requesterAvatar,
            'createdAt'       => $createdAt ?: '-',
            'summary'         => $summary,
        ];
    }

    private function employeeFullName($employee): string
    {
        if (!$employee) return '';

        try {
            if (isset($employee->fullname) && trim((string) $employee->fullname) !== '') {
                return (string) $employee->fullname;
            }
            if (method_exists($employee, 'fullname')) {
                return (string) $employee->fullname();
            }
        } catch (\Throwable $e) {}

        return '';
    }

    private function employeeAvatar($employee): string
    {
        if (!$employee) return '';

        try {
            if (method_exists($employee, 'showAvatar')) {
                return (string) $employee->showAvatar();
            }
            if (method_exists($employee, 'ShowAvatar')) {
                return (string) $employee->ShowAvatar();
            }
        } catch (\Throwable $e) {}

        return '';
    }

    /**
     * Timeline ของระดับการอนุมัติ (จาก approve table for same from_id+name)
     * @return Approve[]
     */
    public function loadTimeline(Approve $approve): array
    {
        try {
            return Approve::find()
                ->where(['name' => $approve->name, 'from_id' => $approve->from_id])
                ->andWhere(['IS', 'deleted_at', null])
                ->orderBy(['level' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * ประมวลผลการอนุมัติ
     *   - leave → ส่งต่อ LeaveApprovalService (workflow ครบ)
     *   - อื่นๆ → minimal update approve.status + comment + stamp emp_id + date_json
     *
     * @return array{ok:bool, message?:string}
     */
    public function process(Approve $approve, string $status, string $comment, ?int $actorEmpId): array
    {
        if (!in_array($status, [self::STATUS_PASS, self::STATUS_REJECT, self::STATUS_SENDBACK], true)) {
            return ['ok' => false, 'message' => 'สถานะไม่ถูกต้อง'];
        }
        if ($approve->status !== self::STATUS_PENDING) {
            return ['ok' => false, 'message' => 'รายการนี้ไม่ได้อยู่ระหว่างรออนุมัติ'];
        }

        // ส่งคืนแก้ไข — ใช้ field comment + status พิเศษ "SendBack" ไม่ก้าวข้าม level
        if ($status === self::STATUS_SENDBACK) {
            if (trim($comment) === '') return ['ok' => false, 'message' => 'ส่งคืนแก้ไขต้องระบุเหตุผล'];
            $approve->status = self::STATUS_SENDBACK;
            $approve->comment = $comment;
            $approve->data_json = ArrayHelper::merge((array) $approve->data_json, ['sendback_date' => date('Y-m-d H:i:s')]);
            if (!empty($actorEmpId) && empty($approve->emp_id)) $approve->emp_id = (int) $actorEmpId;
            if (!$approve->save(false)) return ['ok' => false, 'message' => 'บันทึกไม่สำเร็จ'];
            return ['ok' => true, 'message' => 'ส่งคืนคำขอให้ผู้ขอแก้ไขแล้ว'];
        }

        // Reject ต้องมีเหตุผล
        if ($status === self::STATUS_REJECT && trim($comment) === '') {
            return ['ok' => false, 'message' => 'การไม่อนุมัติต้องระบุเหตุผล'];
        }

        // บันทึก comment ก่อน (ทั้ง 2 กรณี)
        if (trim($comment) !== '') $approve->comment = $comment;

        // LEAVE: delegate ให้ workflow เดิมจัดการ (status map ตาม level, telegram, next approver)
        if ($approve->name === 'leave') {
            $approve->save(false); // commit comment
            $result = (new LeaveApprovalService())->process($approve, $status, $actorEmpId);
            if (!($result['ok'] ?? false)) {
                return ['ok' => false, 'message' => $result['message'] ?? 'บันทึกไม่สำเร็จ'];
            }
            return ['ok' => true, 'message' => $status === self::STATUS_PASS ? 'อนุมัติเรียบร้อย' : 'ไม่อนุมัติเรียบร้อย'];
        }

        if ($approve->name === 'development') {
            return $this->processDevelopmentApproval($approve, $status, $actorEmpId);
        }

        // Fallback สำหรับประเภทที่จะเปิดใช้งานบน mobile ในอนาคต.
        $approve->status = $status;
        $approve->data_json = ArrayHelper::merge((array) $approve->data_json, ['approve_date' => date('Y-m-d H:i:s')]);
        if (!empty($actorEmpId) && empty($approve->emp_id)) $approve->emp_id = (int) $actorEmpId;

        if (!$approve->save(false)) return ['ok' => false, 'message' => 'บันทึกไม่สำเร็จ'];

        if ($status === self::STATUS_PASS) {
            $next = Approve::findOne([
                'name'    => $approve->name,
                'from_id' => $approve->from_id,
                'level'   => $approve->level + 1,
            ]);
            if ($next && $next->status !== 'Pending') {
                $next->status = 'Pending';
                $next->save(false);
            }
        }

        return ['ok' => true, 'message' => $status === self::STATUS_PASS ? 'อนุมัติเรียบร้อย' : 'ไม่อนุมัติเรียบร้อย'];
    }

    /**
     * Workflow ขออบรม/ประชุม/ดูงาน ให้ตรงกับ approve-v2/development.
     *
     * @return array{ok:bool, message?:string}
     */
    private function processDevelopmentApproval(Approve $approve, string $status, ?int $actorEmpId): array
    {
        $approve->data_json = ArrayHelper::merge((array) $approve->data_json, ['approve_date' => date('Y-m-d H:i:s')]);
        $approve->status = $status;
        if (!empty($actorEmpId) && empty($approve->emp_id)) {
            $approve->emp_id = (int) $actorEmpId;
        }

        if (!$approve->save(false)) {
            return ['ok' => false, 'message' => 'บันทึกไม่สำเร็จ'];
        }

        $development = $approve->development;
        if (!$development) {
            return ['ok' => true, 'message' => $status === self::STATUS_PASS ? 'อนุมัติเรียบร้อย' : 'ไม่อนุมัติเรียบร้อย'];
        }

        if ($status === self::STATUS_REJECT) {
            $development->status = 'Reject';
            $development->save(false);
            if (method_exists($development, 'MsgReject')) {
                $development->MsgReject();
            }
            return ['ok' => true, 'message' => 'ไม่อนุมัติเรียบร้อย'];
        }

        if ($approve->maxLevel() && $status === self::STATUS_PASS) {
            $development->status = 'Approve';
            $development->save(false);
            if (method_exists($development, 'MsgApprove')) {
                $development->MsgApprove();
            }
            return ['ok' => true, 'message' => 'อนุมัติเรียบร้อย'];
        }

        $statusMap = [
            1 => ['Pass' => 'Checking1_pass', 'Reject' => 'Checking1_reject'],
            2 => ['Pass' => 'Checking2_pass', 'Reject' => 'Checking2_reject'],
            3 => ['Pass' => 'Checkup_pass', 'Reject' => 'Checkup_reject'],
            4 => ['Pass' => 'Approve', 'Reject' => 'Reject'],
        ];

        if (isset($statusMap[(int) $approve->level][$status])) {
            $development->status = $statusMap[(int) $approve->level][$status];
            $development->save(false);
        }

        if ($status === self::STATUS_PASS) {
            $next = Approve::findOne([
                'from_id' => $approve->from_id,
                'name' => 'development',
                'level' => $approve->level + 1,
            ]);
            if ($next && $next->status !== self::STATUS_PENDING) {
                $next->status = self::STATUS_PENDING;
                $next->save(false);
            }
        }

        return ['ok' => true, 'message' => $status === self::STATUS_PASS ? 'อนุมัติเรียบร้อย' : 'ไม่อนุมัติเรียบร้อย'];
    }

    private function normalizeTypeFilter(?string $typeFilter): ?string
    {
        $typeFilter = trim((string) $typeFilter);
        if ($typeFilter === '' || $typeFilter === 'all') return null;

        return array_key_exists($typeFilter, self::typeMeta()) ? $typeFilter : null;
    }

    /**
     * ให้รายการ leave ใน mobile ตรงกับ approve-v2/leave:
     * approve.name = leave, approve.emp_id = me, approve.status != None,
     * leave.status != Cancel และแสดงหนึ่งแถวต่อใบลา (from_id).
     */
    private function applyLeaveApprovalScope($query, Employees $me): void
    {
        $query
            ->leftJoin(['mobile_leave' => Leave::tableName()], 'mobile_leave.id = approve.from_id AND approve.name = :mobileLeaveName', [
                ':mobileLeaveName' => 'leave',
            ])
            ->andWhere(['or',
                ['<>', 'approve.name', 'leave'],
                ['and',
                    ['approve.name' => 'leave'],
                    ['approve.emp_id' => (int) $me->id],
                    ['<>', 'approve.status', 'None'],
                    ['<>', 'mobile_leave.status', 'Cancel'],
                ],
            ])
            ->groupBy([
                'approve.name',
                new \yii\db\Expression("CASE WHEN approve.name = 'leave' THEN approve.from_id ELSE approve.id END"),
            ]);
    }
}

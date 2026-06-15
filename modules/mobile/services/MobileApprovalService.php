<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\am\models\AssetDetail;
use app\modules\approveV2\models\Approve;
use app\modules\booking\models\Vehicle;
use app\modules\development\models\Development;
use app\modules\hr\models\Employees;
use app\modules\leave\components\LeaveApprovalService;
use app\modules\leave\models\Leave;
use app\modules\purchase\models\Order as PurchaseOrder;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * รวมงานอนุมัติทุกประเภทจาก approve table มาให้มือถือใช้แสดงในที่เดียว
 *
 * ใช้ pattern เดียวกับระบบเดิมเป็น "แนวทาง" — ไม่ redirect ไป controller/view ของ module อื่น
 * - LEAVE: ใช้ LeaveApprovalService (มี workflow ครบ: status map ตาม level, telegram notify, MsgApprove/Reject)
 * - VEHICLE / ASSET-MOVE / PURCHASE / DEVELOPMENT: ทำ minimal update ที่ approve.status + comment เท่านั้น
 *   (ระบบหลักของแต่ละโมดูลจะอ่าน approve.status ของตัวเองตามรอบของมัน — ไม่แตะ business logic หลัก)
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
            'leave'       => ['label' => 'ขอลา',          'icon' => 'calendar-off',    'cat' => 'leave'],
            'vehicle'     => ['label' => 'จองรถ',         'icon' => 'car',             'cat' => 'vehicle'],
            'asset-move'  => ['label' => 'เคลื่อนย้ายครุภัณฑ์', 'icon' => 'arrow-left-right', 'cat' => 'asset'],
            'purchase'    => ['label' => 'ขออนุมัติซื้อ',  'icon' => 'shopping-bag',    'cat' => 'document'],
            'development' => ['label' => 'พัฒนาบุคลากร',  'icon' => 'graduation-cap',  'cat' => 'document'],
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
    public function findForEmployee(?Employees $me, ?string $bucketFilter = null, ?int $thaiYear = null, int $limit = 200): array
    {
        if (!$me) return [];

        $query = Approve::find()
            ->andWhere(['IS', 'deleted_at', null])
            ->orderBy(['approve.id' => SORT_DESC])
            ->limit($limit);

        // visibility: ของฉันเอง + รายการที่ยังไม่มอบหมาย (เฉพาะ pending) + รายการที่เราเคย action ไปแล้ว
        $query->andWhere(['or',
            ['approve.emp_id' => (int) $me->id],
            ['and', ['approve.emp_id' => null], ['approve.status' => 'Pending']],
        ]);

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

        // กรองตาม thai_year (จาก parent record) — ข้าม join ที่ซับซ้อน ใช้ year จาก created_at ของ approve
        if ($thaiYear !== null) {
            $query->andWhere(['between',
                'DATE(approve.created_at)',
                ($thaiYear - 543) . '-10-01',
                ($thaiYear - 542) . '-09-30',
            ]);
        }

        try {
            return $query->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * นับ bucket จากรายการของ user
     * @return array{all:int,pending:int,approved:int,rejected:int,sendback:int}
     */
    public function bucketCounts(?Employees $me, ?int $thaiYear = null): array
    {
        $counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'sendback' => 0];
        if (!$me) return $counts;

        $rows = $this->findForEmployee($me, null, $thaiYear, 500);
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
        // owner check (เจ้าของแถวอนุมัติ หรือ unassigned ที่ยังรออนุมัติ)
        $isOwner = ((int) ($approve->emp_id ?? 0)) === (int) $me->id;
        $isUnassignedPending = ($approve->emp_id === null && $approve->status === 'Pending');
        if (!$isOwner && !$isUnassignedPending) return null;
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
                $title = ($parent->leaveType->title ?? 'ใบลา');
                $requester = (string) ($parent->employee->fullname ?? '');
                $requesterAvatar = method_exists($parent->employee ?? null, 'showAvatar')
                    ? (string) $parent->employee->showAvatar() : '';
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
                $title = (string) ($parent->title ?: 'พัฒนาบุคลากร');
                $requester = (string) ($parent->employee->fullname ?? '');
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

        // อื่นๆ: minimal update approve.status + stamp date + เลื่อน level ถัดไป (ถ้า Pass)
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
}

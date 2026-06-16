<?php

namespace app\modules\mobile\services;

use app\components\AppHelper;
use app\modules\helpdesk2\models\Helpdesk;
use app\modules\hr\models\Employees;

/**
 * Mobile helpers สำหรับงานแจ้งซ่อม (Helpdesk model).
 * ดูแลเฉพาะมุมมองผู้ขอ (เจ้าของ emp_id) — ไม่แตะ business logic หลักของ helpdesk2.
 */
class MobileMaintenanceService
{
    /**
     * โหลด Helpdesk ตาม id ถ้าเป็นของ employee ที่ระบุ.
     */
    public function findOwnedById(int $id, $empId): ?Helpdesk
    {
        $row = Helpdesk::findOne($id);
        if (!$row || (string) $row->emp_id !== (string) $empId) {
            return null;
        }
        return $row;
    }

    /**
     * รายการแจ้งซ่อมของ employee กรองตามปีงบประมาณ.
     *
     * @return Helpdesk[]
     */
    public function findRequestsByYear(?Employees $me, int $thaiYear, ?int $limit = 100): array
    {
        if (!$me) return [];

        try {
            $query = Helpdesk::find()
                ->where(['emp_id' => (int) $me->id, 'name' => 'repair'])
                ->andWhere(['thai_year' => $thaiYear])
                ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);

            if ($limit !== null) {
                $query->limit($limit);
            }

            return $query->all();
        } catch (\Throwable $e) {
            // fallback กรณี thai_year ใน DB ไม่มีค่า → ดึงรายการล่าสุดของ user แทน
            try {
                $query = Helpdesk::find()
                    ->where(['emp_id' => (int) $me->id, 'name' => 'repair'])
                    ->orderBy(['created_at' => SORT_DESC, 'id' => SORT_DESC]);
                if ($limit !== null) $query->limit($limit);
                return $query->all();
            } catch (\Throwable $inner) {
                return [];
            }
        }
    }

    /**
     * รายการปีงบประมาณ (10 ปีย้อนหลัง) สำหรับ filter dropdown.
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
     * แมพ status code → label/tone/bucket/step.
     * รองรับทั้งโค้ดตัวเลข ('1'-'6') จาก categorise.repair_status
     * และโค้ด string ('pending', 'Receive', 'Repair', 'done', 'Cancel') ที่ controller mobile บันทึก.
     *
     * `step` = ลำดับขั้นใน timeline 4 ขั้น (1=ร้องขอ, 2=รับเรื่อง, 3=ดำเนินการ, 4=เสร็จสิ้น)
     * หรือ 0 ถ้ายกเลิก/จำหน่าย (timeline หยุด)
     *
     * @return array{label:string,tone:string,bucket:string,step:int}
     */
    public function statusInfo(string $status): array
    {
        static $map = [
            // โค้ดตัวเลขจาก categorise
            '1' => ['label' => 'รอรับเรื่อง',  'tone' => 'warning',   'bucket' => 'pending',  'step' => 1],
            '2' => ['label' => 'รับเรื่องแล้ว', 'tone' => 'info',     'bucket' => 'pending',  'step' => 2],
            '3' => ['label' => 'กำลังซ่อม',    'tone' => 'info',      'bucket' => 'pending',  'step' => 3],
            '4' => ['label' => 'ซ่อมเสร็จแล้ว', 'tone' => 'success',  'bucket' => 'done',     'step' => 4],
            '5' => ['label' => 'ยกเลิก',       'tone' => 'danger',    'bucket' => 'cancelled', 'step' => 0],
            '6' => ['label' => 'จำหน่าย',      'tone' => 'secondary', 'bucket' => 'cancelled', 'step' => 0],

            // โค้ด string ที่ mobile/desktop controllers อื่นใช้ — map เข้ากลุ่มเดียวกัน
            'pending'  => ['label' => 'รอรับเรื่อง',  'tone' => 'warning',  'bucket' => 'pending', 'step' => 1],
            'Pending'  => ['label' => 'รอรับเรื่อง',  'tone' => 'warning',  'bucket' => 'pending', 'step' => 1],
            'Receive'  => ['label' => 'รับเรื่องแล้ว', 'tone' => 'info',    'bucket' => 'pending', 'step' => 2],
            'Repair'   => ['label' => 'กำลังซ่อม',    'tone' => 'info',     'bucket' => 'pending', 'step' => 3],
            'Approve'  => ['label' => 'ซ่อมเสร็จแล้ว', 'tone' => 'success', 'bucket' => 'done',    'step' => 4],
            'done'     => ['label' => 'ซ่อมเสร็จแล้ว', 'tone' => 'success', 'bucket' => 'done',    'step' => 4],
            'Done'     => ['label' => 'ซ่อมเสร็จแล้ว', 'tone' => 'success', 'bucket' => 'done',    'step' => 4],
            'Cancel'   => ['label' => 'ยกเลิก',       'tone' => 'danger',   'bucket' => 'cancelled', 'step' => 0],
            'Reject'   => ['label' => 'ปฏิเสธ',       'tone' => 'danger',   'bucket' => 'cancelled', 'step' => 0],
        ];
        return $map[$status] ?? [
            'label'  => $status !== '' ? $status : '-',
            'tone'   => 'secondary',
            'bucket' => 'other',
            'step'   => 0,
        ];
    }

    /**
     * นับจำนวนงานซ่อมแยกตาม bucket (สำหรับ stats overlay).
     *
     * @param Helpdesk[] $items
     * @return array{all:int,pending:int,done:int,cancelled:int,other:int}
     */
    public function bucketCounts(iterable $items): array
    {
        $counts = ['all' => 0, 'pending' => 0, 'done' => 0, 'cancelled' => 0, 'other' => 0];
        foreach ($items as $row) {
            $bucket = $this->statusInfo((string) ($row->status ?? ''))['bucket'];
            $counts['all']++;
            $counts[$bucket] = ($counts[$bucket] ?? 0) + 1;
        }
        return $counts;
    }

    /**
     * แบ่ง sub-bucket ละเอียดกว่า bucket — ใช้สำหรับ KPI filter chips บนหน้า list
     *  - waiting     = step 1 (รอรับเรื่อง)
     *  - in_progress = step 2-3 (รับเรื่องแล้ว / กำลังซ่อม)
     *  - done        = step 4 (ซ่อมเสร็จ)
     *  - cancelled   = step 0 (ยกเลิก / จำหน่าย / ปฏิเสธ)
     */
    public function subBucket(string $status): string
    {
        $step = (int) ($this->statusInfo($status)['step'] ?? 0);
        if ($step === 1) return 'waiting';
        if ($step === 2 || $step === 3) return 'in_progress';
        if ($step === 4) return 'done';
        // step 0 includes cancelled/disposed/rejected; treat all as "cancelled" bucket
        $tone = (string) ($this->statusInfo($status)['tone'] ?? '');
        if ($tone === 'danger' || $tone === 'secondary') return 'cancelled';
        return 'other';
    }

    /**
     * นับจำนวนงานซ่อมตาม sub-bucket (5 ช่อง: ทั้งหมด/รอรับ/กำลังซ่อม/เสร็จ/ยกเลิก).
     *
     * @param Helpdesk[] $items
     * @return array{all:int,waiting:int,in_progress:int,done:int,cancelled:int,other:int}
     */
    public function kpiCounts(iterable $items): array
    {
        $counts = ['all' => 0, 'waiting' => 0, 'in_progress' => 0, 'done' => 0, 'cancelled' => 0, 'other' => 0];
        foreach ($items as $row) {
            $sub = $this->subBucket((string) ($row->status ?? ''));
            $counts['all']++;
            $counts[$sub] = ($counts[$sub] ?? 0) + 1;
        }
        return $counts;
    }

    /**
     * Filter รายการตาม sub-bucket (สำหรับ chip filter).
     * คืน array ตามลำดับเดิม (ไม่ sort ใหม่).
     *
     * @param Helpdesk[] $items
     * @return Helpdesk[]
     */
    public function filterBySubBucket(iterable $items, string $bucket): array
    {
        if ($bucket === 'all' || $bucket === '') {
            return is_array($items) ? array_values($items) : iterator_to_array($items, false);
        }
        $out = [];
        foreach ($items as $row) {
            if ($this->subBucket((string) ($row->status ?? '')) === $bucket) {
                $out[] = $row;
            }
        }
        return $out;
    }

    public function canEdit(Helpdesk $row): bool
    {
        $step = $this->statusInfo((string) $row->status)['step'];
        // แก้ไขได้เฉพาะก่อนรับเรื่อง (step 1)
        return $step === 1;
    }

    public function canCancel(Helpdesk $row): bool
    {
        $step = $this->statusInfo((string) $row->status)['step'];
        // ยกเลิกได้เฉพาะก่อนเข้าซ่อม (step 1-2)
        return $step >= 1 && $step <= 2;
    }

    public function canRate(Helpdesk $row): bool
    {
        $step = $this->statusInfo((string) $row->status)['step'];
        // ลงคะแนนได้เฉพาะเสร็จแล้ว และยังไม่เคยให้คะแนน
        if ($step !== 4) return false;
        $rating = trim((string) ($row->rating ?? ''));
        return $rating === '' || $rating === '0';
    }
}

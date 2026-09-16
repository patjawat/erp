<?php

namespace app\modules\complaint\services;

use app\components\AppHelper;
use app\modules\complaint\models\Complaint;
use app\modules\complaint\models\ComplaintLog;
use app\modules\hr\models\Organization;
use Yii;
use yii\db\Query;

/**
 * สิทธิ์ + ตรรกะกลางของโมดูลรับเรื่องร้องเรียน
 *
 * โมเดลสิทธิ์ (ภายในองค์กร):
 * - "ทีมศูนย์รับเรื่องร้องเรียน" = admin หรือ role 'complaint' → เห็น/จัดการทุกเรื่อง
 * - ผู้ใช้ทั่วไป → เห็น/จัดการเฉพาะเรื่องของหน่วยตนเอง (assigned_unit สายตนเอง) หรือที่ตนสร้าง
 *
 * ตรรกะ nested-set หน่วยงาน ยึดกติกาเดียวกับ KmActivityService/TaskService
 */
class ComplaintService
{
    /** ทีมศูนย์ฯ (admin หรือ role complaint) — เห็น/จัดการทุกเรื่อง */
    public static function isManager(): bool
    {
        try {
            return Yii::$app->user->can('admin') || Yii::$app->user->can('complaint');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * id หน่วยงานในสายของหน่วยที่ระบุ (ตัวเอง + ลูกทุกชั้น) ผ่าน nested set ของ tree
     *
     * @return int[]
     */
    public static function unitScopeIds(?int $unitId): array
    {
        if (!$unitId) {
            return [];
        }
        $node = (new Query())
            ->select(['root', 'lft', 'rgt'])
            ->from(Organization::tableName())
            ->where(['id' => $unitId])
            ->one();
        if (!$node) {
            return [(int) $unitId];
        }
        $ids = (new Query())
            ->select('id')
            ->from(Organization::tableName())
            ->where(['root' => $node['root']])
            ->andWhere(['between', 'lft', (int) $node['lft'], (int) $node['rgt']])
            ->column();
        return array_map('intval', $ids ?: [(int) $unitId]);
    }

    /** จัดการเรื่องนี้ได้ไหม — ทีมศูนย์ฯ / ผู้สร้าง / คนในสายหน่วยที่รับผิดชอบ */
    public static function canManage(Complaint $c, ?int $userId, ?int $empUnitId): bool
    {
        if (self::isManager()) {
            return true;
        }
        if ($userId !== null && (int) $c->created_by === (int) $userId) {
            return true;
        }
        return $c->assigned_unit_id
            && in_array((int) $c->assigned_unit_id, self::unitScopeIds($empUnitId), true);
    }

    /** ดูเรื่องนี้ได้ไหม (เฟส 1 = สิทธิ์เดียวกับ manage เพราะมีข้อมูลส่วนบุคคล) */
    public static function canView(Complaint $c, ?int $userId, ?int $empUnitId): bool
    {
        return self::canManage($c, $userId, $empUnitId);
    }

    /** ออกเลขที่เรื่องถัดไปของปีงบ: CPL-{ปีงบ}-{ลำดับ 4 หลัก} */
    public static function nextComplaintNo(int $fiscalYear): string
    {
        $prefix = 'CPL-' . $fiscalYear . '-';
        $last = (new Query())
            ->select('complaint_no')
            ->from(Complaint::tableName())
            ->where(['like', 'complaint_no', $prefix . '%', false])
            ->orderBy(['complaint_no' => SORT_DESC])
            ->scalar();
        $seq = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** สุ่มรหัสติดตามที่ไม่ซ้ำ (ตัวอักษร/เลข 8 หลัก อ่านง่าย) */
    public static function generateTrackingCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // ตัด 0/O/1/I ที่สับสน
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $exists = (new Query())->from(Complaint::tableName())->where(['tracking_code' => $code])->exists();
        } while ($exists);
        return $code;
    }

    /**
     * คำนวณกำหนด SLA (respond/review/reply/close) จากระดับ + วันตั้งต้น
     * คืน array คอลัมน์ที่พร้อมเซ็ตลง model (ค่า null ถ้าไม่มีระดับ/วันตั้งต้น)
     *
     * @return array{respond_due:?string,review_due:?string,reply_due:?string,close_due:?string}
     */
    public static function slaDates(?int $level, ?string $baseDate): array
    {
        $out = ['respond_due' => null, 'review_due' => null, 'reply_due' => null, 'close_due' => null];
        if (!$level || !$baseDate || !isset(Complaint::SLA[$level])) {
            return $out;
        }
        $sla = Complaint::SLA[$level];
        $ts = strtotime($baseDate);
        if ($ts === false) {
            return $out;
        }
        $addDays = static fn (?int $d): ?string => $d === null ? null : date('Y-m-d', strtotime("+$d day", $ts));
        $out['respond_due'] = $addDays($sla['respond']);
        $out['review_due'] = $addDays($sla['review']);
        $out['reply_due'] = $addDays($sla['reply']);
        $out['close_due'] = $addDays($sla['close']);
        return $out;
    }

    /** บันทึกประวัติการเปลี่ยนสถานะ/การกระทำ (audit trail) */
    public static function log(int $complaintId, string $action, ?string $from = null, ?string $to = null, ?string $note = null): void
    {
        $log = new ComplaintLog([
            'complaint_id' => $complaintId,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note !== null ? mb_substr($note, 0, 500) : null,
        ]);
        $log->save(false);
    }

    // --- หน่วยงาน (reuse ทะเบียน org_unit เหมือน KM) ----------------------

    /** @return array<int,array{id:int,name:string}> ทุกหน่วย (ตัวกรอง) */
    public static function orderedUnits(): array
    {
        return self::buildOrderedUnits(null);
    }

    /** @return array<int,array{id:int,name:string}> หน่วยที่ผู้ใช้เลือกได้ (manager=ทุกหน่วย) */
    public static function selectableUnits(?int $empUnitId): array
    {
        $scope = self::isManager() ? null : (self::unitScopeIds($empUnitId) ?: [-1]);
        return self::buildOrderedUnits($scope);
    }

    /**
     * @param int[]|null $scopeIds จำกัดเฉพาะ tree.id เหล่านี้ (null = ทุกหน่วย)
     * @return array<int, array{id:int,name:string}>
     */
    private static function buildOrderedUnits(?array $scopeIds): array
    {
        $year = (int) AppHelper::YearBudget();
        $exists = (new Query())->from('org_unit')->where(['thai_year' => $year])->exists();
        if (!$exists) {
            $year = (int) (new Query())->from('org_unit')->max('thai_year');
        }

        $units = [];
        if ($year) {
            $rows = (new Query())
                ->select(['ref_id', 'name'])
                ->from('org_unit')
                ->where(['thai_year' => $year, 'active' => 1])
                ->andWhere(['not', ['ref_id' => null]])
                ->orderBy(['source' => SORT_DESC, 'sort' => SORT_ASC, 'name' => SORT_ASC])
                ->all();
            $seen = [];
            foreach ($rows as $r) {
                $treeId = (int) $r['ref_id'];
                if ($treeId <= 0 || isset($seen[$treeId])) {
                    continue;
                }
                if ($scopeIds !== null && !in_array($treeId, $scopeIds, true)) {
                    continue;
                }
                $seen[$treeId] = true;
                $units[] = ['id' => $treeId, 'name' => (string) $r['name']];
            }
        }
        if ($units) {
            return $units;
        }

        $q = Organization::find()->where(['active' => 1])->orderBy(['root' => SORT_ASC, 'lft' => SORT_ASC]);
        if ($scopeIds !== null) {
            $q->andWhere(['id' => $scopeIds]);
        }
        return array_map(
            static fn ($o): array => ['id' => (int) $o->id, 'name' => (string) $o->name],
            $q->all()
        );
    }
}
